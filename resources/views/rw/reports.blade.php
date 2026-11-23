@php
    use App\Enums\RequestStatus as S;
    $done = collect(S::done())->sum(fn ($s) => $byStatus[$s->value] ?? 0);
    $rejected = ($byStatus[S::RejectedByRt->value] ?? 0) + ($byStatus[S::RejectedByRw->value] ?? 0);
    $maxTrend = max(1, $trend->max(fn ($t) => max($t['submitted'], $t['issued'])));
    $fmtHours = fn ($h) => $h === null ? '—' : ($h < 48 ? str_replace('.', ',', (string) $h).' jam' : str_replace('.', ',', (string) round($h / 24, 1)).' hari');
@endphp
<x-layouts.app title="Laporan">
    <x-page-header title="Laporan pelayanan" :description="'Periode '.$from->translatedFormat('j F Y').' – '.$to->translatedFormat('j F Y').', berdasarkan tanggal pengajuan dikirim.'">
        <x-slot:actions>
            <x-button :href="route('rw.reports.export', request()->only('dari', 'sampai'))" variant="secondary" icon="download">Unduh CSV</x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="mb-8 flex flex-wrap items-end gap-3">
        <div><label for="dari" class="field-label text-sm">Dari</label><input id="dari" type="date" name="dari" value="{{ $from->format('Y-m-d') }}" class="input"></div>
        <div><label for="sampai" class="field-label text-sm">Sampai</label><input id="sampai" type="date" name="sampai" value="{{ $to->format('Y-m-d') }}" class="input"></div>
        <x-button variant="secondary">Tampilkan</x-button>
        <a href="{{ route('rw.reports.index', ['dari' => now()->startOfYear()->format('Y-m-d')]) }}" class="pb-2.5 text-sm font-medium">Tahun ini</a>
    </form>

    <x-stats :items="[
        ['label' => 'Pengajuan masuk', 'value' => $total],
        ['label' => 'Surat terbit', 'value' => $done, 'hint' => $total ? round($done / $total * 100).'% dari pengajuan' : null],
        ['label' => 'Ditolak', 'value' => $rejected],
        ['label' => 'Rata-rata sampai terbit', 'value' => $fmtHours($avgHours), 'hint' => 'Verifikasi RT rata-rata '.$fmtHours($avgRtHours)],
    ]" />

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Tren 6 bulan</h2>
                <div class="flex gap-4 text-xs text-ink-muted">
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-secondary"></span>Masuk</span>
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-primary"></span>Terbit</span>
                </div>
            </div>
            <div class="px-5 py-5">
                <div class="flex h-44 items-end gap-3" role="img" aria-label="Grafik batang jumlah pengajuan masuk dan surat terbit per bulan">
                    @foreach ($trend as $t)
                        <div class="flex h-full flex-1 flex-col justify-end">
                            <div class="flex flex-1 items-end justify-center gap-1">
                                <div class="w-1/2 max-w-5 rounded-t-sm bg-secondary" style="height: {{ $t['submitted'] / $maxTrend * 100 }}%" title="{{ $t['submitted'] }} masuk"></div>
                                <div class="w-1/2 max-w-5 rounded-t-sm bg-primary" style="height: {{ $t['issued'] / $maxTrend * 100 }}%" title="{{ $t['issued'] }} terbit"></div>
                            </div>
                            <p class="mt-2 border-t border-line pt-1.5 text-center text-xs text-ink-muted">{{ $t['label'] }}</p>
                        </div>
                    @endforeach
                </div>
                <table class="sr-only">
                    <caption>Tren bulanan</caption>
                    <tr><th>Bulan</th><th>Masuk</th><th>Terbit</th></tr>
                    @foreach ($trend as $t)<tr><td>{{ $t['label'] }}</td><td>{{ $t['submitted'] }}</td><td>{{ $t['issued'] }}</td></tr>@endforeach
                </table>
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="panel-header"><h2 class="panel-title">Per RT</h2></div>
            <table class="table-civic">
                <thead><tr><th>RT</th><th class="text-right">Masuk</th><th class="text-right">Terbit</th><th class="text-right">Ditolak</th></tr></thead>
                <tbody>
                    @foreach ($byRt as $row)
                        <tr><td class="font-semibold">{{ $row['number'] }}</td><td class="text-right tabular-nums">{{ $row['total'] }}</td><td class="text-right tabular-nums">{{ $row['done'] }}</td><td class="text-right tabular-nums">{{ $row['rejected'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="panel overflow-hidden lg:col-span-2">
            <div class="panel-header"><h2 class="panel-title">Per jenis surat</h2></div>
            @if ($byType->isEmpty())
                <p class="px-5 py-4 text-sm text-ink-muted">Tidak ada pengajuan pada periode ini.</p>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($byType as $row)
                        <li class="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-1.5 px-5 py-3 sm:grid-cols-[16rem_1fr_6rem]">
                            <span class="text-[0.9375rem] font-medium">{{ $row['name'] }}</span>
                            <span class="order-3 col-span-2 h-2 overflow-hidden rounded-full bg-canvas sm:order-none sm:col-span-1">
                                <span class="block h-full bg-primary" style="width: {{ $row['total'] / max(1, $byType->max('total')) * 100 }}%"></span>
                            </span>
                            <span class="text-right text-sm tabular-nums">{{ $row['total'] }} <span class="text-ink-subtle">/ {{ $row['done'] }} terbit</span></span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.app>
