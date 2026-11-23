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
use Database\Seeders\Support\SamplePdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Data demo berskala: 100 warga fiktif dan 50 pengajuan dengan riwayat lengkap.
 *
 * Semua pengajuan dijalankan lewat LetterWorkflow/LetterIssuer agar riwayat, audit log,
 * notifikasi, dan PDF konsisten dengan penggunaan nyata. Data bersifat deterministik
 * (seed acak tetap) sehingga hasilnya sama setiap kali dijalankan.
 *
 * Jalankan terpisah: php artisan db:seed --class=BulkDemoSeeder
 * Semua akun memakai kata sandi "password123".
 */
class BulkDemoSeeder extends Seeder
{
    private const RESIDENTS = 100;

    private const REQUESTS = 50;

    private const FIRST_MALE = ['Ahmad', 'Budi', 'Cahya', 'Dimas', 'Eko', 'Fikri', 'Gilang', 'Hadi', 'Irfan', 'Joko', 'Kurnia', 'Lukman', 'Mulyadi', 'Nanda', 'Oki', 'Pandu', 'Rizal', 'Sigit', 'Taufik', 'Umar', 'Wahyu', 'Yoga', 'Zaenal', 'Arif', 'Bayu', 'Danu', 'Fajar', 'Hendri', 'Iwan', 'Rangga'];

    private const FIRST_FEMALE = ['Ayu', 'Bunga', 'Citra', 'Desi', 'Eka', 'Fitri', 'Gita', 'Hana', 'Indah', 'Julia', 'Kartika', 'Lestari', 'Mega', 'Nur', 'Okta', 'Putri', 'Ratna', 'Sari', 'Tuti', 'Utari', 'Vina', 'Winda', 'Yanti', 'Zahra', 'Anisa', 'Dewi', 'Endah', 'Melati', 'Rani', 'Septi'];

    private const LAST = ['Pratama', 'Saputra', 'Wijaya', 'Hidayat', 'Nugroho', 'Kusuma', 'Santoso', 'Permana', 'Gunawan', 'Firmansyah', 'Ramadhan', 'Setiawan', 'Hakim', 'Maulana', 'Siregar', 'Nasution', 'Lubis', 'Simanjuntak', 'Purba', 'Tanjung', 'Rahayu', 'Anggraini', 'Puspita', 'Handayani', 'Wulandari', 'Kurniasih', 'Suryani', 'Hartono', 'Sudrajat', 'Sukmana'];

    private const PLACES = ['Bandung', 'Bandung', 'Bandung', 'Cimahi', 'Garut', 'Sumedang', 'Tasikmalaya', 'Bogor', 'Cirebon', 'Jakarta', 'Semarang', 'Surabaya', 'Yogyakarta', 'Medan', 'Padang', 'Palembang', 'Makassar'];

