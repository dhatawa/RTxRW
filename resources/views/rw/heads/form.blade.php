@php $current = $rt->ketua; @endphp
<x-layouts.app :title="'Ketua RT '.$rt->number">
    <x-page-header :eyebrow="'RT '.$rt->number" :title="$current ? 'Kelola akun Ketua RT' : 'Tetapkan Ketua RT'" :back="route('rw.heads.index')" back-label="Ketua RT" />

    <form method="POST" action="{{ route('rw.heads.update', $rt) }}" class="max-w-2xl space-y-6" novalidate
          x-data="{ mode: '{{ old('mode', $current ? 'update' : 'replace') }}' }">
        @csrf @method('PUT')

        @if ($current)
            <fieldset class="panel px-5 py-5 sm:px-6">
                <legend class="sr-only">Jenis perubahan</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="flex cursor-pointer gap-3 rounded-md border border-line px-3 py-3 has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                        <input type="radio" name="mode" value="update" x-model="mode" class="mt-0.5 size-5 accent-primary">
                        <span><span class="block font-semibold">Perbarui data</span><span class="block text-sm text-ink-muted">Ubah kontak atau atur ulang kata sandi {{ $current->name }}.</span></span>
                    </label>
                    <label class="flex cursor-pointer gap-3 rounded-md border border-line px-3 py-3 has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                        <input type="radio" name="mode" value="replace" x-model="mode" class="mt-0.5 size-5 accent-primary">
                        <span><span class="block font-semibold">Ganti Ketua RT</span><span class="block text-sm text-ink-muted">Buat akun baru. Akun lama dinonaktifkan, riwayatnya tetap tersimpan.</span></span>
                    </label>
                </div>
            </fieldset>
        @else
            <input type="hidden" name="mode" value="replace">
        @endif

        <div class="panel">
            <div class="space-y-5 px-5 py-6 sm:px-6">
                <x-field name="name" label="Nama lengkap" :value="$current?->name" required />
                <x-field name="email" label="Email (untuk masuk)" type="email" :value="$current?->email" required />
                <x-field name="phone" label="Nomor HP" type="tel" :value="$current?->phone" optional width="md" />
                <div class="border-t border-line pt-5">
                    <p class="mb-4 text-sm text-ink-muted" x-show="mode === 'update'">Kosongkan kata sandi bila tidak ingin mengubahnya.</p>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-field name="password" label="Kata sandi" type="password" autocomplete="new-password" hint="Min. 8 karakter, huruf dan angka." />
                        <x-field name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" />
                    </div>
                </div>
            </div>
            <div class="flex gap-2 border-t border-line bg-canvas px-5 py-4 sm:px-6">
                <x-button><span x-text="mode === 'replace' ? 'Simpan Ketua RT baru' : 'Simpan perubahan'">Simpan</span></x-button>
                <x-button :href="route('rw.heads.index')" variant="secondary">Batal</x-button>
            </div>
        </div>
    </form>
</x-layouts.app>
