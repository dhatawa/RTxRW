<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->buildWorld();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Siti Aminah',
            'nik' => '3273045501900001',
            'rt_id' => $this->rt1->id,
            'phone' => '081234567890',
            'email' => 'siti@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'consent' => '1',
        ], $overrides);
    }

    public function test_warga_berhasil_registrasi_dan_menunggu_verifikasi_rt(): void
    {
        $this->post(route('register.store'), $this->payload())
            ->assertRedirect(route('warga.profile.edit'));

        $user = User::where('email', 'siti@example.test')->firstOrFail();
        $this->assertSame(Role::Warga, $user->role);
        $this->assertAuthenticatedAs($user);

        $resident = $user->resident;
        $this->assertSame(VerificationStatus::Pending, $resident->verification_status);
        $this->assertSame($this->rt1->id, $resident->rt_id);

        // Ketua RT mendapat notifikasi warga baru.
        $this->assertSame(1, $this->rt1Head->notifications()->count());
    }

    public function test_nik_disimpan_terenkripsi(): void
    {
        $this->post(route('register.store'), $this->payload());

        $raw = DB::table('residents')->value('nik');
        $this->assertNotSame('3273045501900001', $raw);
        $this->assertStringNotContainsString('3273045501900001', $raw);
        $this->assertSame('3273045501900001', Resident::first()->nik);
    }

    public function test_nik_ganda_ditolak(): void
    {
        $this->makeResident(nik: '3273045501900001');

        $this->post(route('register.store'), $this->payload())
            ->assertSessionHasErrors('nik');
    }

    public function test_warga_belum_terverifikasi_tidak_bisa_mengajukan(): void
    {
        $resident = $this->makeResident(verified: false);

        $this->actingAs($resident->user)
            ->post(route('warga.requests.store', $this->spdType()), $this->spdPayload())
            ->assertRedirect(route('warga.requests.create'));

        $this->assertSame(0, $resident->letterRequests()->count());
    }

    public function test_ketua_rt_memverifikasi_warga(): void
    {
        $resident = $this->makeResident(verified: false);

        $this->actingAs($this->rt1Head)
            ->post(route('rt.residents.verify', $resident), ['decision' => 'verify'])
            ->assertRedirect();

        $this->assertTrue($resident->fresh()->isVerified());
        $this->assertSame(1, $resident->user->notifications()->count());
    }

    public function test_ketua_rt_lain_tidak_bisa_memverifikasi(): void
    {
        $resident = $this->makeResident(verified: false);

        $this->actingAs($this->rt2Head)
            ->post(route('rt.residents.verify', $resident), ['decision' => 'verify'])
            ->assertForbidden();

        $this->assertFalse($resident->fresh()->isVerified());
    }
}
