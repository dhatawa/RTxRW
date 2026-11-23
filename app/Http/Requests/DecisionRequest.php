<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi dilakukan di controller (policy) dan di LetterWorkflow.
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject', 'revision'])],
            'note' => ['nullable', 'required_unless:decision,approve', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required_unless' => 'Tuliskan alasan atau bagian yang perlu diperbaiki agar warga memahami keputusan Anda.',
        ];
    }

    public function attributes(): array
    {
        return ['decision' => 'keputusan', 'note' => 'catatan'];
    }
}
