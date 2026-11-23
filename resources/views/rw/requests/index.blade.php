<x-layouts.app title="Pengajuan">
    <x-page-header title="Pengajuan surat" description="Seluruh pengajuan dari semua RT. Anda memberi persetujuan akhir setelah Ketua RT memverifikasi." />

    <x-tabs :current="$tab" :keep="['q', 'rt', 'jenis']" :items="[
        'perlu-tindakan' => ['label' => 'Menunggu persetujuan', 'count' => $counts['perlu-tindakan']],
        'siap-terbit' => ['label' => 'Siap terbit', 'count' => $counts['siap-terbit']],
        'di-rt' => 'Masih di RT',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'semua' => 'Semua',
    ]" />

    <form method="GET" class="mb-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-[1fr_9rem_14rem_auto]" role="search">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label class="sr-only" for="q">Cari</label>
        <input id="q" name="q" value="{{ $search }}" class="input sm:col-span-2 lg:col-span-1" placeholder="Cari nama pemohon atau nomor pengajuan">
        <label class="sr-only" for="rt">RT</label>
        <select id="rt" name="rt" class="input">
            <option value="">Semua RT</option>
            @foreach ($rts as $rt)<option value="{{ $rt->id }}" @selected(request('rt') == $rt->id)>RT {{ $rt->number }}</option>@endforeach
        </select>
        <label class="sr-only" for="jenis">Jenis surat</label>
        <select id="jenis" name="jenis" class="input">
            <option value="">Semua jenis surat</option>
            @foreach ($letterTypes as $t)<option value="{{ $t->id }}" @selected(request('jenis') == $t->id)>{{ $t->name }}</option>@endforeach
        </select>
        <x-button variant="secondary" icon="search">Terapkan</x-button>
    </form>

    @include('partials.request-table', ['requests' => $requests, 'routeName' => 'rw.requests.show', 'showRt' => true])
</x-layouts.app>
