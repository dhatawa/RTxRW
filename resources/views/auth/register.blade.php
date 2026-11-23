<x-layouts.auth title="Daftar sebagai warga">
    <h1 class="text-2xl font-bold sm:text-[1.875rem]">Daftar sebagai warga</h1>
    <p class="mt-2 text-ink-muted">Setelah mendaftar, Ketua RT akan memeriksa data Anda. Pengajuan surat bisa dilakukan setelah data terverifikasi.</p>

    @if ($errors->any())
        <x-alert tone="danger" title="Ada yang perlu diperbaiki" class="mt-6">
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-6" novalidate>
        @csrf

        <fieldset class="space-y-5">
            <legend class="mb-4 text-lg font-semibold">Identitas</legend>
            <x-field name="name" label="Nama lengkap" hint="Sesuai KTP." required autocomplete="name" />
            <x-field name="nik" label="NIK" hint="16 digit angka di KTP atau Kartu Keluarga." required inputmode="numeric" maxlength="16" autocomplete="off" width="md" />
            <x-field name="rt_id" label="RT tempat tinggal" type="select" required width="sm"
                     :options="$rts->mapWithKeys(fn ($rt) => [$rt->id => 'RT '.$rt->number.($rt->area_description ? ' — '.$rt->area_description : '')])->all()"
                     placeholder="Pilih RT" />
        </fieldset>

        <fieldset class="space-y-5 border-t border-line pt-6">
            <legend class="mb-4 text-lg font-semibold">Akun</legend>
            <x-field name="phone" label="Nomor HP" hint="Dipakai Ketua RT untuk menghubungi Anda bila ada kekurangan data." type="tel" required inputmode="tel" autocomplete="tel" width="md" />
            <x-field name="email" label="Email" type="email" required autocomplete="email" inputmode="email" />
            <x-field name="password" label="Kata sandi" hint="Minimal 8 karakter, berisi huruf dan angka." type="password" required autocomplete="new-password" />
            <x-field name="password_confirmation" label="Ulangi kata sandi" type="password" required autocomplete="new-password" />
        </fieldset>

        <div @class(['field-group-error' => $errors->has('consent')])>
            @error('consent')<p class="field-error"><x-svg-icon name="circle-alert" class="mt-0.5 size-4" />{{ $message }}</p>@enderror
            <label class="flex gap-3 text-[0.9375rem]">
                <input type="checkbox" name="consent" value="1" class="check mt-0.5" @checked(old('consent'))>
                <span>Saya menyatakan data yang saya isi benar, dan setuju data ini digunakan oleh pengurus RT/RW untuk keperluan layanan surat.</span>
            </label>
        </div>

        <x-button class="w-full sm:w-auto">Buat akun</x-button>
    </form>

    <p class="mt-10 border-t border-line pt-6 text-[0.9375rem] text-ink-muted">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold">Masuk</a></p>
</x-layouts.auth>
