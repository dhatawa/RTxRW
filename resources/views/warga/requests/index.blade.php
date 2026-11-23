<x-layouts.app title="Pengajuan saya">
    <x-page-header title="Pengajuan saya" description="Semua surat yang pernah Anda ajukan, termasuk draf.">
        <x-slot:actions>
            <x-button :href="route('warga.requests.create')" icon="file-plus">Ajukan surat</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-tabs param="status" :current="$filter ?? ''" :items="['' => 'Semua', 'proses' => 'Diproses', 'perbaikan' => 'Draf & perbaikan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak']" />

    <div class="panel">
        @forelse ($requests as $req)
            <a href="{{ route('warga.requests.show', $req) }}" class="flex flex-col gap-2 border-b border-line px-5 py-4 text-ink no-underline last:border-b-0 hover:bg-canvas sm:flex-row sm:items-center sm:gap-6">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $req->letterType->name }}</p>
                    <p class="mt-0.5 text-sm text-ink-muted">
                        <span class="font-mono text-xs">{{ $req->reference_number }}</span>
                        <span aria-hidden="true">·</span>
                        {{ $req->submitted_at ? 'Dikirim '.$req->submitted_at->translatedFormat('j M Y') : 'Dibuat '.$req->created_at->translatedFormat('j M Y') }}
                    </p>
                </div>
                <x-status :status="$req->status" class="self-start sm:self-center" />
                <x-svg-icon name="chevron-right" class="hidden size-5 text-ink-subtle sm:block" />
            </a>
        @empty
            <x-empty title="Belum ada pengajuan" icon="files">
                Mulai dengan memilih jenis surat yang Anda butuhkan.
                <x-slot:action><x-button :href="route('warga.requests.create')" icon="file-plus">Ajukan surat</x-button></x-slot:action>
            </x-empty>
        @endforelse
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
</x-layouts.app>
