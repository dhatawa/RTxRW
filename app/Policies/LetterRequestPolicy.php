<?php

namespace App\Policies;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Models\LetterRequest;
use App\Models\Rw;
use App\Models\User;

class LetterRequestPolicy
{
    public function view(User $user, LetterRequest $request): bool
    {
        return match ($user->role) {
            Role::Warga => $this->owns($user, $request),
            Role::KetuaRt => $this->inRt($user, $request) && $request->status !== RequestStatus::Draft,
            Role::KetuaRw => $this->isRwHead($user) && $request->status !== RequestStatus::Draft,
        };
    }

    /** Ubah isi pengajuan (data / dokumen): hanya pemilik dan hanya saat draf / perlu perbaikan. */
    public function update(User $user, LetterRequest $request): bool
    {
        return $user->role === Role::Warga
            && $this->owns($user, $request)
            && $request->status->isEditableByResident();
    }

    public function delete(User $user, LetterRequest $request): bool
    {
        return $user->role === Role::Warga
            && $this->owns($user, $request)
            && $request->status === RequestStatus::Draft;
    }

    public function decideAsRt(User $user, LetterRequest $request): bool
    {
        return $user->role === Role::KetuaRt && $this->inRt($user, $request);
    }

    public function decideAsRw(User $user, LetterRequest $request): bool
    {
        return $user->role === Role::KetuaRw && $this->isRwHead($user);
    }

    /** Unduh PDF: hanya jika surat sudah terbit, oleh pemilik atau pengurus berwenang. */
    public function downloadLetter(User $user, LetterRequest $request): bool
    {
        if (! $request->status->hasLetter()) {
            return false;
        }

        return match ($user->role) {
            Role::Warga => $this->owns($user, $request),
            Role::KetuaRt => $this->inRt($user, $request),
            Role::KetuaRw => $this->isRwHead($user),
        };
    }

    private function owns(User $user, LetterRequest $request): bool
    {
        return $user->resident !== null && (int) $user->resident->id === (int) $request->resident_id;
    }

    private function inRt(User $user, LetterRequest $request): bool
    {
        return $user->managedRt !== null && (int) $user->managedRt->id === (int) $request->rt_id;
    }

    private function isRwHead(User $user): bool
    {
        return (int) Rw::current()->ketua_user_id === (int) $user->id;
    }
}
