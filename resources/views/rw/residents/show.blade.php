<x-layouts.app :title="$resident->full_name">
    <x-page-header :eyebrow="'Warga RT '.$resident->rt->number" :title="$resident->full_name" :back="route('rw.residents.index')" back-label="Data warga" />

    <div class="grid gap-8 lg:grid-cols-[1.4fr_1fr]">
        <section class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Data kependudukan</h2>
                <x-verification-badge :status="$resident->verification_status" />
            </div>
            <div class="px-5 sm:px-6">@include('partials.resident-data')</div>
            @if ($resident->verified_at)
                <p class="border-t border-line px-5 py-3 text-sm text-ink-muted sm:px-6">
                    {{ $resident->verification_status->label() }} oleh {{ $resident->verifier?->name }}, {{ $resident->verified_at->translatedFormat('j F Y') }}.
                </p>
            @endif
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">Riwayat pengajuan</h2>
            <div class="panel">
                @forelse ($requests as $req)
                    <a href="{{ route('rw.requests.show', $req) }}" class="flex items-center justify-between gap-3 border-b border-line px-5 py-3.5 text-ink no-underline last:border-b-0 hover:bg-canvas">
                        <span><span class="block font-medium">{{ $req->letterType->name }}</span><span class="text-xs text-ink-subtle">{{ $req->submitted_at?->translatedFormat('j M Y') }}</span></span>
                        <x-status :status="$req->status" size="sm" />
                    </a>
                @empty
                    <p class="px-5 py-4 text-sm text-ink-muted">Belum pernah mengajukan surat.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
