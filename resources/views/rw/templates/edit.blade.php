<x-layouts.app :title="'Template '.$type->name">
    <x-page-header eyebrow="Template surat" :title="$type->name" :back="route('rw.templates.index')" back-label="Template surat"
                   :description="$template ? 'Menampilkan versi '.$template->version.($template->is_active ? ' (aktif)' : '').'. Menyimpan akan membuat versi baru dan langsung mengaktifkannya.' : 'Belum ada template. Buat versi pertama.'" />

    <div class="grid gap-8 xl:grid-cols-[1fr_20rem]">
        <form method="POST" action="{{ route('rw.templates.store', $type) }}" class="space-y-6" novalidate x-data x-ref="form">
            @csrf
            <div class="panel">
                <div class="space-y-5 px-5 py-6 sm:px-6">
                    <x-field name="title" label="Judul surat" :value="$template?->title ?? $type->name" required hint="Dicetak dengan huruf kapital dan bergaris bawah." />
                    <x-field name="opening" label="Pembuka" type="textarea" rows="3" :value="$template?->opening" required />
                    <x-field name="body" label="Isi" type="textarea" rows="10" :value="$template?->body" required
                             :hint="'Pisahkan paragraf dengan baris kosong. Tulis '.str_repeat('{', 2).'tabel_identitas'.str_repeat('}', 2).' di baris tersendiri untuk tabel data pemohon.'" />
                    <x-field name="closing" label="Penutup" type="textarea" rows="2" :value="$template?->closing" required />
                </div>
                <div class="flex flex-wrap gap-2 border-t border-line bg-canvas px-5 py-4 sm:px-6">
                    <x-button>Simpan sebagai versi baru</x-button>
                    <button type="submit" formaction="{{ route('rw.templates.preview', $type) }}" formtarget="_blank" class="btn btn-secondary">
                        <x-svg-icon name="eye" class="size-[1.125rem]" />Pratinjau
                    </button>
                </div>
            </div>
            <p class="text-sm text-ink-muted">Kop surat, nomor, tanggal, tanda tangan, dan kode QR verifikasi ditambahkan otomatis.</p>
        </form>

        <aside class="space-y-8">
            <section class="panel">
                <div class="panel-header"><h2 class="panel-title">Placeholder</h2></div>
                <ul class="max-h-96 divide-y divide-line overflow-y-auto text-sm">
                    @foreach ($placeholders as $key => $label)
                        <li class="px-5 py-2">
                            <code class="font-mono text-xs text-primary">{{ str_repeat('{', 2).$key.str_repeat('}', 2) }}</code>
                            <span class="block text-ink-muted">{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Versi</h2>
                <div class="panel">
                    @forelse ($type->templates as $t)
                        <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-3 last:border-b-0">
                            <div class="text-sm">
                                <a href="{{ route('rw.templates.edit', [$type, 'versi' => $t->version]) }}" class="font-semibold">Versi {{ $t->version }}</a>
                                @if ($t->is_active)<span class="ml-1 rounded-sm bg-primary-soft px-1.5 text-xs font-semibold text-primary">Aktif</span>@endif
                                <span class="block text-xs text-ink-subtle">{{ $t->created_at->translatedFormat('j M Y H:i') }}{{ $t->author ? ' · '.$t->author->name : '' }}</span>
                            </div>
                            @unless ($t->is_active)
                                <form method="POST" action="{{ route('rw.templates.activate', $t) }}">
                                    @csrf
                                    <button class="cursor-pointer text-sm font-semibold text-primary underline underline-offset-[3px]">Aktifkan</button>
                                </form>
                            @endunless
                        </div>
                    @empty
                        <p class="px-5 py-4 text-sm text-ink-muted">Belum ada versi.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</x-layouts.app>
