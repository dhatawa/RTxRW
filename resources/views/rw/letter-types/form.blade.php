@php
    $editing = $type->exists;
    $requirements = old('requirements', $editing ? $type->requirements->map(fn ($r) => [
        'id' => $r->id, 'name' => $r->name, 'description' => $r->description, 'is_required' => $r->is_required,
    ])->all() : [['id' => null, 'name' => 'Foto KTP pemohon', 'description' => null, 'is_required' => true]]);
    $fields = old('fields', $type->fieldDefinitions());
@endphp
<x-layouts.app :title="$editing ? 'Ubah '.$type->name : 'Tambah jenis surat'">
    <x-page-header :title="$editing ? $type->name : 'Tambah jenis surat'" :back="route('rw.letter-types.index')" back-label="Jenis surat" />

    <form method="POST" action="{{ $editing ? route('rw.letter-types.update', $type) : route('rw.letter-types.store') }}" class="max-w-3xl space-y-8" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <section class="panel">
            <div class="panel-header"><h2 class="panel-title">Informasi umum</h2></div>
            <div class="space-y-5 px-5 py-6 sm:px-6">
                <div class="grid gap-5 sm:grid-cols-[1fr_9rem]">
                    <x-field name="name" label="Nama jenis surat" :value="$type->name" required />
                    <x-field name="code" label="Kode" :value="$type->code" required maxlength="10" hint="Untuk nomor surat." />
                </div>
                <x-field name="description" label="Penjelasan untuk warga" type="textarea" rows="3" :value="$type->description" optional hint="Kapan surat ini dibutuhkan. Tampil di halaman publik dan saat warga memilih surat." />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="signatory" label="Ditandatangani oleh" type="select" :value="$type->signatory?->value" required
                             :options="collect(\App\Enums\Signatory::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()"
                             hint="Alur persetujuan tetap RT lalu RW." />
                    <div class="grid grid-cols-2 gap-5">
                        <x-field name="processing_days" label="Estimasi (hari)" type="number" :value="$type->processing_days" required min="1" max="30" />
                        <x-field name="sort_order" label="Urutan" type="number" :value="$type->sort_order" optional min="0" />
                    </div>
                </div>
                <input type="hidden" name="is_active" value="0">
                <label class="flex gap-3">
                    <input type="checkbox" name="is_active" value="1" class="check mt-0.5" @checked(old('is_active', $type->is_active))>
                    <span><span class="font-semibold">Dilayani</span><span class="block text-sm text-ink-muted">Warga dapat memilih jenis surat ini.</span></span>
                </label>
            </div>
        </section>

        <section class="panel" x-data="repeater(@js(array_values($requirements)), { id: null, name: '', description: '', is_required: true })">
            <div class="panel-header">
                <h2 class="panel-title">Dokumen persyaratan</h2>
                <button type="button" class="btn btn-secondary btn-sm" @click="add()"><x-svg-icon name="plus" class="size-4" />Tambah</button>
            </div>
            <ol class="divide-y divide-line">
                <template x-for="(row, i) in rows" :key="row._key ?? row.id ?? i">
                    <li class="grid gap-3 px-5 py-4 sm:grid-cols-[1fr_1fr_auto] sm:items-start sm:px-6">
                        <input type="hidden" :name="`requirements[${i}][id]`" :value="row.id">
                        <div>
                            <label class="field-label text-sm" :for="`req-name-${i}`">Nama dokumen</label>
                            <input class="input" :id="`req-name-${i}`" :name="`requirements[${i}][name]`" x-model="row.name" required>
                        </div>
                        <div>
                            <label class="field-label text-sm" :for="`req-desc-${i}`">Petunjuk <span class="font-normal text-ink-muted">(opsional)</span></label>
                            <input class="input" :id="`req-desc-${i}`" :name="`requirements[${i}][description]`" x-model="row.description">
                        </div>
                        <div class="flex items-center gap-1 sm:pt-7">
                            <input type="hidden" :name="`requirements[${i}][is_required]`" value="0">
                            <label class="mr-2 flex items-center gap-2 text-sm"><input type="checkbox" class="check" :name="`requirements[${i}][is_required]`" value="1" x-model="row.is_required"> Wajib</label>
                            <button type="button" class="btn btn-ghost btn-sm text-ink-muted" @click="move(i, -1)" :disabled="i === 0" aria-label="Naikkan"><x-svg-icon name="arrow-up" class="size-4" /></button>
                            <button type="button" class="btn btn-ghost btn-sm text-ink-muted" @click="move(i, 1)" :disabled="i === rows.length - 1" aria-label="Turunkan"><x-svg-icon name="arrow-down" class="size-4" /></button>
                            <button type="button" class="btn btn-ghost btn-sm text-danger" @click="remove(i)" aria-label="Hapus"><x-svg-icon name="trash-2" class="size-4" /></button>
                        </div>
                    </li>
                </template>
            </ol>
            <p x-show="!rows.length" class="px-5 py-4 text-sm text-ink-muted sm:px-6">Belum ada persyaratan dokumen.</p>
        </section>

        <section class="panel" x-data="repeater(@js(array_values($fields)), { key: null, label: '', type: 'text', required: true, help: '' })">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Isian tambahan</h2>
                    <p class="text-sm text-ink-muted">Informasi khusus jenis surat ini, misalnya nama usaha. Bisa dipakai di template.</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm shrink-0" @click="add()"><x-svg-icon name="plus" class="size-4" />Tambah</button>
            </div>
            <ol class="divide-y divide-line">
                <template x-for="(row, i) in rows" :key="row._key ?? row.key ?? i">
                    <li class="grid gap-3 px-5 py-4 sm:grid-cols-[1fr_10rem_auto] sm:items-start sm:px-6">
                        <input type="hidden" :name="`fields[${i}][key]`" :value="row.key">
                        <div>
                            <label class="field-label text-sm" :for="`f-label-${i}`">Label pertanyaan</label>
                            <input class="input" :id="`f-label-${i}`" :name="`fields[${i}][label]`" x-model="row.label" required>
                            <input class="input mt-2 text-sm" :name="`fields[${i}][help]`" x-model="row.help" placeholder="Petunjuk pengisian (opsional)" aria-label="Petunjuk pengisian">
                            <p class="mt-1 text-xs text-ink-subtle" x-show="row.key">Placeholder: <code x-text="'{' + '{' + row.key + '}' + '}'"></code></p>
                        </div>
                        <div>
                            <label class="field-label text-sm" :for="`f-type-${i}`">Jenis</label>
                            <select class="input" :id="`f-type-${i}`" :name="`fields[${i}][type]`" x-model="row.type">
                                @foreach (\App\Models\LetterType::FIELD_TYPES as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-1 sm:pt-7">
                            <input type="hidden" :name="`fields[${i}][required]`" value="0">
                            <label class="mr-2 flex items-center gap-2 text-sm"><input type="checkbox" class="check" :name="`fields[${i}][required]`" value="1" x-model="row.required"> Wajib</label>
                            <button type="button" class="btn btn-ghost btn-sm text-ink-muted" @click="move(i, -1)" :disabled="i === 0" aria-label="Naikkan"><x-svg-icon name="arrow-up" class="size-4" /></button>
                            <button type="button" class="btn btn-ghost btn-sm text-ink-muted" @click="move(i, 1)" :disabled="i === rows.length - 1" aria-label="Turunkan"><x-svg-icon name="arrow-down" class="size-4" /></button>
                            <button type="button" class="btn btn-ghost btn-sm text-danger" @click="remove(i)" aria-label="Hapus"><x-svg-icon name="trash-2" class="size-4" /></button>
                        </div>
                    </li>
                </template>
            </ol>
            <p x-show="!rows.length" class="px-5 py-4 text-sm text-ink-muted sm:px-6">Tidak ada isian tambahan. Warga cukup menulis keperluan.</p>
        </section>

        <div class="flex gap-2">
            <x-button>{{ $editing ? 'Simpan perubahan' : 'Simpan dan lanjut ke template' }}</x-button>
            <x-button :href="route('rw.letter-types.index')" variant="secondary">Batal</x-button>
        </div>
    </form>
</x-layouts.app>
