<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'kelurahan', 'kecamatan', 'city', 'province', 'postal_code', 'secretariat_address', 'ketua_user_id'])]
class Rw extends Model
{
    public function rts(): HasMany
    {
        return $this->hasMany(Rt::class)->orderBy('number');
    }

    public function ketua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_user_id');
    }

    /** Sistem dirancang untuk satu RW. */
    public static function current(): self
    {
        return once(fn () => static::query()->firstOrFail());
    }

    public function label(): string
    {
        return 'RW '.$this->number;
    }

    public function fullAddress(): string
    {
        return "Kelurahan {$this->kelurahan}, Kecamatan {$this->kecamatan}, {$this->city}";
    }
}
