<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['letter_type_id', 'name', 'description', 'is_required', 'sort_order'])]
class LetterRequirement extends Model
{
    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class);
    }
}
