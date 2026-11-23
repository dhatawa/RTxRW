@php $editing = $rt->exists; @endphp
<x-layouts.app :title="$editing ? 'Ubah RT '.$rt->number : 'Tambah RT'">
    <x-page-header :title="$editing ? 'Ubah RT '.$rt->number : 'Tambah RT'" :back="route('rw.rts.index')" back-label="Data RT" />

    <form method="POST" action="{{ $editing ? route('rw.rts.update', $rt) : route('rw.rts.store') }}" class="panel max-w-2xl" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="space-y-5 px-5 py-6 sm:px-6">
            <x-field name="number" label="Nomor RT" :value="$rt->number" required inputmode="numeric" maxlength="3" width="xs" hint="Contoh: 05" />
            <x-field name="area_description" label="Cakupan wilayah" :value="$rt->area_description" optional hint="Nama jalan, gang, atau blok. Ditampilkan saat warga memilih RT." />
            <input type="hidden" name="is_active" value="0">
            <label class="flex gap-3">
                <input type="checkbox" name="is_active" value="1" class="check mt-0.5" @checked(old('is_active', $rt->is_active))>
                <span><span class="font-semibold">RT aktif</span><span class="block text-sm text-ink-muted">RT nonaktif tidak bisa dipilih oleh pendaftar baru. Data dan pengajuan lama tetap tersimpan.</span></span>
            </label>
        </div>
        <div class="flex gap-2 border-t border-line bg-canvas px-5 py-4 sm:px-6">
            <x-button>{{ $editing ? 'Simpan perubahan' : 'Tambah RT' }}</x-button>
            <x-button :href="route('rw.rts.index')" variant="secondary">Batal</x-button>
        </div>
    </form>
</x-layouts.app>
