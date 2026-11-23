<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\ApprovalHistory;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->buildWorld();
    }

    public function test_ketua_rt_tidak_bisa_melihat_atau_memutus_pengajuan_rt_lain(): void
    {
        $request = $this->submitAs($this->makeResident($this->rt1));

        $this->actingAs($this->rt2Head)->get(route('rt.requests.show', $request))->assertForbidden();
        $this->actingAs($this->rt2Head)->post(route('rt.requests.decide', $request), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($this->rt2Head)->get(route('rt.requests.index', ['tab' => 'semua']))->assertDontSee($request->reference_number);

        $this->assertSame(RequestStatus::PendingRt, $request->fresh()->status);
    }

    public function test_ketua_rt_tidak_bisa_membuka_dokumen_atau_data_warga_rt_lain(): void
    {
        $resident = $this->makeResident($this->rt1);
        $request = $this->submitAs($resident);

        $this->actingAs($this->rt2Head)->get(route('documents.show', $request->documents->first()))->assertForbidden();
        $this->actingAs($this->rt2Head)->get(route('rt.residents.show', $resident))->assertForbidden();
    }

    public function test_warga_tidak_bisa_mengakses_halaman_admin(): void
    {
        $user = $this->makeResident()->user;

        foreach (['rt.dashboard', 'rt.requests.index', 'rw.dashboard', 'rw.requests.index', 'rw.letter-types.index', 'rw.activity.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }

    public function test_ketua_rt_tidak_bisa_mengakses_halaman_rw(): void
    {
        $this->actingAs($this->rt1Head)->get(route('rw.dashboard'))->assertForbidden();
        $this->actingAs($this->rt1Head)->get(route('rw.letter-types.index'))->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('warga.dashboard'))->assertRedirect(route('login'));
        $this->get(route('rw.dashboard'))->assertRedirect(route('login'));
    }

    public function test_warga_tidak_bisa_melihat_pengajuan_dan_dokumen_warga_lain(): void
    {
        $request = $this->submitAs($this->makeResident());
        $other = $this->makeResident();

        $this->actingAs($other->user)->get(route('warga.requests.show', $request))->assertForbidden();
        $this->actingAs($other->user)->get(route('documents.show', $request->documents->first()))->assertForbidden();
        $this->actingAs($other->user)->put(route('warga.requests.update', $request), ['purpose' => 'Ubah paksa'])->assertForbidden();
    }

    public function test_warga_tidak_bisa_melewati_rt_dan_rw(): void
    {
        $resident = $this->makeResident();
        $request = $this->submitAs($resident);

        // Mencoba memakai endpoint keputusan RT/RW dan penerbitan secara langsung.
        $this->actingAs($resident->user)->post(route('rt.requests.decide', $request), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($resident->user)->post(route('rw.requests.decide', $request), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($resident->user)->post(route('rw.requests.issue', $request))->assertForbidden();

        // Mencoba menyisipkan status lewat form ubah.
        $this->actingAs($resident->user)->put(route('warga.requests.update', $request), ['purpose' => 'x', 'status' => 'approved'])->assertForbidden();

        $this->assertSame(RequestStatus::PendingRt, $request->fresh()->status);
    }

    public function test_rw_tidak_bisa_melompati_verifikasi_rt(): void
    {
        $request = $this->submitAs($this->makeResident());

        $this->actingAs($this->rwHead)->post(route('rw.requests.decide', $request), ['decision' => 'approve'])
            ->assertSessionHas('error');
        $this->actingAs($this->rwHead)->post(route('rw.requests.issue', $request))
            ->assertSessionHas('error');

        $this->assertSame(RequestStatus::PendingRt, $request->fresh()->status);
        $this->assertSame(1, ApprovalHistory::where('letter_request_id', $request->id)->count());
    }

    public function test_keputusan_ganda_ditolak(): void
    {
        $request = $this->submitAs($this->makeResident());
        $this->actingAs($this->rt1Head)->post(route('rt.requests.decide', $request), ['decision' => 'approve']);

        $this->actingAs($this->rt1Head)->post(route('rt.requests.decide', $request), ['decision' => 'reject', 'note' => 'Berubah pikiran'])
            ->assertSessionHas('error');

        $this->assertSame(RequestStatus::PendingRw, $request->fresh()->status);
    }

    public function test_draf_tidak_terlihat_oleh_rt_dan_rw(): void
    {
        $request = $this->submitAs($this->makeResident(), 'draft');

        $this->actingAs($this->rt1Head)->get(route('rt.requests.show', $request))->assertForbidden();
        $this->actingAs($this->rwHead)->get(route('rw.requests.show', $request))->assertForbidden();
    }

    public function test_unggahan_berbahaya_atau_terlalu_besar_ditolak(): void
    {
        $resident = $this->makeResident();
        $reqId = $this->spdType()->requirements->first()->id;

        $this->actingAs($resident->user)->post(route('warga.requests.store', $this->spdType()), [
            'purpose' => 'Keperluan bank', 'intent' => 'draft',
            'documents' => [$reqId => UploadedFile::fake()->create('virus.php', 10, 'application/x-php')],
        ])->assertSessionHasErrors("documents.{$reqId}");

        $this->actingAs($resident->user)->post(route('warga.requests.store', $this->spdType()), [
            'purpose' => 'Keperluan bank', 'intent' => 'draft',
            'documents' => [$reqId => UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf')],
        ])->assertSessionHasErrors("documents.{$reqId}");

        $this->assertSame(0, $resident->letterRequests()->count());
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $this->rt1Head->update(['is_active' => false]);

        $this->post(route('login.store'), ['email' => 'rt1@test.id', 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
