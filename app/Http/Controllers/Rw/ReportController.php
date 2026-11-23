<?php

namespace App\Http\Controllers\Rw;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Rt;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $base = LetterRequest::query()->visibleToRw()->whereBetween('submitted_at', [$from, $to]);

        $byStatus = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $byType = LetterType::query()->ordered()->get(['id', 'name'])->map(fn ($t) => [
            'name' => $t->name,
            'total' => (clone $base)->where('letter_type_id', $t->id)->count(),
            'done' => (clone $base)->where('letter_type_id', $t->id)->status(RequestStatus::done())->count(),
        ])->filter(fn ($r) => $r['total'] > 0)->sortByDesc('total')->values();

        $byRt = Rt::query()->orderBy('number')->get(['id', 'number'])->map(fn ($rt) => [
            'number' => $rt->number,
            'total' => (clone $base)->where('rt_id', $rt->id)->count(),
            'done' => (clone $base)->where('rt_id', $rt->id)->status(RequestStatus::done())->count(),
            'rejected' => (clone $base)->where('rt_id', $rt->id)->status([RequestStatus::RejectedByRt, RequestStatus::RejectedByRw])->count(),
        ]);

        $completed = (clone $base)->whereNotNull('issued_at')->get(['submitted_at', 'issued_at', 'rt_decided_at']);

        // Tren 6 bulan terakhir (pengajuan masuk vs surat terbit).
        $trend = collect(range(5, 0))->map(function ($i) {
            $start = now()->subMonthsNoOverflow($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            return [
                'label' => $start->translatedFormat('M Y'),
                'submitted' => LetterRequest::query()->whereBetween('submitted_at', [$start, $end])->count(),
                'issued' => LetterRequest::query()->whereBetween('issued_at', [$start, $end])->count(),
            ];
        });

        return view('rw.reports', [
            'from' => $from,
            'to' => $to,
            'total' => $byStatus->sum(),
            'byStatus' => $byStatus,
            'byType' => $byType,
            'byRt' => $byRt,
            'trend' => $trend,
            'avgHours' => $completed->isEmpty() ? null : round($completed->avg(fn ($r) => $r->submitted_at->diffInMinutes($r->issued_at)) / 60, 1),
            'avgRtHours' => $completed->whereNotNull('rt_decided_at')->isEmpty() ? null
                : round($completed->whereNotNull('rt_decided_at')->avg(fn ($r) => $r->submitted_at->diffInMinutes($r->rt_decided_at)) / 60, 1),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        ActivityLogger::log('report.exported', 'Mengunduh laporan pengajuan '.$from->format('d/m/Y').'–'.$to->format('d/m/Y'));

        $filename = 'laporan-rtxrw-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['No. Pengajuan', 'Tanggal Kirim', 'Jenis Surat', 'Pemohon', 'RT', 'Status', 'Nomor Surat', 'Tanggal Terbit'], ';');

            LetterRequest::query()->visibleToRw()->whereBetween('submitted_at', [$from, $to])
                ->with(['letterType', 'resident', 'rt', 'issuedLetter'])->orderBy('submitted_at')
                ->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->reference_number,
                            $r->submitted_at?->format('d/m/Y H:i'),
                            $r->letterType->name,
                            $r->resident->full_name,
                            $r->rt->number,
                            $r->status->label(),
                            $r->issuedLetter?->letter_number,
                            $r->issued_at?->format('d/m/Y'),
                        ], ';');
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function period(Request $request): array
    {
        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $from = $request->date('dari')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('sampai')?->endOfDay() ?? now()->endOfDay();

        return [$from, $to];
    }
}
