<?php

namespace App\Enums;

/** Pihak yang membubuhkan tanda tangan pada PDF surat. */
enum Signatory: string
{
    case Rt = 'rt';
    case Rw = 'rw';
    case RtRw = 'rt_rw';

    public function label(): string
    {
        return match ($this) {
            self::Rt => 'Ketua RT',
            self::Rw => 'Ketua RW',
            self::RtRw => 'Ketua RT, mengetahui Ketua RW',
        };
    }
}
