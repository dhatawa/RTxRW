<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\WorkflowAction;
use App\Models\ActivityLog;
use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->buildWorld();
    }

    public function test_alur_lengkap_dari_pengajuan_sampai_surat_diunduh(): void
    {
        $resident = $this->makeResident();

        // 1–2. Warga mengajukan surat.
        $request = $this->submitAs($resident);
        $this->assertSame(RequestStatus::PendingRt, $request->status);
        $this->assertSame($this->rt1->id, $request->rt_id);
        $this->assertCount(2, $request->documents);

        // 3. Ketua RT menerima pengajuan (muncul di daftar & bisa dibuka).
        $this->actingAs($this->rt1Head)->get(route('rt.requests.index'))
            ->assertOk()->assertSee($resident->full_name);
        $this->actingAs($this->rt1Head)->get(route('rt.requests.show', $request))->assertOk();

        // 4. Ketua RT menyetujui.
        $this->actingAs($this->rt1Head)
            ->post(route('rt.requests.decide', $request), ['decision' => 'approve'])
            ->assertRedirect();
        $this->assertSame(RequestStatus::PendingRw, $request->fresh()->status);

        // 5. Ketua RW menerima pengajuan.
        $this->actingAs($this->rwHead)->get(route('rw.requests.index'))
            ->assertOk()->assertSee($resident->full_name);

        // 6. Ketua RW menyetujui.
        $this->actingAs($this->rwHead)
            ->post(route('rw.requests.decide', $request), ['decision' => 'approve'])
            ->assertRedirect();
        $this->assertSame(RequestStatus::Approved, $request->fresh()->status);

        // Sebelum terbit, PDF belum bisa diunduh.
        $this->actingAs($resident->user)->get(route('letters.download', $request))->assertForbidden();

        // 7. Surat PDF dibuat.
        $this->actingAs($this->rwHead)->post(route('rw.requests.issue', $request))->assertRedirect();
        $request->refresh();
        $this->assertSame(RequestStatus::Issued, $request->status);
        $letter = $request->issuedLetter;
        $this->assertNotNull($letter);
        $this->assertMatchesRegularExpression('#^001/SPD/RT01/RW05/[IVX]+/\d{4}$#', $letter->letter_number);
        Storage::disk('local')->assertExists($letter->file_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($letter->file_path));

        // 8. Warga mengunduh surat.
        $this->actingAs($resident->user)->get(route('letters.download', $request))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertSame(RequestStatus::Completed, $request->fresh()->status);

        // 12. Semua aktivitas tercatat.
        $actions = ApprovalHistory::where('letter_request_id', $request->id)->orderBy('id')->pluck('action')->all();
        $this->assertSame([
            WorkflowAction::Submit, WorkflowAction::RtApprove, WorkflowAction::RwApprove, WorkflowAction::Issue, WorkflowAction::Complete,
        ], $actions);
        $this->assertSame(5, ActivityLog::where('action', 'like', 'workflow.%')->where('subject_id', $request->id)->count());

        // Warga diberi tahu setiap keputusan petugas.
        $this->assertSame(3, $resident->user->notifications()->count());
    }

    public function test_nomor_surat_berurutan(): void
    {
        foreach ([1, 2] as $i) {
            $request = $this->submitAs($this->makeResident());
            $this->actingAs($this->rt1Head)->post(route('rt.requests.decide', $request), ['decision' => 'approve']);
            $this->actingAs($this->rwHead)->post(route('rw.requests.decide', $request), ['decision' => 'approve']);
            $this->actingAs($this->rwHead)->post(route('rw.requests.issue', $request));
            $this->assertStringStartsWith(sprintf('%03d/', $i), $request->fresh()->issuedLetter->letter_number);
        }
    }

    public function test_pengajuan_revisi_dari_rt_dapat_dikirim_ulang(): void
    {
        $resident = $this->makeResident();
        $request = $this->submitAs($resident);

        $this->actingAs($this->rt1Head)
            ->post(route('rt.requests.decide', $request), ['decision' => 'revision', 'note' => 'Foto KTP buram, mohon diunggah ulang.'])
            ->assertRedirect();
        $request->refresh();
        $this->assertSame(RequestStatus::RevisionRequired, $request->status);
        $this->assertSame('Foto KTP buram, mohon diunggah ulang.', $request->last_note);

        // Warga memperbaiki dan mengirim ulang.
        $type = $this->spdType();
        $this->actingAs($resident->user)
            ->put(route('warga.requests.update', $request), [
                'purpose' => 'Persyaratan pembukaan rekening bank BRI',
                'intent' => 'submit',
                'documents' => [$type->requirements->first()->id => \Illuminate\Http\UploadedFile::fake()->image('ktp.jpg')],
            ])->assertRedirect(route('warga.requests.show', $request));

        $request->refresh();
        $this->assertSame(RequestStatus::PendingRt, $request->status);
        $this->assertSame(1, $request->revision_count);
        $this->assertNull($request->last_note);
        $this->assertSame(2, $request->documents()->count(), 'Dokumen lama diganti, bukan ditambah.');
        $this->assertSame(WorkflowAction::Resubmit, $request->histories->last()->action);
    }

    public function test_revisi_dari_rw_kembali_diverifikasi_rt(): void
    {
        $resident = $this->makeResident();
        $request = $this->submitAs($resident);
        $this->actingAs($this->rt1Head)->post(route('rt.requests.decide', $request), ['decision' => 'approve']);
        $this->actingAs($this->rwHead)->post(route('rw.requests.decide', $request), ['decision' => 'revision', 'note' => 'Keperluan kurang jelas.']);

        $this->assertSame(RequestStatus::RevisionRequired, $request->fresh()->status);

        $this->actingAs($resident->user)->post(route('warga.requests.submit', $request))->assertRedirect();
        $this->assertSame(RequestStatus::PendingRt, $request->fresh()->status);
    }

    public function test_penolakan_wajib_disertai_alasan(): void
    {
        $request = $this->submitAs($this->makeResident());

        $this->actingAs($this->rt1Head)
            ->post(route('rt.requests.decide', $request), ['decision' => 'reject'])
            ->assertSessionHasErrors('note');
        $this->assertSame(RequestStatus::PendingRt, $request->fresh()->status);

        $this->actingAs($this->rt1Head)
            ->post(route('rt.requests.decide', $request), ['decision' => 'reject', 'note' => 'Bukan warga RT kami.']);
        $this->assertSame(RequestStatus::RejectedByRt, $request->fresh()->status);
    }

    public function test_draf_bisa_disimpan_tanpa_dokumen_tetapi_tidak_bisa_dikirim(): void
    {
        $resident = $this->makeResident();
        $this->actingAs($resident->user)
            ->post(route('warga.requests.store', $this->spdType()), ['purpose' => 'Keperluan bank', 'intent' => 'draft'])
            ->assertRedirect();
        $request = $resident->letterRequests()->first();
        $this->assertSame(RequestStatus::Draft, $request->status);

        $this->actingAs($resident->user)->post(route('warga.requests.submit', $request))
            ->assertSessionHas('error');
        $this->assertSame(RequestStatus::Draft, $request->fresh()->status);
    }
}
