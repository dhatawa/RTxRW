# RTxRW

**Pelayanan Warga, Tanpa Batas Jarak.**

RTxRW adalah layanan administrasi surat untuk satu wilayah RW. Warga mengajukan surat pengantar dan surat keterangan secara daring. Ketua RT memverifikasi, Ketua RW memberi persetujuan akhir, lalu surat PDF bernomor resmi dan ber-QR terbit di akun warga.

Dibangun dengan Laravel 13, Blade, Tailwind CSS v4, Alpine.js, DomPDF, dan MySQL.

---

## Fitur

| Peran | Yang bisa dilakukan |
|---|---|
| **Warga** | Daftar dengan NIK dan RT; melengkapi data kependudukan; mengajukan surat (formulir dinamis per jenis dan unggah dokumen); menyimpan draf; memantau tahapan; memperbaiki dan mengirim ulang; mengunduh PDF; menerima notifikasi |
| **Ketua RT** | Memverifikasi pendaftar di RT-nya; memeriksa pengajuan dan dokumen; menyetujui (diteruskan ke RW), meminta perbaikan, atau menolak dengan alasan; melihat riwayat verifikasi dan statistik RT |
| **Ketua RW** | Persetujuan akhir untuk semua RT; menerbitkan surat (penomoran otomatis dan PDF); mengelola jenis surat, persyaratan, isian tambahan, dan template berversi; mengelola RT dan akun Ketua RT; laporan dengan ekspor CSV; audit log; identitas RW untuk kop surat |
| **Publik** | Beranda, informasi layanan, daftar persyaratan surat, dan pengecekan keaslian surat lewat kode atau QR |

---

## Kebutuhan

- PHP 8.3+ dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `intl`, `zip`
- Composer 2
- Node.js 20+ dan npm
- MySQL 8 / MariaDB 10.6+

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3308
DB_DATABASE=rtxrw
DB_USERNAME=root
DB_PASSWORD=
```

Buat database, jalankan migrasi dan data demo, lalu build aset:

```bash
mysql -u root -P 3308 -h 127.0.0.1 -e "CREATE DATABASE rtxrw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka http://localhost:8000.

> **Penting:** `APP_KEY` dipakai untuk mengenkripsi NIK/KK dan membuat hash NIK. Jangan mengganti `APP_KEY` setelah ada data warga, karena data yang sudah terenkripsi tidak akan bisa dibaca lagi.

## Akun demo

Semua akun memakai kata sandi `password123`.

| Peran | Email | Catatan |
|---|---|---|
| Ketua RW 05 | `rw@rtxrw.test` | Ada 2 pengajuan menunggu persetujuan dan 1 siap terbit |
| Ketua RT 01 | `rt01@rtxrw.test` | Ada pengajuan menunggu dan 1 warga menunggu verifikasi |
| Ketua RT 02–04 | `rt02@rtxrw.test` … `rt04@rtxrw.test` | |
| Warga RT 01 | `warga@rtxrw.test` | Punya surat terbit, pengajuan yang perlu diperbaiki, dan draf |

Semua nama, NIK, dan alamat di data demo adalah fiktif.

## Menjalankan test

```bash
php artisan test
```

Test memakai SQLite in-memory, jadi tidak menyentuh database pengembangan. Test mencakup:

- registrasi, enkripsi NIK, dan penolakan NIK ganda
- alur lengkap: pengajuan → RT → RW → PDF terbit → unduh → selesai, termasuk riwayat dan audit
- revisi dari RT maupun RW lalu kirim ulang
- isolasi RT: Ketua RT tidak bisa melihat, memutus, atau membuka dokumen RT lain
- warga tidak bisa membuka halaman admin, data warga lain, atau melewati RT/RW
- transisi status tidak sah, keputusan ganda, dan draf yang tidak terlihat oleh petugas
- validasi unggahan (tipe dan ukuran), akun nonaktif
- template tidak bisa menyisipkan HTML, versi template, dan penggantian Ketua RT
- setiap halaman tampil untuk setiap peran

---

## Arsitektur singkat

```
app/
├─ Enums/           Role, RequestStatus, WorkflowAction (peta transisi), Signatory, VerificationStatus
├─ Services/
│  ├─ LetterWorkflow    satu-satunya pintu perubahan status (lock + cek role/wilayah/status + riwayat + audit + notifikasi)
│  ├─ LetterIssuer      penomoran, render PDF, penyimpanan privat, kode verifikasi
│  ├─ LetterRenderer    mengisi template teks dengan placeholder (escape penuh)
│  ├─ DocumentStorage   unggahan di disk privat dengan nama acak
│  └─ ActivityLogger    audit log append-only
├─ Policies/        LetterRequestPolicy, ResidentPolicy (pencegahan IDOR)
├─ Http/Middleware/ EnsureRole, EnsureAccountActive, SecurityHeaders
└─ Http/Controllers/{Public,Auth,Shared,Warga,Rt,Rw}
```

### Alur status

```
draft ──kirim──▶ pending_rt ──RT setuju──▶ pending_rw ──RW setuju──▶ approved ──terbitkan──▶ issued ──warga unduh──▶ completed
                   │  └─RT tolak─▶ rejected_rt      │  └─RW tolak─▶ rejected_rw
                   └─RT minta perbaikan─┐           └─RW minta perbaikan─┐
                                        ▼                                ▼
                               revision_required ──warga kirim ulang──▶ pending_rt
```

Peta transisi ada di [app/Enums/WorkflowAction.php](app/Enums/WorkflowAction.php). Kolom `status` tidak fillable, sehingga status tidak bisa diubah lewat form atau request langsung.

### Keamanan

- Otorisasi berlapis: middleware role → policy per model → pemeriksaan ulang di `LetterWorkflow`.
- Ketua RT dibatasi ke `rts.ketua_user_id`, Ketua RW ke `rws.ketua_user_id`.
- Pengajuan memakai ULID di URL, bukan ID berurutan.
- NIK dan No. KK dienkripsi (`encrypted` cast). Pencarian dan cek duplikat memakai HMAC (`nik_hash`).
- Dokumen dan PDF disimpan di `storage/app/private` dan hanya dilayani lewat controller ber-policy dengan `Cache-Control: no-store`.
- Unggahan dibatasi ke JPG, PNG, dan PDF maksimal 2 MB, dicek dari ekstensi dan MIME isi file.
- Transisi memakai transaksi DB dan `SELECT … FOR UPDATE`. Nomor surat unik per tahun, dijaga juga oleh unique index.
- `approval_histories` dan `activity_logs` menolak update/delete di level model.
- Rate limit untuk login, registrasi, dan verifikasi publik. Ditambah security headers.
- Template surat berupa teks biasa: semua isi di-escape sebelum dirender, jadi admin tidak bisa menyisipkan HTML/skrip.

Keputusan bisnis yang diambil selama pengembangan dicatat di [docs/ASSUMPTIONS.md](docs/ASSUMPTIONS.md).
