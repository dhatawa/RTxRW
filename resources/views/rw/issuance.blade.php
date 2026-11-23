<x-layouts.app title="Penerbitan surat">
    <x-page-header title="Penerbitan surat" description="Pengajuan yang sudah Anda setujui dan arsip surat yang telah terbit." />

    <section aria-labelledby="siap" class="mb-10">
        <h2 id="siap" class="mb-3 text-lg font-semibold">Siap diterbitkan <span class="font-normal text-ink-subtle">({{ $ready->count() }})</span></h2>
        <div class="panel">
            @forelse ($ready as $req)
                <div class="flex flex-col gap-3 border-b border-line px-5 py-4 last:border-b-0 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('rw.requests.show', $req) }}" class="font-semibold">{{ $req->resident->full_name }}</a>
                        <p class="text-sm text-ink-muted">{{ $req->letterType->name }} · RT {{ $req->rt->number }} · disetujui {{ $req->rw_decided_at?->diffForHumans() }}</p>
                    </div>
                    @if ($req->letterType->activeTemplate)
                        <form method="POST" action="{{ route('rw.requests.issue', $req) }}" x-data="{ busy: false }" @submit="busy = true">
                            @csrf
                            <x-button size="sm" icon="stamp" x-bind:disabled="busy"><span x-text="busy ? 'Menerbitkan…' : 'Terbitkan'">Terbitkan</span></x-button>
                        </form>
                    @else
                        <a href="{{ route('rw.templates.edit', $req->letterType) }}" class="text-sm font-semibold text-warning">Template belum ada</a>
                    @endif
                </div>
            @empty
                <x-empty title="Tidak ada surat yang menunggu diterbitkan" icon="stamp" />
            @endforelse
        </div>
    </section>

    <section aria-labelledby="arsip">
        <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <h2 id="arsip" class="text-lg font-semibold">Arsip surat terbit</h2>
            <form method="GET" class="flex gap-2" role="search">
                <label for="q" class="sr-only">Cari surat</label>
                <input id="q" name="q" value="{{ $search }}" class="input sm:w-72" placeholder="Nomor surat, nama, atau kode">
                <x-button variant="secondary" icon="search"><span class="sr-only">Cari</span></x-button>
            </form>
        </div>
        <div class="panel overflow-hidden">
            @if ($issued->isEmpty())
                <x-empty title="Belum ada surat yang cocok" icon="file-text" />
            @else
                <table class="table-civic hidden md:table">
                    <thead><tr><th>Nomor surat</th><th>Pemohon</th><th>Terbit</th><th>Diunduh</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                        @foreach ($issued as $l)
                            <tr>
                                <td class="font-mono text-sm">{{ $l->letter_number }}<span class="block font-sans text-xs text-ink-subtle">{{ $l->letterRequest->letterType->name }}</span></td>
                                <td>{{ $l->letterRequest->resident->full_name }}<span class="block text-xs text-ink-subtle">RT {{ $l->letterRequest->rt->number }}</span></td>
                                <td class="whitespace-nowrap text-sm text-ink-muted">{{ $l->issued_at->translatedFormat('j M Y') }}</td>
                                <td class="text-sm">{{ $l->first_downloaded_at ? $l->first_downloaded_at->translatedFormat('j M Y') : 'Belum' }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <a href="{{ route('letters.download', $l->letterRequest) }}" class="font-semibold">PDF</a>
                                    <span class="text-line-strong">|</span>
                                    <a href="{{ route('rw.requests.show', $l->letterRequest) }}" class="font-semibold">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <ul class="divide-y divide-line md:hidden">
                    @foreach ($issued as $l)
                        <li class="px-4 py-3.5">
                            <a href="{{ route('rw.requests.show', $l->letterRequest) }}" class="font-semibold">{{ $l->letterRequest->resident->full_name }}</a>
                            <p class="font-mono text-xs">{{ $l->letter_number }}</p>
                            <p class="text-xs text-ink-subtle">{{ $l->issued_at->translatedFormat('j M Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="mt-6">{{ $issued->links() }}</div>
    </section>
</x-layouts.app>
