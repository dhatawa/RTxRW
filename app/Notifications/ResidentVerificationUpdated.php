<?php

namespace App\Notifications;

use App\Enums\VerificationStatus;
use App\Models\Resident;
use Illuminate\Notifications\Notification;

class ResidentVerificationUpdated extends Notification
{
    public function __construct(public Resident $resident) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $verified = $this->resident->verification_status === VerificationStatus::Verified;

        return [
            'title' => $verified ? 'Data kependudukan terverifikasi' : 'Data kependudukan perlu diperbaiki',
            'message' => $verified
                ? 'Ketua RT telah memverifikasi data Anda. Sekarang Anda bisa mengajukan surat.'
                : 'Ketua RT belum dapat memverifikasi data Anda. Catatan: '.$this->resident->verification_note,
            'url' => route('warga.profile.edit', absolute: false),
            'reference' => null,
            'status' => $this->resident->verification_status->value,
        ];
    }
}
