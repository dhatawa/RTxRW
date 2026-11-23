<?php

namespace App\Notifications;

use App\Models\Resident;
use Illuminate\Notifications\Notification;

class NewResidentRegistered extends Notification
{
    public function __construct(public Resident $resident) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Warga baru menunggu verifikasi',
            'message' => "{$this->resident->full_name} mendaftar sebagai warga RT {$this->resident->rt->number}.",
            'url' => route('rt.residents.show', $this->resident, false),
            'reference' => null,
            'status' => 'pending',
        ];
    }
}
