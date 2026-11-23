<x-layouts.public title="Informasi pelayanan" description="Alur, waktu layanan, dan ketentuan pengajuan surat RT/RW secara daring.">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 md:py-16">
        <p class="eyebrow">Informasi pelayanan</p>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Alur dan ketentuan layanan</h1>
        <p class="mt-4 text-lg text-ink-muted">Hal-hal yang perlu Anda ketahui sebelum mengajukan surat melalui RTxRW.</p>

        <div class="prose-civic mt-10">
            <h2>Siapa yang bisa mengajukan</h2>
            <p>Warga yang berdomisili di RW {{ $rw?->number }} Kelurahan {{ $rw?->kelurahan }} dan sudah terdaftar di salah satu RT. Akun baru perlu diverifikasi oleh Ketua RT sebelum dapat mengajukan surat. Verifikasi ini memastikan surat hanya diterbitkan untuk warga yang benar tinggal di wilayah ini.</p>

            <h2>Tahapan pengajuan</h2>
            <ol>
                <li><strong>Draf</strong> — pengajuan tersimpan dan masih bisa diubah. Belum dikirim ke siapa pun.</li>
                <li><strong>Menunggu Ketua RT</strong> — Ketua RT memeriksa data dan dokumen Anda.</li>
                <li><strong>Menunggu Ketua RW</strong> — sudah disetujui RT dan menunggu persetujuan akhir.</li>
                <li><strong>Surat terbit</strong> — PDF bernomor resmi tersedia di akun Anda.</li>
            </ol>
            <p>Bila Ketua RT atau Ketua RW menemukan kekurangan, status berubah menjadi <strong>Perlu perbaikan</strong> disertai catatan. Setelah Anda memperbaiki dan mengirim ulang, pengajuan diperiksa kembali mulai dari Ketua RT.</p>

            <h2>Waktu layanan</h2>
            <p>Pengajuan bisa dikirim kapan saja. Pemeriksaan dilakukan pengurus di sela kegiatan sehari-hari, umumnya 1–3 hari kerja tergantung jenis surat. Untuk keperluan mendesak, seperti surat keterangan kematian, hubungi Ketua RT secara langsung setelah mengirim pengajuan.</p>

            <h2>Dokumen</h2>
            <ul>
                <li>Format JPG, PNG, atau PDF, maksimal 2 MB per file.</li>
                <li>Pastikan foto terang dan seluruh tulisan terbaca.</li>
                <li>Dokumen hanya dapat dilihat oleh Anda, Ketua RT Anda, dan Ketua RW.</li>
            </ul>

            <h2>Keaslian surat</h2>
            <p>Setiap surat yang terbit memiliki nomor dan kode verifikasi unik. Instansi penerima dapat memeriksanya di halaman <a href="{{ route('verify.index') }}">cek keaslian surat</a>.</p>

            <h2>Biaya</h2>
            <p>Layanan surat RT/RW melalui RTxRW tidak dipungut biaya.</p>
        </div>

        <div class="mt-10 flex flex-wrap gap-3 border-t border-line pt-8">
            <x-button :href="route('letter-types')" variant="secondary">Lihat jenis surat</x-button>
            @guest<x-button :href="route('register')">Daftar sebagai warga</x-button>@endguest
        </div>
    </div>
</x-layouts.public>
