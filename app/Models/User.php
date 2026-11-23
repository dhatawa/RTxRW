<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /** Default di sisi model agar instance baru konsisten dengan default kolom database. */
    protected $attributes = [
        'role' => 'warga',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    public function resident(): HasOne
    {
        return $this->hasOne(Resident::class);
    }

    /** RT yang dipimpin (khusus Ketua RT). */
    public function managedRt(): HasOne
    {
        return $this->hasOne(Rt::class, 'ketua_user_id');
    }

    /** RW yang dipimpin (khusus Ketua RW). */
    public function managedRw(): HasOne
    {
        return $this->hasOne(Rw::class, 'ketua_user_id');
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isWarga(): bool
    {
        return $this->role === Role::Warga;
    }

    public function isKetuaRt(): bool
    {
        return $this->role === Role::KetuaRt;
    }

    public function isKetuaRw(): bool
    {
        return $this->role === Role::KetuaRw;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
    }
}
