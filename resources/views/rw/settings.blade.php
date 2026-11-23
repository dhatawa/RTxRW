<x-layouts.app title="Identitas RW">
    <x-page-header title="Identitas RW" description="Dipakai pada kop surat dan halaman publik. Perubahan hanya berlaku untuk surat yang terbit setelahnya." />

    <form method="POST" action="{{ route('rw.settings.update') }}" class="panel max-w-2xl" novalidate>
        @csrf @method('PUT')
        <div class="space-y-5 px-5 py-6 sm:px-6">
            <x-field name="number" label="Nomor RW" :value="$rw->number" required maxlength="3" width="xs" inputmode="numeric" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field name="kelurahan" label="Kelurahan / Desa" :value="$rw->kelurahan" required />
                <x-field name="kecamatan" label="Kecamatan" :value="$rw->kecamatan" required />
                <x-field name="city" label="Kota / Kabupaten" :value="$rw->city" required hint="Contoh: Kota Bandung" />
                <x-field name="province" label="Provinsi" :value="$rw->province" required />
            </div>
            <x-field name="postal_code" label="Kode pos" :value="$rw->postal_code" optional inputmode="numeric" maxlength="5" width="xs" />
            <x-field name="secretariat_address" label="Alamat sekretariat" :value="$rw->secretariat_address" optional />
        </div>
        <div class="border-t border-line bg-canvas px-5 py-4 sm:px-6"><x-button>Simpan</x-button></div>
    </form>
</x-layouts.app>
