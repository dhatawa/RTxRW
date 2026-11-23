<x-layouts.public title="Pelayanan Warga, Tanpa Batas Jarak" description="Ajukan surat pengantar dan surat keterangan RT/RW secara daring, pantau prosesnya, dan unduh suratnya dari rumah.">
    {{-- Pembuka --}}
    <section class="border-b border-line">
        <div class="mx-auto grid max-w-6xl gap-12 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[1.25fr_1fr] lg:gap-16">
            <div>
                <p class="eyebrow">RW {{ $rw?->number }} · Kelurahan {{ $rw?->kelurahan }}</p>
                <h1 class="mt-4 text-[2.25rem] font-bold leading-[1.1] sm:text-5xl">
                    Surat pengantar RT/RW,<br class="hidden sm:block"> tanpa harus mengetuk pintu.
                </h1>
                <p class="mt-6 max-w-xl text-lg text-ink-muted">
                    Isi formulir dan unggah dokumen dari ponsel. Ketua RT memeriksa, Ketua RW menyetujui,
                    dan surat bisa diunduh begitu terbit. Setiap tahap tercatat dan bisa Anda pantau.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <x-button :href="route('dashboard')" icon-right="arrow-right">Lanjut ke dasbor</x-button>
                    @else
                        <x-button :href="route('register')" icon-right="arrow-right">Daftar sebagai warga</x-button>
                        <x-button :href="route('login')" variant="secondary">Saya sudah punya akun</x-button>
                    @endauth
                </div>
                <p class="mt-6 text-sm text-ink-subtle">Pendaftaran baru diverifikasi oleh Ketua RT, biasanya dalam 1–2 hari.</p>
            </div>

            {{-- Contoh lembar status: memperlihatkan apa yang akan dilihat warga --}}
            <figure class="self-center">
                <div class="panel overflow-hidden">
                    <div class="flex items-center justify-between border-b border-line bg-canvas px-5 py-3">
                        <span class="font-mono text-xs text-ink-muted">PGJ-251118-4KQ2M</span>
                        <span class="rounded-sm border border-info-line bg-info-soft px-2 py-0.5 text-xs font-semibold text-info">Menunggu Ketua RW</span>
                    </div>
                    <div class="px-5 py-5">
                        <p class="font-semibold">Surat Keterangan Domisili</p>
                        <ol class="mt-5 space-y-4 text-[0.9375rem]">
                            <li class="flex gap-3">
                                <x-svg-icon name="circle-check" class="mt-0.5 size-5 text-primary" />
                                <span><span class="font-medium">Pengajuan dikirim</span><span class="block text-sm text-ink-subtle">Senin, 09.12</span></span>
                            </li>
                            <li class="flex gap-3">
                                <x-svg-icon name="circle-check" class="mt-0.5 size-5 text-primary" />
                                <span><span class="font-medium">Disetujui Ketua RT 03</span><span class="block text-sm text-ink-subtle">Senin, 19.40</span></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex size-5 items-center justify-center"><span class="size-3 rounded-full border-2 border-secondary"></span></span>
                                <span><span class="font-medium">Persetujuan Ketua RW</span><span class="block text-sm text-ink-subtle">Sedang diperiksa</span></span>
                            </li>
                            <li class="flex gap-3 text-ink-subtle">
                                <span class="mt-0.5 flex size-5 items-center justify-center"><span class="size-3 rounded-full border-2 border-line-strong"></span></span>
                                <span>Surat terbit dan siap diunduh</span>
                            </li>
                        </ol>
                    </div>
                </div>
                <figcaption class="mt-3 text-sm text-ink-subtle">Contoh tampilan status pengajuan di akun warga.</figcaption>
            </figure>
        </div>
    </section>

    {{-- Cara kerja --}}
    <section class="border-b border-line bg-canvas">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 md:py-16">
            <h2 class="text-2xl font-bold sm:text-3xl">Bagaimana prosesnya</h2>
            <ol class="mt-10 grid gap-10 md:grid-cols-4 md:gap-8">
                @foreach ([
                    ['Daftar dan lengkapi data', 'Buat akun dengan NIK dan pilih RT tempat tinggal. Ketua RT memverifikasi bahwa Anda memang warganya.'],
                    ['Ajukan surat', 'Pilih jenis surat, tulis keperluannya, dan unggah dokumen yang diminta. Foto dari ponsel sudah cukup.'],
                    ['Diperiksa RT, disetujui RW', 'Ketua RT memeriksa data, lalu Ketua RW memberi persetujuan akhir. Bila ada kekurangan, Anda diminta memperbaiki.'],
                    ['Unduh surat', 'Surat PDF bernomor resmi terbit di akun Anda, lengkap dengan kode QR untuk pengecekan keaslian.'],
                ] as $i => [$title, $text])
                    <li class="border-t-2 border-primary pt-5">
                        <span class="text-sm font-semibold tabular-nums text-secondary">Langkah {{ $i + 1 }}</span>
                        <h3 class="mt-1 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-[0.9375rem] text-ink-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Jenis surat (dari database) --}}
    <section class="border-b border-line">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 md:py-16">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-2xl font-bold sm:text-3xl">Surat yang bisa diajukan</h2>
                    <p class="mt-2 max-w-xl text-ink-muted">Setiap jenis surat punya persyaratan sendiri. Buka untuk melihat dokumen yang perlu disiapkan.</p>
                </div>
                <a href="{{ route('letter-types') }}" class="inline-flex items-center gap-1 font-semibold">Lihat semua persyaratan <x-svg-icon name="arrow-right" class="size-4" /></a>
            </div>

            <ul class="mt-8 grid border-t border-line sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($letterTypes as $type)
                    <li class="border-b border-line sm:odd:border-r lg:border-r lg:[&:nth-child(3n)]:border-r-0">
                        <a href="{{ route('letter-types.show', $type) }}" class="group flex h-full items-start justify-between gap-4 px-1 py-5 text-ink no-underline sm:px-5">
                            <span>
                                <span class="block font-semibold group-hover:underline">{{ $type->name }}</span>
                                <span class="mt-1 block text-sm text-ink-subtle">Estimasi {{ $type->processing_days }} hari kerja · {{ $type->signatory->label() }}</span>
                            </span>
                            <x-svg-icon name="chevron-right" class="mt-0.5 size-5 text-ink-subtle group-hover:text-primary" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Fakta layanan + verifikasi --}}
    <section>
        <div class="mx-auto grid max-w-6xl gap-12 px-4 py-14 sm:px-6 md:py-16 lg:grid-cols-2">
            <div>
                <h2 class="text-2xl font-bold">Terbuka dan tercatat</h2>
                <p class="mt-3 text-ink-muted">Setiap keputusan Ketua RT dan Ketua RW dicatat beserta waktu dan alasannya. Warga bisa melihat siapa yang sedang memproses suratnya.</p>
                <dl class="mt-8 grid grid-cols-2 gap-6 border-t border-line pt-6">
                    <div>
                        <dt class="text-sm text-ink-muted">Surat terbit bulan ini</dt>
                        <dd class="mt-1 text-3xl font-bold tabular-nums">{{ $stats['issued_month'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-ink-muted">Rata-rata waktu proses</dt>
                        <dd class="mt-1 text-3xl font-bold tabular-nums">
                            @if ($stats['avg_hours'] === null) — @elseif ($stats['avg_hours'] < 48) {{ $stats['avg_hours'] }} <span class="text-lg font-semibold">jam</span> @else {{ round($stats['avg_hours'] / 24, 1) }} <span class="text-lg font-semibold">hari</span> @endif
                        </dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-ink-subtle">Dihitung dari data pengajuan 90 hari terakhir.</p>
            </div>

            <div class="rounded-lg bg-primary p-6 text-white sm:p-8">
                <x-svg-icon name="qr-code" class="size-7 text-white/70" />
                <h2 class="mt-4 text-xl font-bold text-white">Menerima surat dari warga RW {{ $rw?->number }}?</h2>
                <p class="mt-2 text-white/80">Periksa keasliannya dengan kode verifikasi yang tercetak di bagian bawah surat.</p>
                <form action="{{ route('verify.index') }}" method="GET" class="mt-6 flex flex-col gap-2 sm:flex-row">
                    <label for="kode-beranda" class="sr-only">Kode verifikasi</label>
                    <input id="kode-beranda" name="kode" required placeholder="Contoh: ABCD-EFGH-JKMN" autocomplete="off"
                           class="input border-white/30 font-mono uppercase sm:flex-1">
                    <button type="submit" class="btn border-white bg-white text-primary hover:bg-canvas">Periksa</button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
