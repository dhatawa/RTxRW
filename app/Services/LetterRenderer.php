<?php

namespace App\Services;

use App\Models\LetterRequest;
use App\Models\LetterTemplate;
use App\Models\LetterType;
use App\Models\Rw;
use Illuminate\Support\Carbon;

/**
 * Mengisi template surat dengan data pengajuan.
 *
 * Template berupa teks biasa dengan placeholder {{kunci}}. Seluruh teks template dan nilai
 * di-escape, sehingga isi template yang diedit admin tidak dapat menyisipkan HTML/skrip.
 */
class LetterRenderer
{
    /** Placeholder standar yang tersedia untuk semua jenis surat. */
    public const PLACEHOLDERS = [
        'nama' => 'Nama lengkap pemohon',
        'nik' => 'NIK pemohon',
        'no_kk' => 'Nomor KK',
        'tempat_lahir' => 'Tempat lahir',
        'tanggal_lahir' => 'Tanggal lahir',
        'tempat_tanggal_lahir' => 'Tempat, tanggal lahir',
        'jenis_kelamin' => 'Jenis kelamin',
        'agama' => 'Agama',
        'status_perkawinan' => 'Status perkawinan',
        'pekerjaan' => 'Pekerjaan',
        'kewarganegaraan' => 'Kewarganegaraan',
        'alamat' => 'Alamat lengkap (termasuk RT/RW)',
        'rt' => 'Nomor RT',
        'rw' => 'Nomor RW',
        'kelurahan' => 'Kelurahan',
        'kecamatan' => 'Kecamatan',
        'kota' => 'Kota/Kabupaten',
        'keperluan' => 'Keperluan yang ditulis pemohon',
        'nomor_surat' => 'Nomor surat',
        'tanggal_surat' => 'Tanggal surat diterbitkan',
        'tabel_identitas' => 'Tabel identitas pemohon (otomatis)',
    ];

    /** @return array<string, string> */
    public function values(LetterRequest $request, ?string $letterNumber = null, ?Carbon $date = null): array
    {
        $resident = $request->resident;
        $rt = $request->rt;
        $rw = Rw::current();
        $date ??= now();

        $values = [
            'nama' => $resident->full_name,
            'nik' => (string) $resident->nik,
            'no_kk' => (string) $resident->kk_number,
            'tempat_lahir' => (string) $resident->birth_place,
            'tanggal_lahir' => $resident->birth_date?->translatedFormat('j F Y') ?? '',
            'tempat_tanggal_lahir' => trim($resident->birth_place.', '.$resident->birth_date?->translatedFormat('j F Y'), ', '),
            'jenis_kelamin' => (string) $resident->genderLabel(),
            'agama' => (string) $resident->religion,
            'status_perkawinan' => (string) $resident->marital_status,
            'pekerjaan' => (string) $resident->occupation,
            'kewarganegaraan' => (string) $resident->nationality,
            'alamat' => $resident->fullAddress(),
            'rt' => $rt->number,
            'rw' => $rw->number,
            'kelurahan' => $rw->kelurahan,
            'kecamatan' => $rw->kecamatan,
            'kota' => $rw->city,
            'keperluan' => $request->purpose,
            'nomor_surat' => $letterNumber ?? '[nomor surat]',
            'tanggal_surat' => $date->translatedFormat('j F Y'),
        ];

        foreach ($request->letterType->fieldDefinitions() as $field) {
            $raw = $request->fieldValue($field['key']);
            $values[$field['key']] = $field['type'] === 'date' && filled($raw)
                ? Carbon::parse($raw)->translatedFormat('j F Y')
                : (string) $raw;
        }

        return $values;
    }

    /** @return list<array{0:string,1:string}> */
    public function identityRows(array $values): array
    {
        return [
            ['Nama', $values['nama']],
            ['NIK', $values['nik']],
            ['Tempat, tanggal lahir', $values['tempat_tanggal_lahir']],
            ['Jenis kelamin', $values['jenis_kelamin']],
            ['Agama', $values['agama']],
            ['Status perkawinan', $values['status_perkawinan']],
            ['Pekerjaan', $values['pekerjaan']],
            ['Kewarganegaraan', $values['kewarganegaraan']],
            ['Alamat', $values['alamat']],
        ];
    }

    /**
     * Ubah satu bagian template menjadi HTML aman.
     * Baris kosong memisahkan paragraf. {{tabel_identitas}} di baris sendiri menjadi tabel.
     */
    public function renderSection(string $text, array $values): string
    {
        $blocks = preg_split("/\R{2,}/", trim(str_replace("\r\n", "\n", $text))) ?: [];
        $html = '';

        foreach ($blocks as $block) {
            if (trim($block) === '{{tabel_identitas}}') {
                $html .= $this->identityTable($values);

                continue;
            }

            $escaped = e($block);
            // Tanda baca tepat setelah placeholder ikut masuk span agar tidak terpisah ke baris baru di PDF.
            $filled = preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}([.,;:)]?)/i', function ($m) use ($values) {
                $key = strtolower($m[1]);
                if ($key === 'tabel_identitas') {
                    return '';
                }

                // $m[0] sudah ter-escape karena berasal dari $escaped.
                return array_key_exists($key, $values) ? '<span class="v">'.e($values[$key]).$m[2].'</span>' : $m[0];
            }, $escaped);

            $html .= '<p>'.nl2br($filled, false).'</p>';
        }

        return $html;
    }

    public function identityTable(array $values): string
    {
        $rows = '';
        foreach ($this->identityRows($values) as [$label, $value]) {
            $rows .= '<tr><td class="l">'.e($label).'</td><td class="c">:</td><td>'.e($value).'</td></tr>';
        }

        return '<table class="identity">'.$rows.'</table>';
    }

    /** @return array{title:string,opening:string,body:string,closing:string} */
    public function render(LetterTemplate $template, array $values): array
    {
        return [
            'title' => e(mb_strtoupper($template->title)),
            'opening' => $this->renderSection($template->opening, $values),
            'body' => $this->renderSection($template->body, $values),
            'closing' => $this->renderSection($template->closing, $values),
        ];
    }

    /** Kunci placeholder yang dikenali untuk jenis surat tertentu (standar + isian tambahan). */
    public static function placeholdersFor(?LetterType $type): array
    {
        $list = self::PLACEHOLDERS;
        foreach ($type?->fieldDefinitions() ?? [] as $field) {
            $list[$field['key']] = $field['label'].' (isian tambahan)';
        }

        return $list;
    }
}
