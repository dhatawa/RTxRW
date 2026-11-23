<?php

namespace App\Http\Controllers\Rw;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Models\Rw;
use App\Services\ActivityLogger;
use App\Services\LetterRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Template berversi: menyimpan perubahan selalu membuat versi baru, sehingga surat yang sudah
 * terbit tetap bisa ditelusuri ke versi template yang dipakai.
 */
class LetterTemplateController extends Controller
{
    public function index(): View
    {
        return view('rw.templates.index', [
            'letterTypes' => LetterType::query()->ordered()->with('activeTemplate')->withCount('templates')->get(),
        ]);
    }

    public function edit(Request $request, LetterType $letterType): View
    {
        $letterType->load(['templates.author', 'activeTemplate']);
        $base = $request->query('versi')
            ? $letterType->templates->firstWhere('version', (int) $request->query('versi'))
            : $letterType->activeTemplate;

        return view('rw.templates.edit', [
            'type' => $letterType,
            'template' => $base,
            'placeholders' => LetterRenderer::placeholdersFor($letterType),
        ]);
    }

    public function store(Request $request, LetterType $letterType): RedirectResponse
    {
        $data = $this->validated($request);

        $template = DB::transaction(function () use ($request, $letterType, $data) {
            // Kunci baris jenis surat sebagai mutex versi (PostgreSQL melarang FOR UPDATE + MAX()).
            LetterType::query()->whereKey($letterType->getKey())->lockForUpdate()->first();
            $version = (int) $letterType->templates()->max('version') + 1;
            $letterType->templates()->update(['is_active' => false]);

            $template = $letterType->templates()->create($data + [
                'version' => $version,
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);

            ActivityLogger::log('template.created', "Template {$letterType->name} versi {$version} dibuat dan diaktifkan", $template);

            return $template;
        });

        return redirect()->route('rw.templates.edit', $letterType)
            ->with('success', "Template versi {$template->version} disimpan dan menjadi template aktif.");
    }

    public function activate(LetterTemplate $template): RedirectResponse
    {
        DB::transaction(function () use ($template) {
            $template->letterType->templates()->update(['is_active' => false]);
            $template->update(['is_active' => true]);
        });

        ActivityLogger::log('template.activated', "Mengaktifkan template {$template->letterType->name} versi {$template->version}", $template);

        return back()->with('success', "Versi {$template->version} sekarang menjadi template aktif.");
    }

    /** Pratinjau dengan data contoh, tanpa menyimpan. */
    public function preview(Request $request, LetterType $letterType, LetterRenderer $renderer): View
    {
        $data = $this->validated($request);
        $template = new LetterTemplate($data + ['version' => 0]);

        $rw = Rw::current()->load('ketua');
        $rt = $rw->rts()->with('ketua')->first();

        $values = [
            'nama' => 'Contoh Nama Warga', 'nik' => '3273000000000000', 'no_kk' => '3273000000000001',
            'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '17 Agustus 1990', 'tempat_tanggal_lahir' => 'Bandung, 17 Agustus 1990',
            'jenis_kelamin' => 'Perempuan', 'agama' => 'Islam', 'status_perkawinan' => 'Kawin', 'pekerjaan' => 'Karyawan Swasta',
            'kewarganegaraan' => 'WNI', 'alamat' => 'Jl. Contoh No. 1, RT '.($rt?->number ?? '01').'/RW '.$rw->number,
            'rt' => $rt?->number ?? '01', 'rw' => $rw->number, 'kelurahan' => $rw->kelurahan, 'kecamatan' => $rw->kecamatan,
            'kota' => $rw->city, 'keperluan' => 'Contoh keperluan pemohon', 'nomor_surat' => '001/'.$letterType->code.'/RT'.($rt?->number ?? '01').'/RW'.$rw->number.'/'.now()->format('m').'/'.now()->year,
            'tanggal_surat' => now()->translatedFormat('j F Y'),
        ];
        foreach ($letterType->fieldDefinitions() as $field) {
            $values[$field['key']] = '['.$field['label'].']';
        }

        return view('rw.templates.preview', [
            'type' => $letterType,
            'sections' => $renderer->render($template, $values),
            'rw' => $rw,
            'rt' => $rt,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'opening' => ['required', 'string', 'max:3000'],
            'body' => ['required', 'string', 'max:5000'],
            'closing' => ['required', 'string', 'max:3000'],
        ], [], ['title' => 'judul surat', 'opening' => 'pembuka', 'body' => 'isi', 'closing' => 'penutup']);
    }
}
