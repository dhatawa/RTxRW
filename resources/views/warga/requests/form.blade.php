@php
    $editing = $letterRequest !== null;
    $docs = $editing ? $letterRequest->documents->keyBy('letter_requirement_id') : collect();
    $isRevision = $editing && $letterRequest->status === \App\Enums\RequestStatus::RevisionRequired;
@endphp
<x-layouts.app :title="($editing ? 'Ubah ' : 'Ajukan ').$type->name">
    <x-page-header
        :eyebrow="$editing ? $letterRequest->reference_number : 'Pengajuan baru'"
        :title="$type->name"
        :back="$editing ? route('warga.requests.show', $letterRequest) : route('warga.requests.create')"
        :back-label="$editing ? 'Kembali ke detail pengajuan' : 'Pilih jenis surat lain'" />

    @if ($isRevision && $letterRequest->last_note)
        <x-alert tone="warning" title="Catatan perbaikan" class="mb-6">{{ $letterRequest->last_note }}</x-alert>
    @endif

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $editing ? route('warga.requests.update', $letterRequest) : route('warga.requests.store', $type) }}"
          class="grid gap-8 lg:grid-cols-[1fr_18rem]">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-8">
            {{-- 1. Pemohon (dari profil, hanya-baca) --}}
            <section class="panel">
                <div class="panel-header">
                    <h2 class="panel-title"><span class="text-ink-subtle">1.</span> Data pemohon</h2>
                    <a href="{{ route('warga.profile.edit') }}" class="text-sm font-medium">Ubah di profil</a>
                </div>
                <div class="px-5 sm:px-6">
                    <x-summary-list :rows="[
                        ['Nama', $resident->full_name],
                        ['NIK', $resident->maskedNik()],
                        ['Alamat', $resident->fullAddress()],
                        ['Pekerjaan', $resident->occupation],
                    ]" />
                </div>
            </section>

            {{-- 2. Keperluan & isian khusus jenis surat --}}
            <section class="panel">
                <div class="panel-header"><h2 class="panel-title"><span class="text-ink-subtle">2.</span> Keterangan surat</h2></div>
                <div class="space-y-5 px-5 py-5 sm:px-6">
                    <x-field name="purpose" label="Untuk keperluan apa surat ini?" type="textarea" rows="3" required
                             :value="$letterRequest?->purpose"
                             hint="Tulis singkat dan jelas. Kalimat ini akan tercetak di surat, misalnya: Persyaratan pembukaan rekening tabungan." />

                    @foreach ($type->fieldDefinitions() as $field)
                        <x-field :name="'fields['.$field['key'].']'" :label="$field['label']"
                                 :type="match ($field['type']) { 'textarea' => 'textarea', 'date' => 'date', 'number' => 'number', default => 'text' }"
                                 :value="$letterRequest?->fieldValue($field['key'])"
                                 :hint="$field['help']" :required="$field['required']" :optional="! $field['required']"
                                 rows="3" :width="$field['type'] === 'date' || $field['type'] === 'number' ? 'sm' : null"
                                 :inputmode="$field['type'] === 'number' ? 'numeric' : null" />
                    @endforeach
                </div>
            </section>

            {{-- 3. Dokumen --}}
            <section class="panel">
                <div class="panel-header"><h2 class="panel-title"><span class="text-ink-subtle">3.</span> Dokumen persyaratan</h2></div>
                <div class="px-5 pt-4 sm:px-6">
                    <p class="text-sm text-ink-muted">JPG, PNG, atau PDF, maksimal 2 MB per file. Pastikan tulisan terbaca jelas. Foto langsung dari kamera ponsel bisa dipakai.</p>
                </div>
                <ul class="divide-y divide-line">
                    @foreach ($type->requirements as $req)
                        @php
                            $existing = $docs->get($req->id);
                            $key = 'documents.'.$req->id;
                            $error = $errors->first($key);
                        @endphp
                        <li class="px-5 py-5 sm:px-6" x-data="fileInput(2048)">
                            <div @class(['field-group-error' => $error])>
                                <label for="doc-{{ $req->id }}" class="field-label">
                                    {{ $req->name }}
                                    @unless ($req->is_required)<span class="font-normal text-ink-muted">(opsional)</span>@endunless
                                </label>
                                @if ($req->description)<span class="field-hint">{{ $req->description }}</span>@endif
                                @if ($error)<p class="field-error"><x-svg-icon name="circle-alert" class="mt-0.5 size-4" />{{ $error }}</p>@endif
                                <p class="field-error" x-show="error" x-text="error" x-cloak></p>

                                @if ($existing)
                                    <p class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                                        <x-svg-icon name="file-check" class="size-4 text-primary" />
                                        <a href="{{ route('documents.show', $existing) }}" target="_blank" rel="noopener">{{ $existing->original_name }}</a>
                                        <span class="text-ink-subtle">· {{ $existing->humanSize() }}</span>
                                    </p>
                                @endif

                                <div class="flex flex-wrap items-center gap-3">
                                    <label for="doc-{{ $req->id }}" class="btn btn-secondary btn-sm">
                                        <x-svg-icon name="upload" class="size-4" />
                                        <span x-text="name ? 'Ganti file' : '{{ $existing ? 'Ganti file' : 'Pilih file' }}'">{{ $existing ? 'Ganti file' : 'Pilih file' }}</span>
                                    </label>
                                    <input id="doc-{{ $req->id }}" type="file" name="documents[{{ $req->id }}]" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                           class="sr-only" @change="pick($event)" @if ($error) aria-invalid="true" @endif>
                                    <span class="text-sm text-ink-muted" x-show="name" x-cloak><span x-text="name" class="font-medium text-ink"></span> · <span x-text="size"></span></span>
                                    @unless ($existing)
                                        <span class="text-sm text-ink-subtle" x-show="!name">Belum ada file</span>
                                    @endunless
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Aksi --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="panel px-5 py-5">
                <p class="font-semibold">Kirim ke Ketua RT {{ $resident->rt->number }}</p>
                <p class="mt-1 text-sm text-ink-muted">Setelah dikirim, pengajuan tidak bisa diubah kecuali diminta perbaikan.</p>
                <div class="mt-5 flex flex-col gap-2">
                    <x-button name="intent" value="submit" icon="arrow-right">{{ $isRevision ? 'Kirim perbaikan' : 'Kirim pengajuan' }}</x-button>
                    <x-button name="intent" value="draft" variant="secondary">Simpan sebagai draf</x-button>
                </div>
            </div>
            <p class="mt-4 px-1 text-sm text-ink-muted">Estimasi proses {{ $type->processing_days }} hari kerja setelah dikirim.</p>
        </aside>
    </form>
</x-layouts.app>
