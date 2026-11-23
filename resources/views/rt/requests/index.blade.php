<x-layouts.app title="Pengajuan masuk">
    <x-page-header eyebrow="RT {{ auth()->user()->managedRt->number }}" title="Pengajuan surat" description="Periksa data dan dokumen warga, lalu teruskan ke Ketua RW atau kembalikan dengan catatan." />

    <x-tabs :current="$tab" :keep="['q', 'jenis']" :items="[
        'perlu-tindakan' => ['label' => 'Perlu diperiksa', 'count' => $pendingCount],
        'di-rw' => 'Di Ketua RW',
        'perbaikan' => 'Menunggu perbaikan warga',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'semua' => 'Semua',
    ]" />

    <form method="GET" class="mb-5 grid gap-2 sm:grid-cols-[1fr_14rem_auto]" role="search">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label class="sr-only" for="q">Cari</label>
        <input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari nama pemohon atau nomor pengajuan">
        <label class="sr-only" for="jenis">Jenis surat</label>
        <select id="jenis" name="jenis" class="input">
            <option value="">Semua jenis surat</option>
            @foreach ($letterTypes as $t)<option value="{{ $t->id }}" @selected(request('jenis') == $t->id)>{{ $t->name }}</option>@endforeach
        </select>
        <x-button variant="secondary" icon="search">Terapkan</x-button>
    </form>

    @include('partials.request-table', ['requests' => $requests, 'routeName' => 'rt.requests.show', 'showRt' => false])
</x-layouts.app>
