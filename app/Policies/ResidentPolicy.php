<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Resident;
use App\Models\Rw;
use App\Models\User;

class ResidentPolicy
{
    public function view(User $user, Resident $resident): bool
    {
        return match ($user->role) {
            Role::Warga => (int) $resident->user_id === (int) $user->id,
            Role::KetuaRt => $user->managedRt !== null && (int) $user->managedRt->id === (int) $resident->rt_id,
            Role::KetuaRw => (int) Rw::current()->ketua_user_id === (int) $user->id,
        };
    }

    public function verify(User $user, Resident $resident): bool
    {
        return $user->role === Role::KetuaRt
            && $user->managedRt !== null
            && (int) $user->managedRt->id === (int) $resident->rt_id;
    }
}
