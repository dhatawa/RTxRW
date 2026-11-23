<?php

namespace App\Models;

use App\Enums\Signatory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'slug', 'description', 'signatory', 'processing_days', 'fields', 'is_active', 'sort_order'])]
class LetterType extends Model
{
    use SoftDeletes;

    public const FIELD_TYPES = ['text' => 'Teks singkat', 'textarea' => 'Teks panjang', 'date' => 'Tanggal', 'number' => 'Angka'];

    protected $attributes = ['is_active' => true, 'processing_days' => 3, 'sort_order' => 0];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'is_active' => 'boolean',
            'signatory' => Signatory::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(LetterRequirement::class)->orderBy('sort_order');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(LetterTemplate::class)->orderByDesc('version');
    }

    public function activeTemplate(): HasOne
    {
        return $this->hasOne(LetterTemplate::class)->where('is_active', true)->latestOfMany('version');
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    /** @return list<array{key:string,label:string,type:string,required:bool,help:?string}> */
    public function fieldDefinitions(): array
    {
        return collect($this->fields ?? [])->map(fn ($f) => [
            'key' => $f['key'],
            'label' => $f['label'],
            'type' => $f['type'] ?? 'text',
            'required' => (bool) ($f['required'] ?? false),
            'help' => $f['help'] ?? null,
        ])->values()->all();
    }
}
