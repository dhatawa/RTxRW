@php $r = $letterRequest; @endphp
<x-layouts.app :title="$r->letterType->name.' — '.$r->resident->full_name">
    <x-page-header :eyebrow="$r->reference_number" :title="$r->letterType->name" :back="route('rt.requests.index')" back-label="Pengajuan masuk">
        <x-slot:actions><x-status :status="$r->status" /></x-slot:actions>
    </x-page-header>

    <div class="grid gap-8 lg:grid-cols-[1fr_22rem]">
        <div class="space-y-8">
            @if ($r->revision_count && $canDecide)
                <x-alert tone="info" title="Pengajuan ini sudah diperbaiki warga">
                    Diperbaiki {{ $r->revision_count }} kali. Lihat catatan sebelumnya di riwayat.
                </x-alert>
            @endif

            <section class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Pemohon</h2>
                    <div class="flex items-center gap-4">
                        <x-verification-badge :status="$r->resident->verification_status" />
                        <a href="{{ route('rt.residents.show', $r->resident) }}" class="text-sm font-medium">Profil warga</a>
                    </div>
                </div>
                <div class="px-5 sm:px-6">@include('partials.resident-data', ['resident' => $r->resident])</div>
            </section>

            @include('partials.request-details', ['letterRequest' => $r])

            @if ($r->issuedLetter)
                <section class="panel px-5 py-4 sm:px-6">
                    <p class="font-semibold">Surat terbit: {{ $r->issuedLetter->letter_number }}</p>
                    <a href="{{ route('letters.download', $r) }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold"><x-svg-icon name="download" class="size-4" />Unduh arsip PDF</a>
                </section>
            @endif
        </div>

        <aside class="space-y-8 lg:sticky lg:top-24 lg:self-start">
            @if ($canDecide)
                @include('partials.decision-panel', [
                    'action' => route('rt.requests.decide', $r),
                    'approveLabel' => 'Setujui dan teruskan ke RW',
                    'approveHint' => 'Data dan dokumen sudah sesuai. Ketua RW akan memberi persetujuan akhir.',
                ])
            @endif

            <section>
                <h2 class="mb-3 text-lg font-semibold">Riwayat</h2>
                <div class="panel px-5 py-5"><x-timeline :histories="$r->histories" /></div>
            </section>
        </aside>
    </div>
</x-layouts.app>
