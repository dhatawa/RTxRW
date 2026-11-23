@php $V = \App\Enums\VerificationStatus::class; @endphp
<x-layouts.app :title="$resident->full_name">
    <x-page-header eyebrow="Data warga" :title="$resident->full_name" :back="route('rt.residents.index')" back-label="Data warga" />

    <div class="grid gap-8 lg:grid-cols-[1.4fr_1fr]">
        <div class="space-y-8">
            <section class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Data kependudukan</h2>
                    <x-verification-badge :status="$resident->verification_status" />
                </div>
                <div class="px-5 sm:px-6">@include('partials.resident-data')</div>
            </section>

            <section>
                <h2 class="mb-3 text-lg font-semibold">Pengajuan surat</h2>
                <div class="panel">
                    @forelse ($requests as $req)
                        <a href="{{ route('rt.requests.show', $req) }}" class="flex items-center justify-between gap-3 border-b border-line px-5 py-3.5 text-ink no-underline last:border-b-0 hover:bg-canvas">
                            <span><span class="block font-medium">{{ $req->letterType->name }}</span><span class="text-xs text-ink-subtle">{{ $req->submitted_at?->translatedFormat('j M Y') }}</span></span>
                            <x-status :status="$req->status" size="sm" />
                        </a>
                    @empty
                        <p class="px-5 py-4 text-sm text-ink-muted">Belum pernah mengajukan surat.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
            @if ($resident->verification_status === $V::Verified)
                <div class="panel px-5 py-5">
                    <p class="font-semibold">Terverifikasi</p>
                    <p class="mt-1 text-sm text-ink-muted">Oleh {{ $resident->verifier?->name }} pada {{ $resident->verified_at?->translatedFormat('j F Y') }}. Warga ini dapat mengajukan surat.</p>
                </div>
            @else
                <form method="POST" action="{{ route('rt.residents.verify', $resident) }}" class="panel" x-data="{ decision: '{{ old('decision', '') }}' }" novalidate>
                    @csrf
                    <div class="panel-header"><h2 class="panel-title">Verifikasi warga</h2></div>
                    <div class="space-y-4 px-5 py-5">
                        @if ($resident->verification_status === $V::Rejected)
                            <x-alert tone="warning">Sebelumnya ditolak: {{ $resident->verification_note }}</x-alert>
                        @endif
                        @unless ($resident->isProfileComplete())
                            <x-alert tone="warning">Warga belum melengkapi: {{ collect($resident->missingProfileFields())->map(fn ($f) => __('validation.attributes.'.$f) === 'validation.attributes.'.$f ? str_replace('_', ' ', $f) : __('validation.attributes.'.$f))->implode(', ') }}.</x-alert>
                        @endunless
                        <p class="text-sm text-ink-muted">Pastikan orang ini memang tinggal di RT {{ $resident->rt->number }} dan NIK-nya sesuai.</p>
                        <fieldset class="space-y-2">
                            <legend class="sr-only">Keputusan</legend>
                            <label class="flex items-center gap-3 rounded-md border border-line px-3 py-2.5 has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                                <input type="radio" name="decision" value="verify" x-model="decision" class="size-5 accent-primary"> Benar warga saya, verifikasi
                            </label>
                            <label class="flex items-center gap-3 rounded-md border border-line px-3 py-2.5 has-[:checked]:border-danger has-[:checked]:bg-danger-soft">
                                <input type="radio" name="decision" value="reject" x-model="decision" class="size-5 accent-danger"> Data belum sesuai
                            </label>
                        </fieldset>
                        <div x-show="decision === 'reject'" x-cloak>
                            <x-field name="note" label="Apa yang perlu diperbaiki?" type="textarea" rows="3" hint="Catatan ini dikirim ke warga." />
                        </div>
                        <x-button class="w-full" x-bind:disabled="!decision">Simpan keputusan</x-button>
                    </div>
                </form>
            @endif
        </aside>
    </div>
</x-layouts.app>
