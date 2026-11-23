<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'letter_request_id', 'letter_template_id', 'letter_number', 'sequence', 'year',
    'verification_code', 'file_path', 'snapshot', 'issued_by', 'issued_at', 'first_downloaded_at',
])]
#[Hidden(['file_path'])]
class IssuedLetter extends Model
{
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'issued_at' => 'datetime',
            'first_downloaded_at' => 'datetime',
        ];
    }

    public function letterRequest(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by')->withTrashed();
    }

    public function downloadFilename(): string
    {
        return str_replace(['/', ' '], ['-', '_'], $this->letter_number).'.pdf';
    }
}
