<x-layouts.app title="Beranda RT">
    <x-page-header :eyebrow="'RT '.$rt->number.' / RW '.$rt->rw->number" title="Ringkasan pelayanan" :description="$rt->area_description" />

    <x-stats :items="[
        ['label' => 'Pengajuan menunggu Anda', 'value' => $stats['pending'], 'href' => route('rt.requests.index'), 'emphasis' => true],
        ['label' => 'Warga menunggu verifikasi', 'value' => $stats['pending_residents'], 'href' => route('rt.residents.index', ['status' => 'pending']), 'emphasis' => true],
        ['label' => 'Pengajuan bulan ini', 'value' => $stats['month_total'], 'hint' => $stats['month_forwarded'].' diteruskan ke RW'],
        ['label' => 'Surat terbit bulan ini', 'value' => $stats['month_done'], 'hint' => 'Dari '.$stats['residents'].' warga terdaftar'],
    ]" />

    <div class="mt-8 grid gap-8 lg:grid-cols-[1.5fr_1fr]">
        <section aria-labelledby="antrean">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 id="antrean" class="text-lg font-semibold">Antrean pemeriksaan</h2>
                <a href="{{ route('rt.requests.index') }}" class="text-sm font-medium">Semua pengajuan</a>
            </div>
            <div class="panel">
                @forelse ($queue as $req)
                    <a href="{{ route('rt.requests.show', $req) }}" class="flex items-center gap-4 border-b border-line px-5 py-4 text-ink no-underline last:border-b-0 hover:bg-canvas">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">{{ $req->resident->full_name }}</p>
                            <p class="mt-0.5 text-sm text-ink-muted">{{ $req->letterType->name }}</p>
                        </div>
                        <div class="text-right text-sm">
                            <p class="text-ink-muted">{{ $req->submitted_at->diffForHumans() }}</p>
                            @if ($req->revision_count)<p class="text-xs text-warning">Perbaikan ke-{{ $req->revision_count }}</p>@endif
                        </div>
                        <x-svg-icon name="chevron-right" class="size-5 text-ink-subtle" />
                    </a>
                @empty
                    <x-empty title="Tidak ada pengajuan yang menunggu" icon="circle-check">Semua pengajuan warga RT {{ $rt->number }} sudah Anda periksa.</x-empty>
                @endforelse
            </div>
        </section>

        <div class="space-y-8">
            <section aria-labelledby="warga-baru">
                <div class="mb-3 flex items-baseline justify-between">
                    <h2 id="warga-baru" class="text-lg font-semibold">Warga baru</h2>
                    <a href="{{ route('rt.residents.index', ['status' => 'pending']) }}" class="text-sm font-medium">Lihat semua</a>
                </div>
                <div class="panel">
                    @forelse ($newResidents as $res)
                        <a href="{{ route('rt.residents.show', $res) }}" class="flex items-center justify-between gap-3 border-b border-line px-5 py-3.5 text-ink no-underline last:border-b-0 hover:bg-canvas">
                            <span class="min-w-0">
                                <span class="block truncate font-medium">{{ $res->full_name }}</span>
                                <span class="block text-xs text-ink-subtle">Mendaftar {{ $res->created_at->diffForHumans() }}</span>
                            </span>
                            <span class="text-sm font-semibold text-primary">Periksa</span>
                        </a>
                    @empty
                        <p class="px-5 py-4 text-sm text-ink-muted">Tidak ada pendaftaran baru.</p>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="terbaru">
                <h2 id="terbaru" class="mb-3 text-lg font-semibold">Aktivitas terbaru</h2>
                <div class="panel px-5 py-4">
                    @forelse ($recent as $h)
                        <div class="border-b border-line py-2.5 text-[0.9375rem] first:pt-0 last:border-b-0 last:pb-0">
                            <p><span class="font-medium">{{ $h->action->label() }}</span></p>
                            <p class="text-sm text-ink-muted">{{ $h->letterRequest->resident->full_name }} · {{ $h->letterRequest->letterType->name }}</p>
                            <p class="text-xs text-ink-subtle">{{ $h->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-ink-muted">Belum ada aktivitas.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
