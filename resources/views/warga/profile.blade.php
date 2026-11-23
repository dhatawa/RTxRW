<x-layouts.app title="Data kependudukan">
    @php
        $locked = $resident->isVerified();
        $V = \App\Enums\VerificationStatus::class;
    @endphp
    <x-page-header title="Data kependudukan" description="Data ini dipakai untuk mengisi surat Anda secara otomatis. Pastikan sesuai KTP dan Kartu Keluarga." />

    <div class="grid gap-8 lg:grid-cols-[1fr_18rem]">
        <form method="POST" action="{{ route('warga.profile.update') }}" class="panel" novalidate>
            @csrf @method('PUT')

            <fieldset class="space-y-5 px-5 py-6 sm:px-6">
                <legend class="text-lg font-semibold">Identitas</legend>
                @if ($locked)
                    <p class="text-sm text-ink-muted">Nama, NIK, dan RT sudah diverifikasi Ketua RT dan tidak bisa diubah sendiri. Hubungi Ketua RT bila ada kesalahan.</p>
                    <x-summary-list class="border-y border-line" :rows="[
                        ['Nama lengkap', $resident->full_name],
                        ['NIK', $resident->maskedNik()],
                        ['RT / RW', 'RT '.$resident->rt->number.' / RW '.$resident->rt->rw->number],
                    ]" />
                @else
                    <x-field name="full_name" label="Nama lengkap" :value="$resident->full_name" required hint="Sesuai KTP." />
                    <x-field name="nik" label="NIK" :value="$resident->nik" required inputmode="numeric" maxlength="16" width="md" />
                    <x-field name="rt_id" label="RT" type="select" :value="$resident->rt_id" required width="sm"
                             :options="$rts->mapWithKeys(fn ($rt) => [$rt->id => 'RT '.$rt->number.($rt->area_description ? ' — '.$rt->area_description : '')])->all()" />
                @endif
                <x-field name="kk_number" label="Nomor Kartu Keluarga" :value="$resident->kk_number" optional inputmode="numeric" maxlength="16" width="md" />
            </fieldset>

            <fieldset class="space-y-5 border-t border-line px-5 py-6 sm:px-6">
                <legend class="text-lg font-semibold">Data diri</legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="birth_place" label="Tempat lahir" :value="$resident->birth_place" required />
                    <x-field name="birth_date" label="Tanggal lahir" type="date" :value="$resident->birth_date?->format('Y-m-d')" required />
                </div>

                <fieldset @class(['field-group-error' => $errors->has('gender')])>
                    <legend class="field-label">Jenis kelamin</legend>
                    @error('gender')<p class="field-error"><x-svg-icon name="circle-alert" class="mt-0.5 size-4" />{{ $message }}</p>@enderror
                    <div class="mt-1 flex gap-6">
                        @foreach (['L' => 'Laki-laki', 'P' => 'Perempuan'] as $val => $label)
                            <label class="flex items-center gap-2.5"><input type="radio" name="gender" value="{{ $val }}" class="size-5 accent-primary" @checked(old('gender', $resident->gender) === $val)> {{ $label }}</label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="religion" label="Agama" type="select" :value="$resident->religion" required :options="array_combine(\App\Models\Resident::RELIGIONS, \App\Models\Resident::RELIGIONS)" />
                    <x-field name="marital_status" label="Status perkawinan" type="select" :value="$resident->marital_status" required :options="array_combine(\App\Models\Resident::MARITAL_STATUSES, \App\Models\Resident::MARITAL_STATUSES)" />
                    <x-field name="occupation" label="Pekerjaan" :value="$resident->occupation" required hint="Sesuai KTP/KK, misalnya Karyawan Swasta." />
                    <x-field name="nationality" label="Kewarganegaraan" type="select" :value="$resident->nationality" required :options="['WNI' => 'WNI', 'WNA' => 'WNA']" />
                </div>
            </fieldset>

            <fieldset class="space-y-5 border-t border-line px-5 py-6 sm:px-6">
                <legend class="text-lg font-semibold">Alamat</legend>
                <div class="grid gap-5 sm:grid-cols-[1fr_8rem]">
                    <x-field name="address" label="Nama jalan / gang / blok" :value="$resident->address" required />
                    <x-field name="house_number" label="Nomor rumah" :value="$resident->house_number" optional />
                </div>
            </fieldset>

            <div class="flex flex-col gap-3 border-t border-line bg-canvas px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                <x-button>Simpan data</x-button>
                @if ($resident->verification_status === $V::Rejected)
                    <p class="text-sm text-ink-muted">Setelah disimpan, data akan diperiksa ulang oleh Ketua RT.</p>
                @endif
            </div>
        </form>

        <aside class="space-y-4">
            <div class="panel px-5 py-4">
                <p class="text-sm font-semibold">Status verifikasi</p>
                <x-verification-badge :status="$resident->verification_status" class="mt-2" />
                @if ($resident->verification_status === $V::Verified)
                    <p class="mt-2 text-sm text-ink-muted">Oleh {{ $resident->verifier?->name }}, {{ $resident->verified_at?->translatedFormat('j F Y') }}.</p>
                @elseif ($resident->verification_status === $V::Rejected)
                    <p class="mt-2 border-l-[3px] border-danger pl-3 text-sm">{{ $resident->verification_note }}</p>
                @else
                    <p class="mt-2 text-sm text-ink-muted">
                        @if ($resident->isProfileComplete())
                            Ketua RT {{ $resident->rt->number }} akan memeriksa data Anda.
                        @else
                            Lengkapi semua isian wajib agar Ketua RT dapat memverifikasi.
                        @endif
                    </p>
                @endif
            </div>
            <div class="px-1 text-sm text-ink-muted">
                <p class="flex gap-2"><x-svg-icon name="lock" class="mt-0.5 size-4" />NIK dan nomor KK disimpan terenkripsi dan hanya ditampilkan kepada Ketua RT dan Ketua RW.</p>
            </div>
        </aside>
    </div>
</x-layouts.app>
