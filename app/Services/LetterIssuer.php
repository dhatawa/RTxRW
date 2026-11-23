<?php

namespace App\Services;

use App\Enums\WorkflowAction;
use App\Exceptions\WorkflowException;
use App\Models\IssuedLetter;
use App\Models\LetterRequest;
use App\Models\Rw;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/** Menerbitkan surat: memberi nomor, merender PDF, menyimpan secara privat, dan mengubah status. */
class LetterIssuer
{
    public function __construct(
        private LetterWorkflow $workflow,
        private LetterRenderer $renderer,
    ) {}

    public function issue(LetterRequest $request, User $actor): IssuedLetter
    {
        if (! $this->workflow->can($request, WorkflowAction::Issue, $actor)) {
            throw new WorkflowException('Surat ini belum dapat diterbitkan.');
        }

        $template = $request->letterType->activeTemplate;
        if (! $template) {
            throw new WorkflowException('Jenis surat ini belum memiliki template aktif. Atur template terlebih dahulu.');
        }

        $path = null;
        $original = $request;

        try {
            $letter = DB::transaction(function () use ($request, $actor, $template, &$path) {
                // Kunci baris pengajuan agar klik ganda tidak menghasilkan dua nomor surat.
                $request = LetterRequest::query()->whereKey($request->getKey())->lockForUpdate()->firstOrFail();
                if (! $this->workflow->can($request, WorkflowAction::Issue, $actor) || $request->issuedLetter()->exists()) {
                    throw new WorkflowException('Surat ini sudah diterbitkan atau statusnya berubah.');
                }

                $now = now();
                $year = (int) $now->format('Y');

                // Kunci baris RW sebagai mutex penomoran (portabel: PostgreSQL melarang FOR UPDATE + MAX()).
                // Unique index (year, sequence) tetap menjadi pengaman terakhir.
                Rw::query()->whereKey(Rw::current()->getKey())->lockForUpdate()->first();
                $sequence = (int) IssuedLetter::query()->where('year', $year)->max('sequence') + 1;
                $number = $this->formatNumber($sequence, $request, $now->month, $year);
                $code = $this->verificationCode();

                $values = $this->renderer->values($request, $number, $now);
                $sections = $this->renderer->render($template, $values);
                $rw = Rw::current()->load('ketua');
                $rt = $request->rt->load('ketua');

                $pdf = Pdf::loadView('pdf.letter', [
                    'sections' => $sections,
                    'number' => $number,
                    'date' => $now,
                    'rw' => $rw,
                    'rt' => $rt,
                    'type' => $request->letterType,
                    'verificationCode' => $code,
                    'qr' => $this->qr(route('verify.show', $code)),
                    'verifyUrl' => route('verify.show', $code),
                ])->setPaper('a4');

                $path = 'letters/'.$year.'/'.Str::ulid().'.pdf';
                Storage::disk('local')->put($path, $pdf->output());

                $letter = IssuedLetter::create([
                    'letter_request_id' => $request->id,
                    'letter_template_id' => $template->id,
                    'letter_number' => $number,
                    'sequence' => $sequence,
                    'year' => $year,
                    'verification_code' => $code,
                    'file_path' => $path,
                    'snapshot' => [
                        'name' => $values['nama'],
                        'letter_type' => $request->letterType->name,
                        'rt' => $rt->number,
                        'rw' => $rw->number,
                        'purpose' => $request->purpose,
                        'template_version' => $template->version,
                        'signed_by_rt' => $rt->ketua?->name,
                        'signed_by_rw' => $rw->ketua?->name,
                    ],
                    'issued_by' => $actor->id,
                    'issued_at' => $now,
                ]);

                $this->workflow->apply($request, WorkflowAction::Issue, $actor, "Nomor surat {$number}");

                return $letter;
            });

            $original->setRawAttributes($letter->letterRequest()->first()->getAttributes(), true);
            $original->setRelation('issuedLetter', $letter);

            return $letter;
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    public function formatNumber(int $sequence, LetterRequest $request, int $month, int $year): string
    {
        $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$month - 1];

        return sprintf(
            '%03d/%s/RT%s/RW%s/%s/%d',
            $sequence,
            $request->letterType->code,
            $request->rt->number,
            Rw::current()->number,
            $roman,
            $year,
        );
    }

    private function verificationCode(): string
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
            $code = strtr($code, ['0' => 'X', 'O' => 'Y', 'I' => 'Z', '1' => 'W', 'L' => 'K']);
        } while (IssuedLetter::where('verification_code', $code)->exists());

        return $code;
    }

    private function qr(string $data): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 4,
            'quietzoneSize' => 1,
            'outputBase64' => true,
        ]);

        return (new QRCode($options))->render($data);
    }
}
