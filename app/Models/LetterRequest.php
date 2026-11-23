<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Kolom status sengaja TIDAK fillable: perubahan status hanya lewat App\Services\LetterWorkflow.
 */
#[Fillable(['resident_id', 'rt_id', 'letter_type_id', 'purpose', 'form_data'])]
class LetterRequest extends Model
{
    use HasUlids, SoftDeletes;

    protected $attributes = ['revision_count' => 0];

    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'status' => RequestStatus::class,
            'submitted_at' => 'datetime',
            'rt_decided_at' => 'datetime',
            'rw_decided_at' => 'datetime',
            'issued_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected static function booted(): void
    {
        static::creating(function (LetterRequest $request) {
            $request->status ??= RequestStatus::Draft;
            $request->reference_number ??= static::generateReference();
        });
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'PGJ-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (static::withTrashed()->where('reference_number', $ref)->exists());

        return $ref;
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class)->withTrashed();
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class)->withTrashed();
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class)->withTrashed();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LetterRequestDocument::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class)->orderBy('created_at')->orderBy('id');
    }

    public function issuedLetter(): HasOne
    {
        return $this->hasOne(IssuedLetter::class);
    }

    public function scopeStatus(Builder $query, RequestStatus|array $status): void
    {
        $query->whereIn('status', collect(is_array($status) ? $status : [$status])->map->value);
    }

    public function scopeForRt(Builder $query, int $rtId): void
    {
        $query->where('rt_id', $rtId);
    }

    /** Pengajuan yang pernah sampai ke meja RW (tidak termasuk draf / masih di RT). */
    public function scopeVisibleToRw(Builder $query): void
    {
        $query->whereNotIn('status', [RequestStatus::Draft->value]);
    }

    public function fieldValue(string $key): mixed
    {
        return $this->form_data[$key] ?? null;
    }
}
