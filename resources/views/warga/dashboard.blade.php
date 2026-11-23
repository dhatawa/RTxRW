<x-layouts.app title="Beranda">
    <x-page-header :eyebrow="'RT '.$resident->rt->number.' / RW '.$resident->rt->rw->number" :title="'Halo, '.strtok($resident->full_name, ' ')">
        <x-slot:actions>
            @if ($resident->canSubmitLetters())
                <x-button :href="route('warga.requests.create')" icon="file-plus">Ajukan surat</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Hambatan akun ditampilkan paling atas karena menghalangi semua hal lain --}}
    @unless ($resident->isProfileComplete())
        <x-alert tone="warning" title="Data kependudukan belum lengkap" class="mb-6">
            Lengkapi data diri Anda agar Ketua RT dapat memverifikasi dan Anda bisa mengajukan surat.
            <a href="{{ route('warga.profile.edit') }}">Lengkapi sekarang</a>
        </x-alert>
    @else
        @if ($resident->verification_status === \App\Enums\VerificationStatus::Pending)
            <x-alert tone="info" title="Menunggu verifikasi Ketua RT {{ $resident->rt->number }}" class="mb-6">
                Data Anda sedang diperiksa. Anda akan mendapat notifikasi begitu terverifikasi, lalu bisa langsung mengajukan surat.
            </x-alert>
        @elseif ($resident->verification_status === \App\Enums\VerificationStatus::Rejected)
            <x-alert tone="danger" title="Data perlu diperbaiki" class="mb-6">
                {{ $resident->verification_note }} <a href="{{ route('warga.profile.edit') }}">Perbaiki data</a>
            </x-alert>
        @endif
    @endunless

    <x-stats :items="[
        ['label' => 'Total pengajuan', 'value' => $stats['total'], 'href' => route('warga.requests.index')],
        ['label' => 'Sedang diproses', 'value' => $stats['in_progress'], 'href' => route('warga.requests.index', ['status' => 'proses']), 'hint' => 'Di Ketua RT atau RW'],
        ['label' => 'Perlu tindakan Anda', 'value' => $stats['needs_action'], 'href' => route('warga.requests.index', ['status' => 'perbaikan']), 'emphasis' => true, 'hint' => 'Draf dan perbaikan'],
        ['label' => 'Surat terbit', 'value' => $stats['done'], 'href' => route('warga.requests.index', ['status' => 'selesai'])],
    ]" />

    <div class="mt-8 grid gap-8 lg:grid-cols-[1.5fr_1fr]">
        <div class="space-y-8">
            @if ($attention->isNotEmpty())
                <section aria-labelledby="perhatian">
                    <h2 id="perhatian" class="mb-3 text-lg font-semibold">Perlu perhatian</h2>
                    <ul class="panel divide-y divide-line">
                        @foreach ($attention as $req)
                            <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <a href="{{ route('warga.requests.show', $req) }}" class="font-semibold text-ink no-underline hover:underline">{{ $req->letterType->name }}</a>
                                    <p class="mt-0.5 text-sm text-ink-muted">
                                        @switch($req->status)
                                            @case(\App\Enums\RequestStatus::Issued) Surat sudah terbit dan siap diunduh. @break
                                            @case(\App\Enums\RequestStatus::RevisionRequired) {{ \Illuminate\Support\Str::limit($req->last_note, 110) }} @break
                                            @default Draf belum dikirim.
                                        @endswitch
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    @if ($req->status === \App\Enums\RequestStatus::Issued)
                                        <x-button :href="route('letters.download', $req)" size="sm" icon="download">Unduh surat</x-button>
                                    @elseif ($req->status === \App\Enums\RequestStatus::RevisionRequired)
                                        <x-button :href="route('warga.requests.edit', $req)" size="sm" variant="secondary" icon="pencil">Perbaiki</x-button>
                                    @else
                                        <x-button :href="route('warga.requests.show', $req)" size="sm" variant="secondary">Lanjutkan</x-button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section aria-labelledby="berjalan">
                <div class="mb-3 flex items-baseline justify-between">
                    <h2 id="berjalan" class="text-lg font-semibold">Sedang diproses</h2>
                    <a href="{{ route('warga.requests.index') }}" class="text-sm font-medium">Semua pengajuan</a>
                </div>
                <div class="panel">
                    @forelse ($active as $req)
                        <a href="{{ route('warga.requests.show', $req) }}" class="block border-b border-line px-5 py-4 text-ink no-underline last:border-b-0 hover:bg-canvas">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold">{{ $req->letterType->name }}</p>
                                    <p class="mt-0.5 font-mono text-xs text-ink-subtle">{{ $req->reference_number }}</p>
                                </div>
                                <x-status :status="$req->status" size="sm" />
                            </div>
                            <x-progress :request="$req" class="mt-4" />
                        </a>
                    @empty
                        <x-empty title="Tidak ada pengajuan yang sedang diproses" icon="files">
                            Pengajuan yang Anda kirim akan muncul di sini beserta tahapannya.
                        </x-empty>
                    @endforelse
                </div>
            </section>
        </div>

        <section aria-labelledby="aktivitas">
            <h2 id="aktivitas" class="mb-3 text-lg font-semibold">Aktivitas terbaru</h2>
            <div class="panel px-5 py-5">
                @forelse ($activity as $h)
                    <div class="flex gap-3 pb-4 last:pb-0">
                        <span class="mt-2 size-1.5 shrink-0 rounded-full bg-secondary" aria-hidden="true"></span>
                        <div class="min-w-0 text-[0.9375rem]">
                            <p><span class="font-medium">{{ $h->action->label() }}</span> <span class="text-ink-muted">— {{ $h->letterRequest->letterType->name }}</span></p>
                            <p class="text-xs text-ink-subtle">{{ $h->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-ink-muted">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
