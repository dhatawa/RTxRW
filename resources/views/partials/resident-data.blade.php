{{-- Data lengkap warga untuk Ketua RT / RW (NIK tampil utuh karena dibutuhkan untuk verifikasi). --}}
<x-summary-list :rows="[
    ['Nama lengkap', $resident->full_name],
    ['NIK', $resident->nik],
    ['Nomor KK', $resident->kk_number],
    ['Tempat, tanggal lahir', trim(($resident->birth_place ?? '').', '.($resident->birth_date?->translatedFormat('j F Y') ?? ''), ', ')],
    ['Jenis kelamin', $resident->genderLabel()],
    ['Agama', $resident->religion],
    ['Status perkawinan', $resident->marital_status],
    ['Pekerjaan', $resident->occupation],
    ['Kewarganegaraan', $resident->nationality],
    ['Alamat', $resident->fullAddress()],
    ['Kontak', trim(($resident->user?->phone ?? '').' · '.($resident->user?->email ?? ''), ' ·')],
]" />
