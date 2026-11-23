<?php

namespace App\Support;

use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Models\LetterRequest;
use App\Models\User;

/** Menu navigasi per role. Badge berasal dari data nyata di database. */
class Navigation
{
    /** @return list<array{heading?:string, items: list<array{label:string, route:string, icon:string, active:string, badge?:int}>}> */
    public static function for(User $user): array
    {
        return match ($user->role) {
            Role::Warga => [
                ['items' => [
                    ['label' => 'Beranda', 'route' => 'warga.dashboard', 'icon' => 'house', 'active' => 'warga.dashboard'],
                    ['label' => 'Ajukan surat', 'route' => 'warga.requests.create', 'icon' => 'file-plus', 'active' => 'warga.requests.create|warga.requests.form'],
                    ['label' => 'Pengajuan saya', 'route' => 'warga.requests.index', 'icon' => 'files', 'active' => 'warga.requests.index|warga.requests.show|warga.requests.edit'],
                    ['label' => 'Data kependudukan', 'route' => 'warga.profile.edit', 'icon' => 'id-card', 'active' => 'warga.profile.*'],
                ]],
            ],
            Role::KetuaRt => self::rt($user),
            Role::KetuaRw => self::rw(),
        };
    }

    private static function rt(User $user): array
    {
        $rt = $user->managedRt;
        $pending = $rt ? LetterRequest::query()->forRt($rt->id)->status(RequestStatus::PendingRt)->count() : 0;
        $residents = $rt ? $rt->residents()->where('verification_status', VerificationStatus::Pending)->count() : 0;

        return [
            ['items' => [
                ['label' => 'Beranda', 'route' => 'rt.dashboard', 'icon' => 'house', 'active' => 'rt.dashboard'],
                ['label' => 'Pengajuan masuk', 'route' => 'rt.requests.index', 'icon' => 'inbox', 'active' => 'rt.requests.*', 'badge' => $pending],
                ['label' => 'Data warga', 'route' => 'rt.residents.index', 'icon' => 'users', 'active' => 'rt.residents.*', 'badge' => $residents],
                ['label' => 'Riwayat verifikasi', 'route' => 'rt.history', 'icon' => 'history', 'active' => 'rt.history'],
            ]],
        ];
    }

    private static function rw(): array
    {
        $pending = LetterRequest::query()->status([RequestStatus::PendingRw, RequestStatus::Approved])->count();

        return [
            ['items' => [
                ['label' => 'Beranda', 'route' => 'rw.dashboard', 'icon' => 'house', 'active' => 'rw.dashboard'],
                ['label' => 'Pengajuan', 'route' => 'rw.requests.index', 'icon' => 'inbox', 'active' => 'rw.requests.*', 'badge' => $pending],
                ['label' => 'Penerbitan surat', 'route' => 'rw.issuance.index', 'icon' => 'stamp', 'active' => 'rw.issuance.*'],
            ]],
            ['heading' => 'Wilayah', 'items' => [
                ['label' => 'Data RT', 'route' => 'rw.rts.index', 'icon' => 'map', 'active' => 'rw.rts.*'],
                ['label' => 'Ketua RT', 'route' => 'rw.heads.index', 'icon' => 'user-cog', 'active' => 'rw.heads.*'],
                ['label' => 'Data warga', 'route' => 'rw.residents.index', 'icon' => 'users', 'active' => 'rw.residents.*'],
            ]],
            ['heading' => 'Layanan', 'items' => [
                ['label' => 'Jenis surat', 'route' => 'rw.letter-types.index', 'icon' => 'list-checks', 'active' => 'rw.letter-types.*'],
                ['label' => 'Template surat', 'route' => 'rw.templates.index', 'icon' => 'file-pen-line', 'active' => 'rw.templates.*'],
            ]],
            ['heading' => 'Pengawasan', 'items' => [
                ['label' => 'Laporan', 'route' => 'rw.reports.index', 'icon' => 'chart-column', 'active' => 'rw.reports.*'],
                ['label' => 'Audit log', 'route' => 'rw.activity.index', 'icon' => 'scroll-text', 'active' => 'rw.activity.*'],
                ['label' => 'Identitas RW', 'route' => 'rw.settings.edit', 'icon' => 'settings', 'active' => 'rw.settings.*'],
            ]],
        ];
    }

    public static function isActive(string $pattern): bool
    {
        return request()->routeIs(...explode('|', $pattern));
    }

    /** Label wilayah singkat di bawah nama pengguna. */
    public static function scopeLabel(User $user): string
    {
        return match ($user->role) {
            Role::Warga => $user->resident?->rt ? 'Warga RT '.$user->resident->rt->number.' / RW '.$user->resident->rt->rw->number : 'Warga',
            Role::KetuaRt => 'Ketua RT '.($user->managedRt?->number ?? '-').' / RW '.($user->managedRt?->rw->number ?? '-'),
            Role::KetuaRw => 'Ketua RW '.($user->managedRw?->number ?? ''),
        };
    }
}
