<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Enums\WorkflowAction;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Services\LetterIssuer;
use App\Services\LetterWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Data demo fiktif. Semua nama, NIK, dan alamat dibuat-buat — bukan data warga sungguhan.
 * Akun demo: kata sandi "password123".
 */
class DatabaseSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    public function run(): void
    {
        Storage::disk('local')->deleteDirectory('documents');
        Storage::disk('local')->deleteDirectory('letters');

        $rwHead = User::create([
            'name' => 'H. Ahmad Suryana', 'email' => 'rw@rtxrw.test', 'phone' => '081221000005',
            'password' => self::PASSWORD, 'role' => Role::KetuaRw,
        ]);

        $rw = Rw::create([
            'number' => '05', 'kelurahan' => 'Mekarsari', 'kecamatan' => 'Sukajadi', 'city' => 'Kota Bandung',
            'province' => 'Jawa Barat', 'postal_code' => '40162',
            'secretariat_address' => 'Jl. Cempaka Raya No. 12 (Balai Warga RW 05)',
            'ketua_user_id' => $rwHead->id,
        ]);

        $rtData = [
            ['01', 'Jl. Cempaka I – III', 'Bambang Hermawan'],
            ['02', 'Jl. Cempaka IV – VI', 'Sri Wahyuningsih'],
            ['03', 'Gg. Melati dan Gg. Kenanga', 'Dedi Kurniawan'],
            ['04', 'Perumahan Griya Mekar Blok A–C', 'Yuliana Siregar'],
        ];

        $rts = collect($rtData)->map(function ($row) use ($rw) {
            [$number, $area, $head] = $row;
            $user = User::create([
                'name' => $head, 'email' => "rt{$number}@rtxrw.test", 'phone' => '0812210001'.$number,
                'password' => self::PASSWORD, 'role' => Role::KetuaRt,
            ]);

            return Rt::create(['rw_id' => $rw->id, 'number' => $number, 'area_description' => $area, 'ketua_user_id' => $user->id]);
        })->keyBy('number');

        $this->call(LetterTypeSeeder::class);

        // Warga fiktif: [nama, L/P, RT, tempat lahir, tgl lahir, agama, status kawin, pekerjaan, alamat, verifikasi]
        $people = [
            ['Rina Marlina', 'P', '01', 'Bandung', '1991-03-14', 'Islam', 'Kawin', 'Karyawan Swasta', 'Jl. Cempaka I', 'verified', 'warga@rtxrw.test'],
            ['Agus Setiawan', 'L', '01', 'Garut', '1985-07-02', 'Islam', 'Kawin', 'Wiraswasta', 'Jl. Cempaka II', 'verified'],
            ['Maria Fransiska', 'P', '01', 'Medan', '1979-12-25', 'Katolik', 'Kawin', 'Guru', 'Jl. Cempaka III', 'verified'],
            ['Fajar Nugraha', 'L', '01', 'Bandung', '2001-05-19', 'Islam', 'Belum Kawin', 'Mahasiswa', 'Jl. Cempaka I', 'pending'],
            ['Siti Nurhaliza', 'P', '02', 'Cimahi', '1988-09-09', 'Islam', 'Kawin', 'Pedagang', 'Jl. Cempaka IV', 'verified'],
            ['Hendra Gunawan', 'L', '02', 'Tasikmalaya', '1975-01-30', 'Islam', 'Kawin', 'Buruh Harian Lepas', 'Jl. Cempaka V', 'verified'],
            ['Dewi Lestari', 'P', '02', 'Bandung', '1996-11-11', 'Kristen', 'Belum Kawin', 'Perawat', 'Jl. Cempaka VI', 'pending'],
            ['Yusuf Maulana', 'L', '03', 'Sumedang', '1983-04-21', 'Islam', 'Kawin', 'Sopir', 'Gg. Melati', 'verified'],
            ['Wayan Sudarma', 'L', '03', 'Denpasar', '1990-08-17', 'Hindu', 'Kawin', 'Karyawan Swasta', 'Gg. Kenanga', 'verified'],
            ['Lina Kartika', 'P', '03', 'Bandung', '1999-02-03', 'Islam', 'Belum Kawin', 'Penjahit', 'Gg. Melati', 'rejected'],
            ['Budi Santoso', 'L', '04', 'Semarang', '1970-06-06', 'Islam', 'Kawin', 'Pensiunan', 'Perum. Griya Mekar Blok A', 'verified'],
            ['Andreas Wijaya', 'L', '04', 'Bandung', '1987-10-10', 'Buddha', 'Kawin', 'Wiraswasta', 'Perum. Griya Mekar Blok C', 'verified'],
        ];

        $residents = [];
        foreach ($people as $i => $p) {
            $slug = strtolower(strtok($p[0], ' '));
            $user = User::create([
                'name' => $p[0], 'email' => $p[10] ?? "{$slug}@rtxrw.test", 'phone' => '0813'.str_pad((string) (40000000 + $i * 731), 8, '0', STR_PAD_LEFT),
                'password' => self::PASSWORD, 'role' => Role::Warga,
            ]);
            $rt = $rts[$p[2]];

            $resident = Resident::create([
                'user_id' => $user->id, 'rt_id' => $rt->id,
                // NIK fiktif: kode wilayah 3273 (Kota Bandung) + tanggal lahir + urutan.
                'nik' => '327304'.Carbon::parse($p[4])->format('dmy').str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'kk_number' => '327304'.str_pad((string) (200000 + $i * 37), 10, '0', STR_PAD_LEFT),
                'full_name' => $p[0], 'gender' => $p[1], 'birth_place' => $p[3], 'birth_date' => $p[4],
                'religion' => $p[5], 'marital_status' => $p[6], 'occupation' => $p[7],
                'address' => $p[8], 'house_number' => (string) (3 + $i * 2), 'nationality' => 'WNI',
            ]);

            $status = VerificationStatus::from($p[9]);
            if ($status !== VerificationStatus::Pending) {
                $resident->forceFill([
                    'verification_status' => $status,
                    'verified_at' => now()->subDays(40 - $i),
                    'verified_by' => $rt->ketua_user_id,
                    'verification_note' => $status === VerificationStatus::Rejected ? 'Nomor KK yang diisi tidak sesuai dengan foto KK. Mohon periksa kembali.' : null,
                ])->save();
            }

            $residents[$p[0]] = $resident;
        }

        $this->seedRequests($residents, $rwHead);
    }

    /** Pengajuan demo di berbagai tahap, dijalankan lewat LetterWorkflow agar riwayat dan log konsisten. */
    private function seedRequests(array $residents, User $rwHead): void
    {
        $workflow = app(LetterWorkflow::class);
        $issuer = app(LetterIssuer::class);

        $make = function (string $who, string $code, string $purpose, array $fields, int $daysAgo) use ($residents) {
            $resident = $residents[$who];
            $type = LetterType::where('code', $code)->firstOrFail();
            Carbon::setTestNow();
            Carbon::setTestNow(now()->subDays($daysAgo)->setTime(8 + $daysAgo % 5, 15));

            $request = LetterRequest::create([
                'resident_id' => $resident->id, 'rt_id' => $resident->rt_id, 'letter_type_id' => $type->id,
                'purpose' => $purpose, 'form_data' => $fields,
            ]);

            foreach ($type->requirements as $req) {
                $path = 'documents/'.$request->public_id.'/'.bin2hex(random_bytes(12)).'.pdf';
                Storage::disk('local')->put($path, $this->samplePdf($req->name));
                $request->documents()->create([
                    'letter_requirement_id' => $req->id, 'original_name' => str($req->name)->slug().'.pdf',
                    'path' => $path, 'mime_type' => 'application/pdf', 'size' => 900, 'uploaded_by' => $resident->user_id,
                ]);
            }

            return $request;
        };

        $as = function (User $user, callable $fn, int $hoursLater = 0) {
            Carbon::setTestNow(now()->addHours($hoursLater));
            Auth::setUser($user);
            $fn();
        };

        $rtHead = fn (LetterRequest $r) => $r->rt->ketua;
        $owner = fn (LetterRequest $r) => $r->resident->user;

        // 1. Selesai: diterbitkan & diunduh
        $r = $make('Agus Setiawan', 'SKU', 'Pengajuan Kredit Usaha Rakyat (KUR) di bank', [
            'nama_usaha' => 'Warung Sembako Berkah', 'jenis_usaha' => 'Warung sembako', 'alamat_usaha' => 'Jl. Cempaka II No. 5', 'mulai_usaha' => '2019-02-01',
        ], 21);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtApprove, $rtHead($r)), 5);
        $as($rwHead, fn () => $workflow->apply($r, WorkflowAction::RwApprove, $rwHead), 20);
        $as($rwHead, fn () => $issuer->issue($r, $rwHead), 1);
        $as($owner($r), function () use ($r, $workflow, $owner) {
            $r->issuedLetter->forceFill(['first_downloaded_at' => now()])->save();
            $workflow->apply($r, WorkflowAction::Complete, $owner($r));
        }, 3);

        // 2. Surat terbit, belum diunduh (akun demo warga)
        $r = $make('Rina Marlina', 'SPD', 'Persyaratan pembukaan rekening tabungan', [], 9);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtApprove, $rtHead($r)), 3);
        $as($rwHead, fn () => $workflow->apply($r, WorkflowAction::RwApprove, $rwHead), 18);
        $as($rwHead, fn () => $issuer->issue($r, $rwHead), 1);

        // 3. Perlu perbaikan (akun demo warga) — diminta RT
        $r = $make('Rina Marlina', 'SKD', 'Persyaratan pendaftaran sekolah anak', ['lama_tinggal_di_alamat_ini' => '2 tahun'], 4);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtRevision, $rtHead($r),
            'Lama tinggal yang tertulis 2 tahun, sedangkan di data RT Ibu tercatat pindah ke sini sejak 2019. Mohon disesuaikan.'), 6);

        // 4. Draf (akun demo warga)
        $make('Rina Marlina', 'SKCK', 'Melamar pekerjaan', [], 1);

        // 5. Menunggu RT
        $r = $make('Maria Fransiska', 'SKCK', 'Persyaratan melamar pekerjaan sebagai guru PNS', [], 2);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));

        $r = $make('Siti Nurhaliza', 'SKTM', 'Keringanan biaya sekolah anak di SMP Negeri', [
            'penghasilan_keluarga_per_bulan' => '2100000', 'jumlah_tanggungan' => '4',
        ], 1);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));

        // 6. Menunggu RW
        $r = $make('Yusuf Maulana', 'SPP', 'Pindah domisili karena pekerjaan', [
            'alamat_tujuan' => 'Jl. Merdeka No. 20, RT 02/RW 07, Kel. Citarum, Kota Bandung', 'alasan_pindah' => 'Pekerjaan', 'jumlah_anggota_keluarga_yang_pindah' => '3',
        ], 3);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtApprove, $rtHead($r)), 8);

        $r = $make('Budi Santoso', 'SKD', 'Pengurusan dana pensiun', ['lama_tinggal_di_alamat_ini' => '22 tahun'], 2);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtApprove, $rtHead($r)), 4);

        // 7. Disetujui RW, siap terbit
        $r = $make('Wayan Sudarma', 'SPU', 'Pengurusan Kartu Keluarga baru setelah menikah', ['perihal' => 'Pengantar pembuatan Kartu Keluarga baru'], 5);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtApprove, $rtHead($r)), 6);
        $as($rwHead, fn () => $workflow->apply($r, WorkflowAction::RwApprove, $rwHead), 24);

        // 8. Ditolak RT
        $r = $make('Hendra Gunawan', 'SKU', 'Bantuan modal usaha', [
            'nama_usaha' => 'Bengkel Las Hendra', 'jenis_usaha' => 'Bengkel las', 'alamat_usaha' => 'Jl. Raya Sukajadi No. 101', 'mulai_usaha' => '2024-01-15',
        ], 12);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtReject, $rtHead($r),
            'Lokasi usaha berada di luar wilayah RW 05. Surat keterangan usaha perlu diajukan ke RT/RW tempat usaha berada.'), 10);

        // 9. Ditolak RW
        $r = $make('Andreas Wijaya', 'SKTM', 'Pengajuan beasiswa kuliah anak', [
            'penghasilan_keluarga_per_bulan' => '12000000', 'jumlah_tanggungan' => '3',
        ], 15);
        $as($owner($r), fn () => $workflow->apply($r, WorkflowAction::Submit, $owner($r)));
        $as($rtHead($r), fn () => $workflow->apply($r, WorkflowAction::RtApprove, $rtHead($r)), 12);
        $as($rwHead, fn () => $workflow->apply($r, WorkflowAction::RwReject, $rwHead,
            'Penghasilan yang dilaporkan berada jauh di atas kriteria keluarga tidak mampu yang berlaku di kelurahan.'), 30);

        Carbon::setTestNow();
        Auth::forgetUser();
    }

    /** PDF satu halaman sebagai contoh dokumen persyaratan. */
    private function samplePdf(string $label): string
    {
        $text = 'Contoh dokumen: '.preg_replace('/[^A-Za-z0-9 \/.-]/', '', $label);
        $stream = "BT /F1 14 Tf 72 760 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
