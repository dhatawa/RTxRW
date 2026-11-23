<?php

namespace Tests;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use Database\Seeders\LetterTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected User $rwHead;

    protected Rw $rw;

    protected Rt $rt1;

    protected Rt $rt2;

    protected User $rt1Head;

    protected User $rt2Head;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    /** Satu RW dengan dua RT, masing-masing punya Ketua RT, plus jenis surat bawaan. */
    protected function buildWorld(): void
    {
        $this->rwHead = User::create(['name' => 'Ketua RW', 'email' => 'rw@test.id', 'password' => 'password123', 'role' => Role::KetuaRw]);
        $this->rw = Rw::create([
            'number' => '05', 'kelurahan' => 'Mekarsari', 'kecamatan' => 'Sukajadi', 'city' => 'Kota Bandung',
            'province' => 'Jawa Barat', 'ketua_user_id' => $this->rwHead->id,
        ]);

        $this->rt1Head = User::create(['name' => 'Ketua RT Satu', 'email' => 'rt1@test.id', 'password' => 'password123', 'role' => Role::KetuaRt]);
        $this->rt2Head = User::create(['name' => 'Ketua RT Dua', 'email' => 'rt2@test.id', 'password' => 'password123', 'role' => Role::KetuaRt]);
        $this->rt1 = Rt::create(['rw_id' => $this->rw->id, 'number' => '01', 'ketua_user_id' => $this->rt1Head->id]);
        $this->rt2 = Rt::create(['rw_id' => $this->rw->id, 'number' => '02', 'ketua_user_id' => $this->rt2Head->id]);

        $this->seed(LetterTypeSeeder::class);
    }

    protected function makeResident(?Rt $rt = null, bool $verified = true, string $nik = null): Resident
    {
        static $n = 0;
        $n++;
        $rt ??= $this->rt1;

        $user = User::create([
            'name' => "Warga {$n}", 'email' => "warga{$n}@test.id", 'password' => 'password123', 'role' => Role::Warga,
        ]);

        $resident = Resident::create([
            'user_id' => $user->id, 'rt_id' => $rt->id,
            'nik' => $nik ?? '32730401019'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'full_name' => "Warga {$n}", 'birth_place' => 'Bandung', 'birth_date' => '1990-01-01', 'gender' => 'L',
            'religion' => 'Islam', 'marital_status' => 'Kawin', 'occupation' => 'Karyawan Swasta', 'address' => 'Jl. Uji Coba',
        ]);

        if ($verified) {
            $resident->forceFill(['verification_status' => VerificationStatus::Verified, 'verified_at' => now(), 'verified_by' => $rt->ketua_user_id])->save();
        }

        return $resident->fresh();
    }

    protected function spdType(): LetterType
    {
        return LetterType::where('code', 'SPD')->firstOrFail();
    }

    /** Payload form pengajuan SPD lengkap dengan dokumen wajib. */
    protected function spdPayload(string $intent = 'submit'): array
    {
        $type = $this->spdType();
        $documents = [];
        foreach ($type->requirements as $req) {
            $documents[$req->id] = UploadedFile::fake()->create('dok.pdf', 120, 'application/pdf');
        }

        return ['purpose' => 'Persyaratan pembukaan rekening bank', 'intent' => $intent, 'documents' => $documents];
    }

    /** Buat pengajuan lewat HTTP sebagai warga dan kembalikan modelnya. */
    protected function submitAs(Resident $resident, string $intent = 'submit'): LetterRequest
    {
        $this->actingAs($resident->user)
            ->post(route('warga.requests.store', $this->spdType()), $this->spdPayload($intent))
            ->assertRedirect();

        return LetterRequest::where('resident_id', $resident->id)->latest('id')->firstOrFail();
    }
}
