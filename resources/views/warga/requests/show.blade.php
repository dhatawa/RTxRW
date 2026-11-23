@php
    use App\Enums\RequestStatus as S;
    $r = $letterRequest;
@endphp
<x-layouts.app :title="$r->letterType->name">
    <x-page-header :eyebrow="$r->reference_number" :title="$r->letterType->name" :back="route('warga.requests.index')" back-label="Pengajuan saya" />

    {{-- Ringkasan status: apa yang terjadi sekarang dan apa yang perlu dilakukan --}}
    <section class="panel mb-8 overflow-hidden" aria-labelledby="status-now">
        <div class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-start sm:justify-between sm:px-6">
            <div class="min-w-0">
                <x-status :status="$r->status" />
                <p id="status-now" class="mt-3 text-lg font-semibold">
                    @switch($r->status)
                        @case(S::Draft) Pengajuan belum dikirim. @break
                        @case(S::PendingRt) Sedang diperiksa Ketua RT {{ $r->rt->number }}. @break
                        @case(S::PendingRw) Sudah disetujui RT, menunggu Ketua RW. @break
                        @case(S::Approved) Disetujui Ketua RW. Surat sedang disiapkan. @break
                        @case(S::Issued) Surat Anda sudah terbit. @break
                        @case(S::Completed) Surat sudah Anda unduh. @break
                        @case(S::RevisionRequired) Ada yang perlu Anda perbaiki. @break
                        @default Pengajuan ditolak.
                    @endswitch
                </p>
                @if (in_array($r->status, [S::RevisionRequired, S::RejectedByRt, S::RejectedByRw]) && $r->last_note)
                    <blockquote @class(['mt-3 border-l-[3px] pl-3 text-[0.9375rem]', 'border-warning' => $r->status === S::RevisionRequired, 'border-danger' => $r->status !== S::RevisionRequired])>
                        {{ $r->last_note }}
                    </blockquote>
                @endif
                @if ($r->status->isRejected())
                    <p class="mt-3 text-sm text-ink-muted">Anda dapat membuat pengajuan baru setelah memenuhi catatan di atas.</p>
                @endif
            </div>

            <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                @if ($r->status->hasLetter())
                    <x-button :href="route('letters.download', $r)" icon="download">Unduh surat (PDF)</x-button>
                @endif
                @if ($r->status === S::RevisionRequired)
                    <x-button :href="route('warga.requests.edit', $r)" icon="pencil">Perbaiki pengajuan</x-button>
                @endif
                @if ($r->status === S::Draft)
                    @if ($canSubmit)
                        <form method="POST" action="{{ route('warga.requests.submit', $r) }}">
                            @csrf
                            <x-button icon="arrow-right" class="w-full">Kirim ke Ketua RT</x-button>
                        </form>
                    @endif
                    <x-button :href="route('warga.requests.edit', $r)" variant="secondary" icon="pencil">Ubah draf</x-button>
                @endif
            </div>
        </div>
        @unless ($r->status->isRejected())
            <div class="border-t border-line bg-canvas px-5 py-4 sm:px-6">
                <x-progress :request="$r" />
            </div>
        @endunless
    </section>

    <div class="grid gap-8 lg:grid-cols-[1.4fr_1fr]">
        <div class="space-y-8">
            @if ($r->issuedLetter)
                <section class="panel">
                    <div class="panel-header"><h2 class="panel-title">Surat yang diterbitkan</h2></div>
                    <div class="px-5 sm:px-6">
                        <x-summary-list :rows="[
                            ['Nomor surat', $r->issuedLetter->letter_number],
                            ['Tanggal terbit', $r->issuedLetter->issued_at->translatedFormat('j F Y, H:i')],
                            ['Kode verifikasi', $r->issuedLetter->verification_code],
                        ]" />
                    </div>
                </section>
            @endif

            @include('partials.request-details', ['letterRequest' => $r])

            @if ($r->status === S::Draft)
                <form method="POST" action="{{ route('warga.requests.destroy', $r) }}" x-data
                      @submit="if (!confirm('Hapus draf ini? Dokumen yang sudah diunggah juga akan dihapus.')) $event.preventDefault()">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost btn-sm text-danger"><x-svg-icon name="trash-2" class="size-4" />Hapus draf</button>
                </form>
            @endif
        </div>

        <section aria-labelledby="riwayat">
            <h2 id="riwayat" class="mb-3 text-lg font-semibold">Riwayat</h2>
            <div class="panel px-5 py-5">
                <x-timeline :histories="$r->histories" />
            </div>
        </section>
    </div>
</x-layouts.app>
