<?php

namespace Tests\Feature;

use App\Models\LetterType;
use Tests\TestCase;

/** Memastikan setiap halaman tampil tanpa error untuk role yang berhak, dengan data nyata. */
class PagesRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->buildWorld();
    }

    public function test_halaman_publik(): void
    {
        $type = LetterType::first();

        foreach ([route('home'), route('about'), route('services'), route('letter-types'), route('letter-types.show', $type),
            route('verify.index'), route('verify.show', 'TIDAK-ADA-KODE'), route('login'), route('register')] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_halaman_warga(): void
    {
        $resident = $this->makeResident();
        $request = $this->submitAs($resident);
        $user = $resident->user;

        foreach ([route('warga.dashboard'), route('warga.profile.edit'), route('warga.requests.index'), route('warga.requests.create'),
            route('warga.requests.form', $this->spdType()), route('warga.requests.show', $request), route('notifications.index'), route('account.edit')] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_halaman_ketua_rt(): void
    {
        $resident = $this->makeResident();
        $request = $this->submitAs($resident);

        foreach ([route('rt.dashboard'), route('rt.residents.index'), route('rt.residents.show', $resident), route('rt.requests.index'),
            route('rt.requests.show', $request), route('rt.history')] as $url) {
            $this->actingAs($this->rt1Head)->get($url)->assertOk();
        }
    }

    public function test_halaman_ketua_rw(): void
    {
        $resident = $this->makeResident();
        $request = $this->submitAs($resident);
        $this->actingAs($this->rt1Head)->post(route('rt.requests.decide', $request), ['decision' => 'approve']);
        $type = $this->spdType();

        foreach ([route('rw.dashboard'), route('rw.requests.index'), route('rw.requests.show', $request), route('rw.issuance.index'),
            route('rw.rts.index'), route('rw.rts.create'), route('rw.rts.edit', $this->rt1), route('rw.heads.index'), route('rw.heads.edit', $this->rt1),
            route('rw.residents.index'), route('rw.residents.show', $resident), route('rw.letter-types.index'), route('rw.letter-types.create'),
            route('rw.letter-types.edit', $type), route('rw.templates.index'), route('rw.templates.edit', $type), route('rw.reports.index'),
            route('rw.activity.index'), route('rw.settings.edit')] as $url) {
            $this->actingAs($this->rwHead)->get($url)->assertOk();
        }

        $this->actingAs($this->rwHead)->get(route('rw.reports.export'))->assertOk();
    }
}
