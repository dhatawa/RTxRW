@php
    $r = $letterRequest;
    $rtApproval = $r->histories->where('action', \App\Enums\WorkflowAction::RtApprove)->last();
@endphp
<x-layouts.app :title="$r->letterType->name.' — '.$r->resident->full_name">
    <x-page-header :eyebrow="$r->reference_number.' · RT '.$r->rt->number" :title="$r->letterType->name" :back="route('rw.requests.index')" back-label="Pengajuan">
        <x-slot:actions><x-status :status="$r->status" /></x-slot:actions>
    </x-page-header>

    <div class="grid gap-8 lg:grid-cols-[1fr_22rem]">
        <div class="space-y-8">
            {{-- Status verifikasi RT dulu: dasar keputusan RW --}}
            <section class="panel px-5 py-4 sm:px-6">
                <h2 class="text-sm font-semibold text-ink-muted">Verifikasi Ketua RT {{ $r->rt->number }}</h2>
                @if ($rtApproval)
                    <p class="mt-1 flex items-center gap-2 font-semibold text-primary"><x-svg-icon name="circle-check" class="size-5" />Disetujui {{ $rtApproval->actor?->name }}</p>
                    <p class="text-sm text-ink-muted">{{ $rtApproval->created_at->translatedFormat('l, j F Y H:i') }}</p>
                @else
                    <p class="mt-1 text-ink-muted">Belum diverifikasi Ketua RT.</p>
                @endif
            </section>

            <section class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Pemohon</h2>
                    <a href="{{ route('rw.residents.show', $r->resident) }}" class="text-sm font-medium">Profil warga</a>
                </div>
                <div class="px-5 sm:px-6">@include('partials.resident-data', ['resident' => $r->resident])</div>
            </section>

            @include('partials.request-details', ['letterRequest' => $r])
        </div>

        <aside class="space-y-8 lg:sticky lg:top-24 lg:self-start">
            @if ($canDecide)
                @include('partials.decision-panel', [
                    'action' => route('rw.requests.decide', $r),
                    'approveLabel' => 'Setujui',
                    'approveHint' => 'Surat siap diterbitkan. Anda bisa langsung menerbitkannya setelah ini.',
                ])
            @endif

            @if ($canIssue)
                <form method="POST" action="{{ route('rw.requests.issue', $r) }}" class="panel" x-data="{ busy: false }" @submit="busy = true">
                    @csrf
                    <div class="panel-header"><h2 class="panel-title">Terbitkan surat</h2></div>
                    <div class="space-y-3 px-5 py-5">
                        @if ($r->letterType->activeTemplate)
                            <p class="text-sm text-ink-muted">Sistem akan memberi nomor surat, membuat PDF dari template versi {{ $r->letterType->activeTemplate->version }}, dan memberi tahu warga.</p>
                            <x-button class="w-full" icon="stamp" x-bind:disabled="busy">
                                <span x-text="busy ? 'Menerbitkan…' : 'Terbitkan surat'">Terbitkan surat</span>
                            </x-button>
                        @else
                            <x-alert tone="warning">Jenis surat ini belum punya template aktif. <a href="{{ route('rw.templates.edit', $r->letterType) }}">Buat template</a></x-alert>
                        @endif
                    </div>
                </form>
            @endif

            @if ($r->issuedLetter)
                <section class="panel">
                    <div class="panel-header"><h2 class="panel-title">Surat terbit</h2></div>
                    <div class="px-5 py-2">
                        <x-summary-list :rows="[
                            ['Nomor', $r->issuedLetter->letter_number],
                            ['Terbit', $r->issuedLetter->issued_at->translatedFormat('j M Y, H:i')],
                            ['Diunduh warga', $r->issuedLetter->first_downloaded_at?->translatedFormat('j M Y, H:i') ?? 'Belum'],
                        ]" />
                    </div>
                    <div class="border-t border-line px-5 py-3">
                        <a href="{{ route('letters.download', $r) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold"><x-svg-icon name="download" class="size-4" />Unduh PDF</a>
                    </div>
                </section>
            @endif

            <section>
                <h2 class="mb-3 text-lg font-semibold">Riwayat</h2>
                <div class="panel px-5 py-5"><x-timeline :histories="$r->histories" /></div>
            </section>
        </aside>
    </div>
</x-layouts.app>