    private const JOBS = ['Karyawan Swasta', 'Wiraswasta', 'Pedagang', 'Guru', 'Pegawai Negeri Sipil', 'Buruh Harian Lepas', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Perawat', 'Sopir', 'Pensiunan', 'Petani', 'Montir', 'Penjahit', 'Belum/Tidak Bekerja'];

    private const STREETS = [
        '01' => ['Jl. Cempaka I', 'Jl. Cempaka II', 'Jl. Cempaka III'],
        '02' => ['Jl. Cempaka IV', 'Jl. Cempaka V', 'Jl. Cempaka VI'],
        '03' => ['Gg. Melati', 'Gg. Kenanga', 'Gg. Melati Dalam'],
        '04' => ['Perum. Griya Mekar Blok A', 'Perum. Griya Mekar Blok B', 'Perum. Griya Mekar Blok C'],
    ];

    /** Tujuan realistis per kode jenis surat. */
    private const PURPOSES = [
        'SPD' => ['Persyaratan pembukaan rekening bank', 'Pengurusan kartu BPJS Kesehatan', 'Persyaratan pendaftaran kerja'],
        'SKD' => ['Persyaratan pendaftaran sekolah anak', 'Pengajuan kredit kendaraan', 'Persyaratan administrasi kantor'],
        'SKCK' => ['Melamar pekerjaan', 'Persyaratan pendaftaran CPNS', 'Pengurusan visa kerja'],
        'SKU' => ['Pengajuan Kredit Usaha Rakyat (KUR)', 'Pendaftaran bantuan UMKM', 'Pengurusan NIB usaha mikro'],
        'SKTM' => ['Keringanan biaya sekolah', 'Pengajuan bantuan biaya rumah sakit', 'Pendaftaran beasiswa kuliah'],
        'SKL' => ['Pengurusan akta kelahiran'],
        'SKM' => ['Pengurusan akta kematian', 'Klaim asuransi jiwa'],
        'SPP' => ['Pindah domisili karena pekerjaan', 'Pindah domisili ikut keluarga'],
        'SPU' => ['Pengurusan Kartu Keluarga baru', 'Pengantar pembuatan KTP elektronik', 'Persyaratan pengurusan nikah di KUA'],
    ];

    private const REVISION_NOTES = [
        'Foto KTP kurang jelas, mohon unggah ulang dengan pencahayaan yang lebih terang.',
        'Halaman Kartu Keluarga yang diunggah tidak memuat nama pemohon.',
        'Keperluan surat perlu ditulis lebih spesifik, misalnya nama instansi tujuan.',
        'Alamat usaha berbeda dengan yang kami ketahui. Mohon dicek kembali.',
        'Nomor KK pada formulir tidak sesuai dengan dokumen yang diunggah.',
    ];

    private const REJECT_RT_NOTES = [
        'Pemohon belum tercatat berdomisili di RT ini selama minimal 6 bulan.',
        'Surat sejenis sudah diterbitkan untuk keperluan yang sama bulan ini.',
        'Usaha yang dimaksud berada di luar wilayah RW 05.',
    ];

    private const REJECT_RW_NOTES = [
        'Kondisi ekonomi yang dilaporkan tidak memenuhi kriteria keluarga tidak mampu di kelurahan.',
        'Dokumen pendukung dari rumah sakit tidak dilampirkan untuk keperluan ini.',
    ];

    private LetterWorkflow $workflow;

    private LetterIssuer $issuer;

    public function run(): void
    {
        mt_srand(20261123);
        $this->workflow = app(LetterWorkflow::class);
        $this->issuer = app(LetterIssuer::class);

        // Bisa dilanjutkan bila sebelumnya terputus: warga yang sudah ada dipakai ulang,
        // dan hanya sisa pengajuan yang dibuat.
        $existing = Resident::query()->with(['user', 'rt.ketua'])
            ->whereHas('user', fn ($q) => $q->where('email', 'like', 'warga___@rtxrw.test'))
            ->get();

        if ($existing->count() >= self::RESIDENTS) {
            $residents = $existing;
            $this->command?->info('Warga massal sudah ada, dipakai ulang.');
        } else {
            $residents = $this->seedResidents($existing->pluck('user.email')->all());
            $this->command?->info(self::RESIDENTS.' warga tersedia.');
        }

        $done = LetterRequest::query()->whereIn('resident_id', $residents->pluck('id'))->count();
        if ($done >= self::REQUESTS) {
            $this->command?->info('Pengajuan massal sudah lengkap.');
        } else {
            $this->seedRequests($residents, $done);
            $this->command?->info(self::REQUESTS.' pengajuan beserta riwayatnya tersedia.');
        }

        Carbon::setTestNow();
        Auth::forgetUser();
        mt_srand();
    }

    /** @return Collection<int, Resident> */
    private function seedResidents(array $skipEmails = []): Collection
    {
        $rts = Rt::query()->orderBy('number')->get()->keyBy('number');
        $password = Hash::make('password123'); // di-hash sekali; cast "hashed" tidak menghash ulang
        $created = collect();

        for ($i = 1; $i <= self::RESIDENTS; $i++) {
            $female = $i % 2 === 0;
            $first = $this->pick($female ? self::FIRST_FEMALE : self::FIRST_MALE);
            $name = $first.' '.$this->pick(self::LAST);
            $rtNumber = array_keys(self::STREETS)[($i - 1) % 4];
            $rt = $rts[$rtNumber];
            $birth = Carbon::create(mt_rand(1950, 2006), mt_rand(1, 12), mt_rand(1, 28));
            $age = $birth->age;

            // 85% terverifikasi, 10% menunggu, 5% perlu perbaikan.
            $roll = mt_rand(1, 100);
            $status = $roll <= 85 ? VerificationStatus::Verified : ($roll <= 95 ? VerificationStatus::Pending : VerificationStatus::Rejected);

            if (in_array(sprintf('warga%03d@rtxrw.test', $i), $skipEmails, true)) {
                continue;
            }

            $this->retrying(fn () => DB::transaction(function () use ($i, $name, $female, $rt, $rtNumber, $birth, $age, $status, $password, $created) {
                $user = User::create([
                    'name' => $name,
                    'email' => sprintf('warga%03d@rtxrw.test', $i),
                    'phone' => '0857'.str_pad((string) (21000000 + $i * 137), 8, '0', STR_PAD_LEFT),
                    'password' => $password,
                    'role' => Role::Warga,
                ]);

                // NIK fiktif: 327304 + DDMMYY (tanggal +40 untuk perempuan, sesuai format NIK) + urut 2001–2100.
                $day = $birth->day + ($female ? 40 : 0);
                $resident = Resident::create([
                    'user_id' => $user->id,
                    'rt_id' => $rt->id,
                    'nik' => '327304'.sprintf('%02d', $day).$birth->format('my').sprintf('%04d', 2000 + $i),
                    'kk_number' => '327304'.sprintf('%010d', 3000000 + intdiv($i + 1, 2)),
                    'full_name' => $name,
                    'gender' => $female ? 'P' : 'L',
                    'birth_place' => $this->pick(self::PLACES),
                    'birth_date' => $birth,
                    'religion' => $this->pick(['Islam', 'Islam', 'Islam', 'Islam', 'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
                    'marital_status' => $age < 22 ? 'Belum Kawin' : $this->pick(['Kawin', 'Kawin', 'Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati']),
                    'occupation' => $age < 22 ? 'Pelajar/Mahasiswa' : ($age >= 60 ? $this->pick(['Pensiunan', 'Wiraswasta', 'Belum/Tidak Bekerja']) : $this->pick(self::JOBS)),
                    'address' => $this->pick(self::STREETS[$rtNumber]),
                    'house_number' => (string) mt_rand(1, 60),
                    'nationality' => 'WNI',
                ]);

                if ($status !== VerificationStatus::Pending) {
                    $resident->forceFill([
                        'verification_status' => $status,
                        'verified_at' => now()->subDays(mt_rand(30, 200)),
                        'verified_by' => $rt->ketua_user_id,
                        'verification_note' => $status === VerificationStatus::Rejected
                            ? 'Data alamat belum sesuai dengan catatan RT. Mohon perbarui nomor rumah.'
                            : null,
                    ])->save();
                }

                $created->push($resident->setRelation('user', $user)->setRelation('rt', $rt));
            }));
        }

        return Resident::query()->with(['user', 'rt.ketua'])
            ->whereHas('user', fn ($q) => $q->where('email', 'like', 'warga___@rtxrw.test'))
            ->get();
    }

    /** @param Collection<int, Resident> $residents */
    private function seedRequests(Collection $residents, int $skip = 0): void
    {
        // Rencana dibuat dengan seed acak tetap agar sama persis saat dilanjutkan.
        mt_srand(1123);
        $verified = $residents->filter(fn (Resident $r) => $r->verification_status === VerificationStatus::Verified)->values();
        $types = LetterType::query()->with('requirements')->get()->keyBy('code');
        $rwHead = Rw::current()->ketua;
        $toIssue = [];

        // Komposisi status akhir (total 50). Sebagian melewati siklus perbaikan dulu.
        $plan = array_merge(
            array_fill(0, 12, 'completed'),
            array_fill(0, 6, 'issued'),
            array_fill(0, 4, 'approved'),
            array_fill(0, 6, 'pending_rw'),
            array_fill(0, 8, 'pending_rt'),
            array_fill(0, 5, 'revision_required'),
            array_fill(0, 4, 'rejected_rt'),
            array_fill(0, 2, 'rejected_rw'),
            array_fill(0, 3, 'draft'),
        );

        // Pengajuan yang sudah selesai tersebar hingga 5 bulan ke belakang; yang masih berjalan lebih baru.
        // Diproses dari yang paling lama agar nomor surat urut sesuai tanggal terbit.
        $items = collect($plan)->map(fn ($target, $n) => [
            'n' => $n,
            'target' => $target,
            'daysAgo' => match ($target) {
                // Terbit paling lambat ~3 minggu lalu, sebelum surat demo utama di DatabaseSeeder.
                'completed' => mt_rand(26, 150),
                'issued' => mt_rand(24, 60),
                'rejected_rw', 'rejected_rt' => mt_rand(6, 60),
                'approved', 'pending_rw', 'revision_required' => mt_rand(2, 12),
                default => mt_rand(0, 4),
            },
        ])->sortByDesc('daysAgo')->values()->slice($skip);

        foreach ($items as ['n' => $n, 'target' => $target, 'daysAgo' => $daysAgo]) {
            DB::reconnect(); // koneksi segar per pengajuan; pooler Supabase kadang memutus sesi panjang

            $resident = $verified[($n * 7) % $verified->count()];
            $type = $types[$this->pick(array_keys(self::PURPOSES))];
            $rtHead = $resident->rt->ketua;
            $owner = $resident->user;

            // Selalu dihitung dari waktu sebenarnya, bukan dari waktu simulasi langkah sebelumnya.
            Carbon::setTestNow();
            $this->at(now()->subDays($daysAgo)->setTime(mt_rand(7, 20), mt_rand(0, 59)));

            $request = $this->createRequest($resident, $type);
            if ($target === 'draft') {
                continue;
            }

            $this->act($owner, fn () => $this->workflow->apply($request, WorkflowAction::Submit, $owner));

            // Sekitar sepertiga pengajuan yang lolos sempat diminta perbaikan dulu.
            $withRevision = $target === 'revision_required' || (in_array($target, ['completed', 'issued', 'pending_rw'], true) && $n % 3 === 0);
            if ($withRevision) {
                $byRw = $target !== 'revision_required' && $n % 2 === 0;
                if ($byRw) {
                    $this->act($rtHead, fn () => $this->workflow->apply($request, WorkflowAction::RtApprove, $rtHead), mt_rand(2, 20));
                    $this->act($rwHead, fn () => $this->workflow->apply($request, WorkflowAction::RwRevision, $rwHead, $this->pick(self::REVISION_NOTES)), mt_rand(3, 30));
                } else {
                    $this->act($rtHead, fn () => $this->workflow->apply($request, WorkflowAction::RtRevision, $rtHead, $this->pick(self::REVISION_NOTES)), mt_rand(2, 20));
                }
                if ($target === 'revision_required') {
                    continue;
                }
                $this->act($owner, fn () => $this->workflow->apply($request, WorkflowAction::Resubmit, $owner), mt_rand(3, 30));
            }

            if ($target === 'pending_rt') {
                continue;
            }
            if ($target === 'rejected_rt') {
                $this->act($rtHead, fn () => $this->workflow->apply($request, WorkflowAction::RtReject, $rtHead, $this->pick(self::REJECT_RT_NOTES)), mt_rand(2, 24));

                continue;
            }

            $this->act($rtHead, fn () => $this->workflow->apply($request, WorkflowAction::RtApprove, $rtHead), mt_rand(1, 24));
            if ($target === 'pending_rw') {
                continue;
            }
            if ($target === 'rejected_rw') {
                $this->act($rwHead, fn () => $this->workflow->apply($request, WorkflowAction::RwReject, $rwHead, $this->pick(self::REJECT_RW_NOTES)), mt_rand(3, 30));

                continue;
            }

            $this->act($rwHead, fn () => $this->workflow->apply($request, WorkflowAction::RwApprove, $rwHead), mt_rand(3, 30));
            if ($target === 'approved') {
                continue;
            }

            // Penerbitan ditunda lalu dijalankan berurutan waktu, agar nomor surat kronologis.
            $toIssue[] = [
                'request' => $request,
                'owner' => $owner,
                'complete' => $target === 'completed',
                'at' => now()->addHours(mt_rand(0, 3))->addMinutes(mt_rand(0, 59)),
            ];
        }

        usort($toIssue, fn ($a, $b) => $a['at'] <=> $b['at']);

        foreach ($toIssue as $item) {
            DB::reconnect();
            $this->at($item['at']);
            $this->act($rwHead, fn () => $this->issuer->issue($item['request'], $rwHead));
        }

        // Unduhan warga setelah semua nomor terbit (tidak memengaruhi penomoran).
        foreach ($toIssue as $item) {
            if (! $item['complete']) {
                continue;
            }
            $request = $item['request'];
            $owner = $item['owner'];
            $this->at($request->issued_at->copy());
            $this->act($owner, function () use ($request, $owner) {
                $request->issuedLetter->forceFill(['first_downloaded_at' => now()])->save();
                $this->workflow->apply($request, WorkflowAction::Complete, $owner);
            }, mt_rand(1, 48));
        }
    }

    private function createRequest(Resident $resident, LetterType $type): LetterRequest
    {
        $request = LetterRequest::create([
            'resident_id' => $resident->id,
            'rt_id' => $resident->rt_id,
            'letter_type_id' => $type->id,
            'purpose' => $this->pick(self::PURPOSES[$type->code]),
            'form_data' => $this->fieldsFor($type, $resident),
        ]);

        foreach ($type->requirements as $req) {
            if (! $req->is_required && mt_rand(0, 1)) {
                continue;
            }
            $path = 'documents/'.$request->public_id.'/'.bin2hex(random_bytes(12)).'.pdf';
            Storage::disk('local')->put($path, SamplePdf::make($req->name));
            $request->documents()->create([
                'letter_requirement_id' => $req->id,
                'original_name' => str($req->name)->slug().'.pdf',
                'path' => $path,
                'mime_type' => 'application/pdf',
                'size' => mt_rand(180, 1900) * 1024,
                'uploaded_by' => $resident->user_id,
            ]);
        }

        return $request;
    }

    /** Isian tambahan yang masuk akal untuk setiap jenis surat. */
    private function fieldsFor(LetterType $type, Resident $resident): array
    {
        $values = [];
        foreach ($type->fieldDefinitions() as $field) {
            $values[$field['key']] = match ($field['key']) {
                'lama_tinggal_di_alamat_ini' => mt_rand(1, 25).' tahun',
                'nama_usaha' => $this->pick(['Warung', 'Toko', 'Bengkel', 'Katering', 'Laundry']).' '.strtok($resident->full_name, ' '),
                'jenis_usaha' => $this->pick(['Warung sembako', 'Bengkel motor', 'Jasa katering', 'Jasa laundry', 'Jasa jahit']),
                'alamat_usaha' => $resident->address.' No. '.$resident->house_number,
                'mulai_usaha' => now()->subMonths(mt_rand(8, 96))->format('Y-m-d'),
                'penghasilan_keluarga_per_bulan' => (string) (mt_rand(8, 30) * 100000),
                'jumlah_tanggungan' => (string) mt_rand(2, 6),
                'nama_bayi' => $this->pick(self::FIRST_FEMALE).' '.$this->pick(self::LAST),
                'jenis_kelamin_bayi' => $this->pick(['Laki-laki', 'Perempuan']),
                'tempat_lahir_bayi' => 'Bandung',
                'tanggal_lahir_bayi' => now()->subDays(mt_rand(3, 40))->format('Y-m-d'),
                'nama_ayah' => $resident->gender === 'L' ? $resident->full_name : $this->pick(self::FIRST_MALE).' '.$this->pick(self::LAST),
                'nama_ibu' => $resident->gender === 'P' ? $resident->full_name : $this->pick(self::FIRST_FEMALE).' '.$this->pick(self::LAST),
                'nama_almarhum' => $this->pick(self::FIRST_MALE).' '.$this->pick(self::LAST),
                'nik_almarhum' => '327304'.sprintf('%010d', mt_rand(1000000, 9999999)),
                'tanggal_meninggal' => now()->subDays(mt_rand(1, 14))->format('Y-m-d'),
                'tempat_meninggal' => $this->pick(['Rumah duka', 'RSUD Kota Bandung', 'RS Santo Borromeus']),
                'sebab_meninggal' => $this->pick(['Sakit', 'Usia lanjut']),
                'hubungan_pelapor' => $this->pick(['Anak kandung', 'Suami', 'Istri', 'Saudara kandung']),
                'alamat_tujuan' => $this->pick(['Jl. Merdeka No. 20, Kota Bandung', 'Jl. Sudirman No. 5, Kota Cimahi', 'Perum. Bumi Asri Blok D No. 3, Kab. Bandung']),
                'alasan_pindah' => $this->pick(['Pekerjaan', 'Ikut keluarga', 'Membeli rumah']),
                'jumlah_anggota_keluarga_yang_pindah' => (string) mt_rand(1, 5),
                'perihal' => $this->pick(['Pengantar pembuatan Kartu Keluarga', 'Pengantar perekaman KTP-el', 'Pengantar nikah']),
                default => $field['type'] === 'number' ? (string) mt_rand(1, 10) : 'Contoh isian',
            };
        }

        return $values;
    }

    /** Jalankan aksi sebagai pengguna tertentu, beberapa jam setelah waktu simulasi sebelumnya. */
    private function act(User $user, callable $fn, int $hoursLater = 0): void
    {
        $this->at(now()->addHours($hoursLater)->addMinutes(mt_rand(0, 59)));
        Auth::setUser($user);
        $fn();
    }

    private function at(Carbon $time): void
    {
        // Jangan pernah melewati waktu sekarang yang sebenarnya.
        Carbon::setTestNow();
        Carbon::setTestNow($time->min(now()->subMinutes(5)));
    }

    /** Ulangi sekali dengan koneksi baru bila koneksi database terputus. */
    private function retrying(callable $fn): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            if (! str_contains($e->getMessage(), 'connection')) {
                throw $e;
            }
            sleep(2);
            DB::reconnect();

            return $fn();
        }
    }

    private function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }
}
