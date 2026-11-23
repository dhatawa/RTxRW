# Asumsi dan keputusan

Keputusan bisnis yang belum ditentukan di brief, beserta alasannya. Semuanya bisa diubah.

## Alur dan status

1. **Semua jenis surat melewati RT lalu RW.** Brief meminta agar warga tidak bisa melewati RT maupun RW. Perbedaan kewenangan antar jenis surat diwujudkan lewat **penanda tangan di PDF** (`signatory`: RT saja, RW saja, atau RT dengan mengetahui RW), bukan lewat jalur persetujuan yang berbeda.
2. **"Submitted" bukan status yang menetap.** Saat warga mengirim, peristiwa `submitted` dicatat di riwayat dan status langsung menjadi `pending_rt`. Enum `Submitted` tetap disediakan untuk kompatibilitas.
3. **Revisi dari RW kembali ke warga, lalu diverifikasi ulang oleh RT.** Data yang sudah diubah warga harus diperiksa lagi oleh pihak yang paling mengenal warga tersebut.
4. **`approved` → `issued` dipisah.** Ketua RW menyetujui dulu, lalu menerbitkan. Ini memberi kesempatan memeriksa template sebelum nomor surat dipakai. Keduanya bisa dilakukan berurutan dari halaman yang sama.
5. **`completed` terjadi saat warga pertama kali mengunduh PDF.** Unduhan oleh RT/RW tidak mengubah status.
6. **Penolakan bersifat final.** Warga membuat pengajuan baru bila ingin mencoba lagi. Catatan penolakan tetap terlihat.
7. **Draf tidak terlihat oleh RT/RW** dan boleh disimpan tanpa dokumen lengkap. Kelengkapan dicek saat dikirim.

## Warga dan akun

8. **Pendaftar harus diverifikasi Ketua RT** sebelum bisa mengajukan surat (disetujui pemilik project). Data profil wajib dilengkapi sebelum diverifikasi.
9. **Nama, NIK, dan RT terkunci setelah terverifikasi.** Perubahan dilakukan lewat Ketua RT. Ini mencegah akun yang sudah dipercaya diubah diam-diam.
10. **Tidak ada fitur lupa kata sandi lewat email** di tahap awal, karena banyak warga tidak aktif memakai email. Warga menghubungi Ketua RT. Ketua RW dapat mengatur ulang kata sandi Ketua RT.
11. **Mengganti Ketua RT membuat akun baru dan menonaktifkan akun lama**, bukan menghapusnya, agar riwayat keputusan tetap menunjuk ke orang yang benar.
12. **Satu RW per instalasi.** Tabel `rws` disiapkan untuk banyak RW, tetapi aplikasi saat ini memakai `Rw::current()`.

## Surat

13. **Format nomor surat:** `{urut 3 digit}/{kode jenis}/RT{rt}/RW{rw}/{bulan romawi}/{tahun}`. Nomor urut berlaku per tahun untuk seluruh RW.
14. **Tanda tangan elektronik sederhana.** PDF memuat nama penanda tangan dan kode QR verifikasi, bukan tanda tangan basah atau sertifikat digital. Instansi penerima memeriksa keaslian di `/verifikasi`.
15. **Halaman verifikasi publik hanya menampilkan data minimum:** nomor, jenis, nama yang disamarkan, wilayah, dan tanggal. NIK tidak pernah ditampilkan.
16. **Template berversi.** Menyimpan template selalu membuat versi baru. Surat yang terbit menyimpan referensi versi yang dipakai dan snapshot data penting.
17. **Isian tambahan per jenis surat** (misalnya nama usaha untuk SKU) dikonfigurasi Ketua RW dan otomatis tersedia sebagai placeholder di template. Kunci placeholder tidak berubah walaupun labelnya diganti.

## Notifikasi

18. **Notifikasi di dalam aplikasi** (channel `database`), ditampilkan sebagai lonceng dan halaman notifikasi. Email/WhatsApp bisa ditambahkan nanti sebagai channel baru di kelas notifikasi yang sama.
