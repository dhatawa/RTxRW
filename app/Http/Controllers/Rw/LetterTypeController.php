<?php

namespace App\Http\Controllers\Rw;

use App\Enums\Signatory;
use App\Http\Controllers\Controller;
use App\Models\LetterType;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LetterTypeController extends Controller
{
    public function index(): View
    {
        return view('rw.letter-types.index', [
            'letterTypes' => LetterType::query()->ordered()
                ->withCount(['requirements', 'letterRequests'])
                ->with('activeTemplate')->get(),
        ]);
    }

    public function create(): View
    {
        return view('rw.letter-types.form', [
            'type' => new LetterType(['is_active' => true, 'processing_days' => 3, 'signatory' => Signatory::RtRw]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $type = DB::transaction(function () use ($data) {
            $type = LetterType::create($this->attributes($data, null));
            $this->syncRequirements($type, $data['requirements'] ?? []);
            ActivityLogger::log('letter_type.created', "Menambah jenis surat {$type->name}", $type);

            return $type;
        });

        return redirect()->route('rw.templates.edit', $type)
            ->with('success', "Jenis surat {$type->name} ditambahkan. Buat template suratnya agar bisa diterbitkan.");
    }

    public function edit(LetterType $letterType): View
    {
        return view('rw.letter-types.form', ['type' => $letterType->load('requirements')]);
    }

    public function update(Request $request, LetterType $letterType): RedirectResponse
    {
        $data = $this->validated($request, $letterType);

        DB::transaction(function () use ($letterType, $data) {
            $letterType->update($this->attributes($data, $letterType));
            $this->syncRequirements($letterType, $data['requirements'] ?? []);
            ActivityLogger::log('letter_type.updated', "Memperbarui jenis surat {$letterType->name}", $letterType);
        });

        return redirect()->route('rw.letter-types.index')->with('success', "Jenis surat {$letterType->name} diperbarui.");
    }

    public function toggle(LetterType $letterType): RedirectResponse
    {
        $letterType->update(['is_active' => ! $letterType->is_active]);
        ActivityLogger::log('letter_type.'.($letterType->is_active ? 'activated' : 'deactivated'),
            ($letterType->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." jenis surat {$letterType->name}", $letterType);

        return back()->with('success', $letterType->name.($letterType->is_active ? ' kembali dilayani.' : ' tidak lagi ditampilkan untuk pengajuan baru.'));
    }

    private function validated(Request $request, ?LetterType $type): array
    {
        $request->merge(['code' => strtoupper((string) $request->input('code'))]);

        $data = $request->validate([
            'code' => ['required', 'alpha_num', 'max:10', Rule::unique('letter_types', 'code')->ignore($type?->id)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'signatory' => ['required', Rule::enum(Signatory::class)],
            'processing_days' => ['required', 'integer', 'min:1', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'requirements' => ['array', 'max:15'],
            'requirements.*.id' => ['nullable', 'integer'],
            'requirements.*.name' => ['required', 'string', 'max:150'],
            'requirements.*.description' => ['nullable', 'string', 'max:255'],
            'requirements.*.is_required' => ['nullable', 'boolean'],
            'fields' => ['array', 'max:12'],
            'fields.*.key' => ['nullable', 'regex:/^[a-z][a-z0-9_]{0,40}$/'],
            'fields.*.label' => ['required', 'string', 'max:80'],
            'fields.*.type' => ['required', Rule::in(array_keys(LetterType::FIELD_TYPES))],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.help' => ['nullable', 'string', 'max:200'],
        ], [], [
            'code' => 'kode', 'name' => 'nama', 'signatory' => 'penandatangan', 'processing_days' => 'estimasi hari',
            'requirements.*.name' => 'nama persyaratan', 'fields.*.label' => 'label isian',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function attributes(array $data, ?LetterType $type): array
    {
        $fields = collect($data['fields'] ?? [])->values()->map(function ($f) {
            // Kunci lama dipertahankan agar placeholder di template tidak putus saat label diganti.
            return [
                'key' => ($f['key'] ?? null) ?: self::keyFor($f['label']),
                'label' => $f['label'],
                'type' => $f['type'],
                'required' => (bool) ($f['required'] ?? false),
                'help' => $f['help'] ?? null,
            ];
        })->unique('key')->values()->all();

        $slug = $type?->slug ?? $this->uniqueSlug($data['name']);

        return [
            'code' => $data['code'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'signatory' => $data['signatory'],
            'processing_days' => $data['processing_days'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'],
            'fields' => $fields,
        ];
    }

    public static function keyFor(string $label): string
    {
        $key = Str::of(Str::ascii($label))->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->limit(40, '')->toString();
        $key = preg_match('/^[a-z]/', $key) ? $key : 'isian_'.$key;

        // Hindari bentrok dengan placeholder standar.
        return array_key_exists($key, \App\Services\LetterRenderer::PLACEHOLDERS) ? 'isian_'.$key : $key;
    }

    private function syncRequirements(LetterType $type, array $rows): void
    {
        $keep = [];

        foreach (array_values($rows) as $i => $row) {
            $attributes = [
                'name' => $row['name'],
                'description' => $row['description'] ?? null,
                'is_required' => (bool) ($row['is_required'] ?? false),
                'sort_order' => $i,
            ];

            $existing = ! empty($row['id']) ? $type->requirements()->whereKey($row['id'])->first() : null;
            $requirement = $existing ? tap($existing)->update($attributes) : $type->requirements()->create($attributes);
            $keep[] = $requirement->id;
        }

        $type->requirements()->whereNotIn('id', $keep)->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (LetterType::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
