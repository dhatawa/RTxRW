<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id', 'rt_id', 'nik', 'kk_number', 'full_name', 'birth_place', 'birth_date', 'gender',
    'religion', 'marital_status', 'occupation', 'nationality', 'address', 'house_number',
])]
#[Hidden(['nik', 'nik_hash', 'kk_number'])]
class Resident extends Model
{
    use HasFactory, SoftDeletes;

    public const RELIGIONS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    public const MARITAL_STATUSES = ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];

    /** Isian profil yang wajib sebelum boleh mengajukan surat. */
    public const REQUIRED_PROFILE_FIELDS = [
        'birth_place', 'birth_date', 'gender', 'religion', 'marital_status', 'occupation', 'address',
    ];

    protected $attributes = [
        'verification_status' => 'pending',
        'nationality' => 'WNI',
    ];

    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'kk_number' => 'encrypted',
            'birth_date' => 'date',
            'verified_at' => 'datetime',
            'verification_status' => VerificationStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Resident $resident) {
            if ($resident->isDirty('nik')) {
                $resident->nik_hash = static::hashNik($resident->nik);
            }
        });
    }

    public static function hashNik(string $nik): string
    {
        return hash_hmac('sha256', preg_replace('/\D/', '', $nik), config('app.key'));
    }

    public static function nikExists(string $nik, ?int $exceptId = null): bool
    {
        return static::withTrashed()
            ->where('nik_hash', static::hashNik($nik))
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->exists();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::Verified;
    }

    public function isProfileComplete(): bool
    {
        foreach (self::REQUIRED_PROFILE_FIELDS as $field) {
            if (blank($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    public function missingProfileFields(): array
    {
        return array_values(array_filter(self::REQUIRED_PROFILE_FIELDS, fn ($f) => blank($this->{$f})));
    }

    public function canSubmitLetters(): bool
    {
        return $this->isVerified() && $this->isProfileComplete();
    }

    /** NIK disamarkan untuk tampilan daftar: 3201********0001 */
    public function maskedNik(): string
    {
        $nik = (string) $this->nik;

        return strlen($nik) >= 8 ? substr($nik, 0, 4).str_repeat('•', strlen($nik) - 8).substr($nik, -4) : $nik;
    }

    public function genderLabel(): ?string
    {
        return match ($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => null,
        };
    }

    public function fullAddress(): string
    {
        $rt = $this->rt;
        $parts = array_filter([
            $this->address,
            $this->house_number ? 'No. '.$this->house_number : null,
            $rt ? 'RT '.$rt->number.'/RW '.$rt->rw->number : null,
        ]);

        return implode(', ', $parts);
    }
}
