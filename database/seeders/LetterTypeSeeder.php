<?php

namespace Database\Seeders;

use App\Enums\Signatory;
use App\Models\LetterType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Jenis surat awal. Persyaratan, isian tambahan, penandatangan, dan template dibuat berbeda
 * per jenis karena kebutuhan administrasinya memang berbeda.
 */
class LetterTypeSeeder extends Seeder
{
    private const OPENING_RT = "Yang bertanda tangan di bawah ini, Ketua RT {{rt}} RW {{rw}} Kelurahan {{kelurahan}}, Kecamatan {{kecamatan}}, {{kota}}, dengan ini menerangkan bahwa:";

    private const OPENING_RW = "Yang bertanda tangan di bawah ini, Ketua RW {{rw}} Kelurahan {{kelurahan}}, Kecamatan {{kecamatan}}, {{kota}}, berdasarkan pengantar Ketua RT {{rt}}, dengan ini menerangkan bahwa:";

    private const CLOSING = 'Demikian surat ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.';

    public function run(): void
    {
        $ktp = ['Foto KTP pemohon', 'Foto jelas, seluruh sisi KTP terlihat.', true];
        $kk = ['Foto Kartu Keluarga', 'Halaman yang memuat nama pemohon.', true];

        $types = [
            [
                'code' => 'SPD', 'name' => 'Surat Pengantar Domisili', 'signatory' => Signatory::RtRw, 'days' => 2,
                'description' => 'Pengantar dari RT/RW untuk mengurus surat keterangan domisili di kelurahan, atau keperluan lain yang meminta bukti tempat tinggal.',
                'requirements' => [$ktp, $kk],
                'fields' => [],
                'template' => ['Surat Pengantar Domisili', self::OPENING_RT,
                    "{{tabel_identitas}}\n\nadalah benar warga kami yang bertempat tinggal di alamat tersebut di atas. Surat pengantar ini diberikan untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SKD', 'name' => 'Surat Keterangan Domisili', 'signatory' => Signatory::Rw, 'days' => 3,
                'description' => 'Keterangan bahwa pemohon benar berdomisili di wilayah RW, termasuk lama tinggal. Biasa diminta bank, sekolah, atau tempat kerja.',
                'requirements' => [$ktp, $kk, ['Bukti tinggal', 'Misalnya rekening listrik/PDAM atau surat perjanjian sewa.', false]],
                'fields' => [
                    ['Lama tinggal di alamat ini', 'text', true, 'Contoh: 3 tahun 4 bulan.'],
                ],
                'template' => ['Surat Keterangan Domisili', self::OPENING_RW,
                    "{{tabel_identitas}}\n\nadalah benar berdomisili di alamat tersebut di atas selama {{lama_tinggal_di_alamat_ini}}. Surat keterangan ini diberikan untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SKCK', 'name' => 'Surat Pengantar SKCK', 'signatory' => Signatory::RtRw, 'days' => 2,
                'description' => 'Pengantar untuk mengurus Surat Keterangan Catatan Kepolisian (SKCK) di Polsek/Polres.',
                'requirements' => [$ktp, $kk, ['Pas foto 4x6 latar merah', 'Format JPG/PNG.', true]],
                'fields' => [],
                'template' => ['Surat Pengantar', self::OPENING_RT,
                    "{{tabel_identitas}}\n\nadalah benar warga kami dan sepengetahuan kami berkelakuan baik serta tidak pernah terlibat perkara pidana di lingkungan RT {{rt}} RW {{rw}}. Surat pengantar ini diberikan untuk pengurusan Surat Keterangan Catatan Kepolisian (SKCK) dengan keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SKU', 'name' => 'Surat Keterangan Usaha', 'signatory' => Signatory::Rw, 'days' => 3,
                'description' => 'Keterangan bahwa pemohon menjalankan usaha di wilayah RW. Umumnya untuk pengajuan KUR, izin usaha mikro, atau bantuan UMKM.',
                'requirements' => [$ktp, $kk, ['Foto tempat usaha', 'Tampak depan tempat usaha.', true]],
                'fields' => [
                    ['Nama usaha', 'text', true, null],
                    ['Jenis usaha', 'text', true, 'Contoh: warung sembako, jasa jahit.'],
                    ['Alamat usaha', 'textarea', true, 'Isi bila berbeda dari alamat rumah.'],
                    ['Mulai usaha', 'date', true, null],
                ],
                'template' => ['Surat Keterangan Usaha', self::OPENING_RW,
                    "{{tabel_identitas}}\n\nadalah benar memiliki dan menjalankan usaha dengan keterangan sebagai berikut:\nNama usaha: {{nama_usaha}}\nJenis usaha: {{jenis_usaha}}\nAlamat usaha: {{alamat_usaha}}\nMulai beroperasi: {{mulai_usaha}}\n\nSurat keterangan ini diberikan untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SKTM', 'name' => 'Surat Keterangan Tidak Mampu', 'signatory' => Signatory::RtRw, 'days' => 3,
                'description' => 'Keterangan kondisi ekonomi keluarga untuk keringanan biaya sekolah, kesehatan, atau bantuan sosial. Ketua RT dapat melakukan pengecekan lapangan.',
                'requirements' => [$ktp, $kk, ['Foto kondisi rumah', 'Tampak depan dan bagian dalam.', false]],
                'fields' => [
                    ['Penghasilan keluarga per bulan', 'number', true, 'Perkiraan dalam rupiah, tanpa titik.'],
                    ['Jumlah tanggungan', 'number', true, 'Jumlah anggota keluarga yang ditanggung.'],
                ],
                'template' => ['Surat Keterangan Tidak Mampu', self::OPENING_RT,
                    "{{tabel_identitas}}\n\nadalah benar warga kami yang tergolong keluarga kurang mampu, dengan penghasilan keluarga sekitar Rp{{penghasilan_keluarga_per_bulan}} per bulan dan menanggung {{jumlah_tanggungan}} orang anggota keluarga. Surat keterangan ini diberikan untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SKL', 'name' => 'Surat Keterangan Kelahiran', 'signatory' => Signatory::RtRw, 'days' => 2,
                'description' => 'Pengantar pencatatan kelahiran untuk pengurusan akta kelahiran di Disdukcapil. Diajukan oleh orang tua bayi.',
                'requirements' => [$kk, ['Surat keterangan lahir dari bidan/rumah sakit', null, true], ['Foto KTP ayah dan ibu', 'Boleh digabung dalam satu file PDF.', true]],
                'fields' => [
                    ['Nama bayi', 'text', true, null],
                    ['Jenis kelamin bayi', 'text', true, 'Laki-laki / Perempuan.'],
                    ['Tempat lahir bayi', 'text', true, null],
                    ['Tanggal lahir bayi', 'date', true, null],
                    ['Nama ayah', 'text', true, null],
                    ['Nama ibu', 'text', true, null],
                ],
                'template' => ['Surat Keterangan Kelahiran', self::OPENING_RT,
                    "telah lahir seorang anak:\nNama: {{nama_bayi}}\nJenis kelamin: {{jenis_kelamin_bayi}}\nTempat, tanggal lahir: {{tempat_lahir_bayi}}, {{tanggal_lahir_bayi}}\nNama ayah: {{nama_ayah}}\nNama ibu: {{nama_ibu}}\n\nyang dilaporkan oleh:\n\n{{tabel_identitas}}\n\nSurat keterangan ini diberikan untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SKM', 'name' => 'Surat Keterangan Kematian', 'signatory' => Signatory::RtRw, 'days' => 1,
                'description' => 'Pengantar pelaporan kematian warga untuk pengurusan akta kematian. Diajukan oleh anggota keluarga.',
                'requirements' => [['Foto KTP almarhum/almarhumah', null, true], $kk, ['Surat keterangan dari rumah sakit/dokter', 'Wajib bila meninggal di fasilitas kesehatan.', false]],
                'fields' => [
                    ['Nama almarhum', 'text', true, null],
                    ['NIK almarhum', 'text', true, null],
                    ['Tanggal meninggal', 'date', true, null],
                    ['Tempat meninggal', 'text', true, null],
                    ['Sebab meninggal', 'text', false, null],
                    ['Hubungan pelapor', 'text', true, 'Contoh: anak kandung.'],
                ],
                'template' => ['Surat Keterangan Kematian', self::OPENING_RT,
                    "telah meninggal dunia:\nNama: {{nama_almarhum}}\nNIK: {{nik_almarhum}}\nTanggal meninggal: {{tanggal_meninggal}}\nTempat meninggal: {{tempat_meninggal}}\nSebab: {{sebab_meninggal}}\n\nKematian tersebut dilaporkan oleh {{hubungan_pelapor}} almarhum dengan identitas:\n\n{{tabel_identitas}}",
                    self::CLOSING],
            ],
            [
                'code' => 'SPP', 'name' => 'Surat Pengantar Pindah', 'signatory' => Signatory::RtRw, 'days' => 3,
                'description' => 'Pengantar untuk mengurus surat pindah domisili ke luar wilayah RW.',
                'requirements' => [$ktp, $kk],
                'fields' => [
                    ['Alamat tujuan', 'textarea', true, 'Alamat lengkap tempat tinggal baru.'],
                    ['Alasan pindah', 'text', true, 'Contoh: pekerjaan, ikut keluarga.'],
                    ['Jumlah anggota keluarga yang pindah', 'number', true, null],
                ],
                'template' => ['Surat Pengantar Pindah', self::OPENING_RT,
                    "{{tabel_identitas}}\n\nadalah benar warga kami yang bermaksud pindah ke alamat {{alamat_tujuan}} dengan alasan {{alasan_pindah}}, sebanyak {{jumlah_anggota_keluarga_yang_pindah}} orang. Surat pengantar ini diberikan untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
            [
                'code' => 'SPU', 'name' => 'Surat Pengantar Umum', 'signatory' => Signatory::Rt, 'days' => 2,
                'description' => 'Pengantar untuk keperluan lain yang tidak tercantum di daftar, misalnya pengurusan KTP, KK, atau nikah.',
                'requirements' => [$ktp, ['Dokumen pendukung', 'Bila ada.', false]],
                'fields' => [
                    ['Perihal', 'text', true, 'Contoh: Pengantar pembuatan KK baru.'],
                ],
                'template' => ['Surat Pengantar', self::OPENING_RT,
                    "{{tabel_identitas}}\n\nadalah benar warga kami. Surat pengantar ini diberikan perihal {{perihal}}, untuk keperluan: {{keperluan}}.",
                    self::CLOSING],
            ],
        ];

        foreach ($types as $i => $t) {
            $type = LetterType::create([
                'code' => $t['code'],
                'name' => $t['name'],
                'slug' => Str::slug($t['name']),
                'description' => $t['description'],
                'signatory' => $t['signatory'],
                'processing_days' => $t['days'],
                'is_active' => true,
                'sort_order' => $i,
                'fields' => collect($t['fields'])->map(fn ($f) => [
                    'key' => \App\Http\Controllers\Rw\LetterTypeController::keyFor($f[0]),
                    'label' => $f[0],
                    'type' => $f[1],
                    'required' => $f[2],
                    'help' => $f[3],
                ])->all(),
            ]);

            foreach ($t['requirements'] as $j => [$name, $desc, $required]) {
                $type->requirements()->create(['name' => $name, 'description' => $desc, 'is_required' => $required, 'sort_order' => $j]);
            }

            [$title, $opening, $body, $closing] = $t['template'];
            $type->templates()->create([
                'version' => 1, 'title' => $title, 'opening' => $opening, 'body' => $body, 'closing' => $closing, 'is_active' => true,
            ]);
        }
    }
}
