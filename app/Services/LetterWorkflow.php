<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\WorkflowAction;
use App\Exceptions\WorkflowException;
use App\Models\ApprovalHistory;
use App\Models\LetterRequest;
use App\Models\Rw;
use App\Models\User;
use App\Notifications\LetterRequestUpdated;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat perubahan status pengajuan.
 *
 * Setiap transisi: dikunci (SELECT ... FOR UPDATE), dicek role + wilayah + status asal,
 * dicatat di approval_histories dan activity_logs, lalu notifikasi dikirim setelah commit.
 */
class LetterWorkflow
{
    public function apply(LetterRequest $request, WorkflowAction $action, User $actor, ?string $note = null): LetterRequest
    {
        $note = filled($note) ? trim($note) : null;

        return DB::transaction(function () use ($request, $action, $actor, $note) {
            /** @var LetterRequest $locked */
            $locked = LetterRequest::query()->whereKey($request->getKey())->lockForUpdate()->firstOrFail();

            $this->guard($locked, $action, $actor, $note);

            $rule = $action->rule();
            $from = $locked->status;
            $to = $rule['to'];

            $locked->status = $to;
            $this->stampTimestamps($locked, $action);

            if ($action->requiresNote()) {
                $locked->last_note = $note;
            } elseif (in_array($action, [WorkflowAction::Submit, WorkflowAction::Resubmit], true)) {
                $locked->last_note = null;
            }

            if (in_array($action, [WorkflowAction::RtRevision, WorkflowAction::RwRevision], true)) {
                $locked->revision_count++;
            }

            $locked->save();

            // Sinkronkan instance milik pemanggil agar tidak memegang status lama.
            $request->setRawAttributes($locked->getAttributes(), true);

            ApprovalHistory::create([
                'letter_request_id' => $locked->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role->value,
                'action' => $action->value,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'note' => $note,
            ]);

            ActivityLogger::log(
                'workflow.'.$action->value,
                "{$action->label()} — {$locked->reference_number}",
                $locked,
                ['from' => $from->value, 'to' => $to->value, 'note' => $note],
            );

            DB::afterCommit(fn () => $this->notify($locked->fresh(['resident.user', 'rt.ketua', 'letterType']), $action));

            return $locked;
        });
    }

    /** Daftar aksi yang boleh dilakukan aktor terhadap pengajuan saat ini (untuk menampilkan tombol). */
    public function availableActions(LetterRequest $request, User $actor): array
    {
        return array_values(array_filter(
            WorkflowAction::cases(),
            fn (WorkflowAction $a) => $this->can($request, $a, $actor),
        ));
    }

    public function can(LetterRequest $request, WorkflowAction $action, User $actor): bool
    {
        try {
            $this->guard($request, $action, $actor, $action->requiresNote() ? 'x' : null, checkPreconditions: false);

            return true;
        } catch (WorkflowException) {
            return false;
        }
    }

    private function guard(LetterRequest $request, WorkflowAction $action, User $actor, ?string $note, bool $checkPreconditions = true): void
    {
        $rule = $action->rule();

        if (! $actor->is_active) {
            throw new WorkflowException('Akun Anda tidak aktif.');
        }

        if ($actor->role !== $rule['role']) {
            throw new WorkflowException('Anda tidak berwenang melakukan tindakan ini.');
        }

        if (! $this->actorOwnsScope($request, $actor)) {
            throw new WorkflowException('Pengajuan ini berada di luar wilayah kewenangan Anda.');
        }

        if (! in_array($request->status, $rule['from'], true)) {
            throw new WorkflowException("Tindakan tidak dapat dilakukan karena status pengajuan saat ini \"{$request->status->label()}\".");
        }

        if ($action->requiresNote() && blank($note)) {
            throw new WorkflowException('Alasan atau catatan wajib diisi.');
        }

        if ($checkPreconditions && in_array($action, [WorkflowAction::Submit, WorkflowAction::Resubmit], true)) {
            $this->guardSubmission($request);
        }
    }

    private function actorOwnsScope(LetterRequest $request, User $actor): bool
    {
        return match ($actor->role) {
            Role::Warga => (int) $request->resident()->withTrashed()->value('user_id') === (int) $actor->id,
            Role::KetuaRt => $actor->managedRt !== null && (int) $actor->managedRt->id === (int) $request->rt_id,
            Role::KetuaRw => (int) Rw::current()->ketua_user_id === (int) $actor->id,
        };
    }

    private function guardSubmission(LetterRequest $request): void
    {
        $resident = $request->resident;

        if (! $resident->isVerified()) {
            throw new WorkflowException('Data kependudukan Anda belum diverifikasi Ketua RT.');
        }

        if (! $resident->isProfileComplete()) {
            throw new WorkflowException('Lengkapi data profil Anda terlebih dahulu.');
        }

        if ((int) $resident->rt_id !== (int) $request->rt_id) {
            throw new WorkflowException('Domisili Anda telah berubah. Buat pengajuan baru.');
        }

        $type = $request->letterType;

        if (! $type->is_active) {
            throw new WorkflowException('Jenis surat ini sedang tidak dilayani.');
        }

        foreach ($type->fieldDefinitions() as $field) {
            if ($field['required'] && blank($request->fieldValue($field['key']))) {
                throw new WorkflowException("Isian \"{$field['label']}\" wajib diisi.");
            }
        }

        $uploaded = $request->documents()->pluck('letter_requirement_id')->filter()->unique();

        foreach ($type->requirements()->where('is_required', true)->get() as $requirement) {
            if (! $uploaded->contains($requirement->id)) {
                throw new WorkflowException("Dokumen \"{$requirement->name}\" belum diunggah.");
            }
        }
    }

    private function stampTimestamps(LetterRequest $request, WorkflowAction $action): void
    {
        $now = now();

        match ($action) {
            WorkflowAction::Submit, WorkflowAction::Resubmit => [
                $request->submitted_at = $now,
                $request->rt_decided_at = null,
                $request->rw_decided_at = null,
            ],
            WorkflowAction::RtApprove, WorkflowAction::RtReject, WorkflowAction::RtRevision => $request->rt_decided_at = $now,
            WorkflowAction::RwApprove, WorkflowAction::RwReject, WorkflowAction::RwRevision => $request->rw_decided_at = $now,
            WorkflowAction::Issue => $request->issued_at = $now,
            WorkflowAction::Complete => $request->completed_at = $now,
        };
    }

    private function notify(LetterRequest $request, WorkflowAction $action): void
    {
        $warga = $request->resident->user;

        // Warga selalu diberi tahu, kecuali atas tindakannya sendiri.
        if (! in_array($action, [WorkflowAction::Submit, WorkflowAction::Resubmit, WorkflowAction::Complete], true)) {
            $warga?->notify(new LetterRequestUpdated($request, $action, 'warga'));
        }

        // Petugas berikutnya diberi tahu ada antrean baru.
        if ($request->status === RequestStatus::PendingRt) {
            $request->rt->ketua?->notify(new LetterRequestUpdated($request, $action, 'rt'));
        }

        if (in_array($request->status, [RequestStatus::PendingRw, RequestStatus::Approved], true)) {
            Rw::current()->ketua?->notify(new LetterRequestUpdated($request, $action, 'rw'));
        }
    }
}
