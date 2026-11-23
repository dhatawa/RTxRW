<?php

namespace App\Notifications;

use App\Enums\WorkflowAction;
use App\Models\LetterRequest;
use Illuminate\Notifications\Notification;

class LetterRequestUpdated extends Notification
{
    /** @param 'warga'|'rt'|'rw' $audience */
    public function __construct(
        public LetterRequest $request,
        public WorkflowAction $action,
        public string $audience,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = $this->request->letterType->name;
        $ref = $this->request->reference_number;
        $name = $this->request->resident->full_name;

        [$title, $message, $route] = match ($this->audience) {
            'warga' => [
                $this->action->label(),
                match ($this->action) {
                    WorkflowAction::RtApprove => "{$type} ({$ref}) sudah disetujui Ketua RT dan kini menunggu persetujuan Ketua RW.",
                    WorkflowAction::RwApprove => "{$type} ({$ref}) sudah disetujui Ketua RW. Surat sedang disiapkan untuk diterbitkan.",
                    WorkflowAction::Issue => "{$type} ({$ref}) sudah terbit dan bisa diunduh.",
                    WorkflowAction::RtRevision, WorkflowAction::RwRevision => "{$type} ({$ref}) perlu diperbaiki. Catatan: {$this->request->last_note}",
                    WorkflowAction::RtReject, WorkflowAction::RwReject => "{$type} ({$ref}) ditolak. Alasan: {$this->request->last_note}",
                    default => "Status {$type} ({$ref}) diperbarui.",
                },
                route('warga.requests.show', $this->request, false),
            ],
            'rt' => [
                $this->action === WorkflowAction::Resubmit ? 'Perbaikan pengajuan masuk' : 'Pengajuan baru masuk',
                "{$name} mengajukan {$type} ({$ref}).",
                route('rt.requests.show', $this->request, false),
            ],
            'rw' => [
                $this->action === WorkflowAction::RwApprove ? 'Surat siap diterbitkan' : 'Menunggu persetujuan RW',
                $this->action === WorkflowAction::RwApprove
                    ? "{$type} ({$ref}) atas nama {$name} siap diterbitkan."
                    : "{$type} ({$ref}) atas nama {$name} sudah diverifikasi Ketua RT {$this->request->rt->number}.",
                route('rw.requests.show', $this->request, false),
            ],
        };

        return [
            'title' => $title,
            'message' => $message,
            'url' => $route,
            'reference' => $ref,
            'status' => $this->request->status->value,
        ];
    }
}
