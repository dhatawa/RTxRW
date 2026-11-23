<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['letter_request_id', 'letter_requirement_id', 'original_name', 'path', 'mime_type', 'size', 'uploaded_by'])]
#[Hidden(['path'])]
class LetterRequestDocument extends Model
{
    public function letterRequest(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(LetterRequirement::class, 'letter_requirement_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function humanSize(): string
    {
        return $this->size >= 1048576
            ? number_format($this->size / 1048576, 1, ',', '.').' MB'
            : number_format($this->size / 1024, 0, ',', '.').' KB';
    }
}
