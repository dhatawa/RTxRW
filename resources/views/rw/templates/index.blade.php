<x-layouts.app title="Template surat">
    <x-page-header title="Template surat" description="Isi surat untuk setiap jenis. Setiap perubahan disimpan sebagai versi baru, sehingga surat yang sudah terbit tetap tercatat memakai versi lamanya." />

    <div class="panel">
        @foreach ($letterTypes as $type)
            <a href="{{ route('rw.templates.edit', $type) }}" class="flex items-center gap-4 border-b border-line px-5 py-4 text-ink no-underline last:border-b-0 hover:bg-canvas">
                <x-svg-icon name="file-text" class="size-5 text-ink-subtle" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $type->name }}</p>
                    <p class="text-sm text-ink-muted">
                        @if ($type->activeTemplate)
                            Versi {{ $type->activeTemplate->version }} aktif · {{ $type->templates_count }} versi tersimpan · diubah {{ $type->activeTemplate->created_at->diffForHumans() }}
                        @else
                            <span class="font-semibold text-warning">Belum ada template.</span> Surat jenis ini belum bisa diterbitkan.
                        @endif
                    </p>
                </div>
                <x-svg-icon name="chevron-right" class="size-5 text-ink-subtle" />
            </a>
        @endforeach
    </div>
</x-layouts.app>
