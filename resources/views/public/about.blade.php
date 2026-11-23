<x-layouts.public title="Tentang RTxRW">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 md:py-16">
        <p class="eyebrow">Tentang</p>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Pelayanan warga, tanpa batas jarak</h1>

        <div class="prose-civic mt-8">
            <p class="text-lg">RTxRW adalah layanan administrasi daring untuk warga RW {{ $rw?->number }}. Tujuannya sederhana: warga tidak perlu lagi datang berkali-kali ke rumah Ketua RT dan Ketua RW hanya untuk meminta tanda tangan surat pengantar.</p>

            <h2>Mengapa dibuat</h2>
            <p>Mengurus surat pengantar sering terkendala waktu: pengurus bekerja di siang hari, warga pun demikian. Dengan RTxRW, pengajuan dan pemeriksaan bisa dilakukan kapan saja, sementara keputusan tetap berada di tangan Ketua RT dan Ketua RW.</p>

            <h2>Prinsip yang kami pegang</h2>
            <ul>
                <li><strong>Berjenjang.</strong> Setiap surat diperiksa Ketua RT lalu disetujui Ketua RW. Tidak ada jalan pintas.</li>
                <li><strong>Tercatat.</strong> Setiap keputusan disimpan beserta waktu, pelaku, dan alasannya.</li>
                <li><strong>Menjaga data.</strong> NIK dan nomor KK disimpan terenkripsi. Dokumen hanya bisa dibuka pihak yang berwenang.</li>
                <li><strong>Mudah dipakai.</strong> Dirancang untuk ponsel dan untuk warga dari berbagai usia.</li>
            </ul>
        </div>

        @if ($rw)
            <h2 class="mt-12 text-xl font-semibold">Pengurus</h2>
            <table class="table-civic mt-4">
                <thead><tr><th>Jabatan</th><th>Nama</th><th class="hidden sm:table-cell">Wilayah</th></tr></thead>
                <tbody>
                    <tr><td>Ketua RW {{ $rw->number }}</td><td class="font-medium">{{ $rw->ketua?->name ?? '—' }}</td><td class="hidden text-ink-muted sm:table-cell">Seluruh RW</td></tr>
                    @foreach ($rw->rts as $rt)
                        <tr><td>Ketua RT {{ $rt->number }}</td><td class="font-medium">{{ $rt->ketua?->name ?? '—' }}</td><td class="hidden text-ink-muted sm:table-cell">{{ $rt->area_description }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.public>
