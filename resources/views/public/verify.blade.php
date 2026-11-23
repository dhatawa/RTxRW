<x-layouts.public title="Cek keaslian surat">
    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 md:py-16">
        <p class="eyebrow">Verifikasi</p>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Cek keaslian surat</h1>
        <p class="mt-4 text-ink-muted">Masukkan kode verifikasi yang tercetak di bagian bawah surat, atau pindai kode QR-nya.</p>

        <form action="{{ route('verify.index') }}" method="GET" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="kode" class="field-label">Kode verifikasi</label>
                <input id="kode" name="kode" value="{{ $code }}" required autocomplete="off" placeholder="ABCD-EFGH-JKMN" class="input font-mono uppercase">
            </div>
            <x-button>Periksa</x-button>
        </form>

        @if ($code !== null)
            <div class="mt-10" role="status">
                @if ($letter)
                    @php $s = $letter->snapshot; @endphp
                    <div class="border-l-4 border-primary bg-primary-soft px-5 py-4">
                        <p class="flex items-center gap-2 text-lg font-bold text-primary"><x-svg-icon name="shield-check" class="size-6" />Surat ini asli</p>
                        <p class="mt-1 text-[0.9375rem] text-ink-muted">Diterbitkan melalui RTxRW setelah disetujui Ketua RT dan Ketua RW.</p>
                    </div>
                    @php
                        $name = $s['name'] ?? '';
                        $masked = collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1).str_repeat('•', max(mb_strlen($w) - 1, 1)))->implode(' ');
                    @endphp
                    <x-summary-list class="mt-6 border-y border-line" :rows="[
                        ['Nomor surat', $letter->letter_number],
                        ['Jenis surat', $s['letter_type'] ?? null],
                        ['Atas nama', $masked],
                        ['Wilayah', 'RT '.($s['rt'] ?? '-').' / RW '.($s['rw'] ?? '-')],
                        ['Tanggal terbit', $letter->issued_at->translatedFormat('j F Y')],
                    ]" />
                    <p class="mt-4 text-sm text-ink-subtle">Nama ditampilkan sebagian untuk melindungi data pribadi. Cocokkan huruf awalnya dengan surat yang Anda terima.</p>
                @else
                    <div class="border-l-4 border-danger bg-danger-soft px-5 py-4">
                        <p class="flex items-center gap-2 text-lg font-bold text-danger"><x-svg-icon name="circle-x" class="size-6" />Kode tidak ditemukan</p>
                        <p class="mt-1 text-[0.9375rem] text-ink-muted">Periksa kembali penulisan kode. Jika tetap tidak ditemukan, surat tersebut tidak diterbitkan melalui RTxRW. Hubungi sekretariat RW untuk konfirmasi.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.public>
