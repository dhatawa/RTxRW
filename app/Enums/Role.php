<?php

namespace App\Enums;

enum Role: string
{
    case Warga = 'warga';
    case KetuaRt = 'ketua_rt';
    case KetuaRw = 'ketua_rw';

    public function label(): string
    {
        return match ($this) {
            self::Warga => 'Warga',
            self::KetuaRt => 'Ketua RT',
            self::KetuaRw => 'Ketua RW',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Warga => 'warga.dashboard',
            self::KetuaRt => 'rt.dashboard',
            self::KetuaRw => 'rw.dashboard',
        };
    }
}
