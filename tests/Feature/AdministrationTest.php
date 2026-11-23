<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\IssuedLetter;
use App\Models\LetterType;
use App\Models\User;
use Tests\TestCase;

class AdministrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->buildWorld();
    }

    public function test_rw_menambah_jenis_surat_dengan_persyaratan_dan_isian(): void
    {
        $this->actingAs($this->rwHead)->post(route('rw.letter-types.store'), [
            'code' => 'skbm', 'name' => 'Surat Keterangan Belum Menikah', 'signatory' => 'rt_rw', 'processing_days' => 2, 'is_active' => '1',
            'requirements' => [['name' => 'Foto KTP', 'is_required' => '1'], ['name' => 'Foto KK', 'is_required' => '0']],
            'fields' => [['label' => 'Nama orang tua', 'type' => 'text', 'required' => '1']],
        ])->assertRedirect();

        $type = LetterType::where('code', 'SKBM')->firstOrFail();
        $this->assertCount(2, $type->requirements);
        $this->assertSame('nama_orang_tua', $type->fieldDefinitions()[0]['key']);
        $this->assertNull($type->activeTemplate, 'Jenis baru belum punya template sampai dibuat.');
    }

    public function test_menonaktifkan_jenis_surat_menutup_pengajuan_baru(): void
    {
        $type = $this->spdType();
        $this->actingAs($this->rwHead)->post(route('rw.letter-types.toggle', $type));
        $this->assertFalse($type->fresh()->is_active);

        $resident = $this->makeResident();
        $this->actingAs($resident->user)->get(route('warga.requests.form', $type))->assertNotFound();
    }

    public function test_template_baru_membuat_versi_dan_mengaktifkannya(): void
    {
        $type = $this->spdType();
        $this->actingAs($this->rwHead)->post(route('rw.templates.store', $type), [
            'title' => 'Surat Pengantar', 'opening' => 'Pembuka {{nama}}', 'body' => "{{tabel_identitas}}\n\nIsi.", 'closing' => 'Penutup.',
        ])->assertRedirect();

        $type->refresh();
        $this->assertSame(2, $type->activeTemplate->version);
        $this->assertSame(1, $type->templates()->where('is_active', true)->count());
    }

    public function test_template_tidak_bisa_menyisipkan_html(): void
    {
        $type = $this->spdType();
        $this->actingAs($this->rwHead)->post(route('rw.templates.preview', $type), [
            'title' => 'Uji', 'opening' => '<script>alert(1)</script>', 'body' => '{{nama}}', 'closing' => 'x',
        ])->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_mengganti_ketua_rt_menonaktifkan_akun_lama(): void
    {
        $old = $this->rt1Head;
        $this->actingAs($this->rwHead)->put(route('rw.heads.update', $this->rt1), [
            'mode' => 'replace', 'name' => 'Ketua Baru', 'email' => 'baru@test.id',
            'password' => 'katasandi1', 'password_confirmation' => 'katasandi1',
        ])->assertRedirect();

        $this->assertFalse($old->fresh()->is_active);
        $new = User::where('email', 'baru@test.id')->firstOrFail();
        $this->assertSame(Role::KetuaRt, $new->role);
        $this->assertSame($new->id, $this->rt1->fresh()->ketua_user_id);
    }

    public function test_verifikasi_keaslian_surat(): void
    {
        $request = $this->submitAs($this->makeResident());
        $this->actingAs($this->rt1Head)->post(route('rt.requests.decide', $request), ['decision' => 'approve']);
        $this->actingAs($this->rwHead)->post(route('rw.requests.decide', $request), ['decision' => 'approve']);
        $this->actingAs($this->rwHead)->post(route('rw.requests.issue', $request));
        auth()->logout();

        $letter = IssuedLetter::firstOrFail();
        $this->get(route('verify.show', $letter->verification_code))
            ->assertOk()->assertSee('Surat ini asli')->assertSee($letter->letter_number)
            ->assertDontSee($request->resident->nik);
    }
}
