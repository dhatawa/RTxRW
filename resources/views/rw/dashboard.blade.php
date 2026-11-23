<x-layouts.app title="Beranda RW">
    <x-page-header :eyebrow="'RW '.$rw->number.' · Kel. '.$rw->kelurahan" title="Ringkasan pelayanan">
        <x-slot:actions>
            <x-button :href="route('rw.reports.index')" variant="secondary" icon="chart-column">Laporan</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-stats :items="[
        ['label' => 'Menunggu persetujuan Anda', 'value' => $stats['pending_rw'], 'href' => route('rw.requests.index'), 'emphasis' => true],
        ['label' => 'Siap diterbitkan', 'value' => $stats['to_issue'], 'href' => route('rw.issuance.index'), 'emphasis' => true],
        ['label' => 'Masih di Ketua RT', 'value' => $stats['pending_rt'], 'href' => route('rw.requests.index', ['tab' => 'di-rt'])],
        ['label' => 'Surat terbit bulan ini', 'value' => $stats['month_issued'], 'hint' => $stats['residents'].' warga terdaftar'],
    ]" />

    <div class="mt-8 grid gap-8 xl:grid-cols-[1.4fr_1fr]">
        <section aria-labelledby="antrean">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 id="antrean" class="text-lg font-semibold">Antrean Anda</h2>
                <a href="{{ route('rw.requests.index') }}" class="text-sm font-medium">Semua pengajuan</a>
            </div>
            <div class="panel">
                @forelse ($queue as $req)
                    <a href="{{ route('rw.requests.show', $req) }}" class="flex items-center gap-4 border-b border-line px-5 py-4 text-ink no-underline last:border-b-0 hover:bg-canvas">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">{{ $req->resident->full_name }} <span class="font-normal text-ink-subtle">· RT {{ $req->rt->number }}</span></p>
                            <p class="mt-0.5 text-sm text-ink-muted">{{ $req->letterType->name }}</p>
                        </div>
                        <div class="hidden text-right sm:block">
                            <x-status :status="$req->status" size="sm" />
                            <p class="mt-1 text-xs text-ink-subtle">RT setuju {{ $req->rt_decided_at?->diffForHumans() }}</p>
                        </div>
                        <x-svg-icon name="chevron-right" class="size-5 text-ink-subtle" />
                    </a>
                @empty
                    <x-empty title="Antrean kosong" icon="circle-check">Tidak ada pengajuan yang menunggu persetujuan atau penerbitan.</x-empty>
                @endforelse
            </div>
        </section>

        <section aria-labelledby="per-rt">
            <h2 id="per-rt" class="mb-3 text-lg font-semibold">Per RT</h2>
            <div class="panel overflow-hidden">
                <table class="table-civic">
                    <thead><tr><th>RT</th><th>Ketua</th><th class="text-right">Antre</th><th class="text-right">Bulan ini</th></tr></thead>
                    <tbody>
                        @foreach ($perRt as $rt)
                            <tr>
                                <td class="font-semibold tabular-nums">{{ $rt->number }}</td>
                                <td class="text-sm">{{ $rt->ketua?->name ?? '—' }}<span class="block text-xs text-ink-subtle">{{ $rt->residents_count }} warga</span></td>
                                <td class="text-right tabular-nums">
                                    @if ($rt->pending_rt_count)
                                        <a href="{{ route('rw.requests.index', ['tab' => 'di-rt', 'rt' => $rt->id]) }}" class="font-semibold text-warning">{{ $rt->pending_rt_count }}</a>
                                    @else 0 @endif
                                </td>
                                <td class="text-right tabular-nums">{{ $rt->month_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-ink-subtle">"Antre" = pengajuan yang belum diperiksa Ketua RT.</p>
        </section>
    </div>
</x-layouts.app>
