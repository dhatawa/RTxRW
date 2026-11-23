<?php

namespace App\Services;

use App\Models\LetterRequest;
use App\Models\LetterRequestDocument;
use App\Models\LetterRequirement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Dokumen persyaratan disimpan di disk privat dengan nama acak; hanya bisa diakses lewat controller ber-policy. */
class DocumentStorage
{
    public const MAX_KB = 2048;

    public const MIMES = ['jpg', 'jpeg', 'png', 'pdf'];

    public const MIME_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];

    public static function rules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', self::MIMES),
            'mimetypes:'.implode(',', self::MIME_TYPES),
            'max:'.self::MAX_KB,
        ];
    }

    /** Simpan / ganti dokumen untuk satu persyaratan. */
    public function store(LetterRequest $request, LetterRequirement $requirement, UploadedFile $file, User $uploader): LetterRequestDocument
    {
        $existing = $request->documents()->where('letter_requirement_id', $requirement->id)->first();

        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'pdf',
        };

        $path = $file->storeAs('documents/'.$request->public_id, Str::random(40).'.'.$extension, 'local');

        $document = $request->documents()->create([
            'letter_requirement_id' => $requirement->id,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 180, ''),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $uploader->id,
        ]);

        if ($existing) {
            $this->delete($existing);
        }

        return $document;
    }

    public function delete(LetterRequestDocument $document): void
    {
        Storage::disk('local')->delete($document->path);
        $document->delete();
    }
}
