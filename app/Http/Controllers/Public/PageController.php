<?php

namespace App\Http\Controllers\Public;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\IssuedLetter;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Rw;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $rw = Rw::query()->with(['rts' => fn ($q) => $q->where('is_active', true)])->first();

        return view('public.home', [
            'rw' => $rw,
            'letterTypes' => LetterType::active()->ordered()->get(),
            'stats' => [
                'issued_month' => IssuedLetter::query()->whereBetween('issued_at', [now()->startOfMonth(), now()])->count(),
                'avg_hours' => $this->averageHours(),
            ],
        ]);
    }

    public function about(): View
    {
        return view('public.about', ['rw' => Rw::query()->with('rts.ketua', 'ketua')->first()]);
    }

    public function services(): View
    {
        return view('public.services', ['rw' => Rw::query()->first()]);
    }

    public function letterTypes(): View
    {
        return view('public.letter-types', [
            'letterTypes' => LetterType::active()->ordered()->with('requirements')->get(),
        ]);
    }

    public function letterType(LetterType $letterType): View
    {
        abort_unless($letterType->is_active, 404);

        return view('public.letter-type', [
            'type' => $letterType->load('requirements'),
        ]);
    }

    /** Rata-rata jam dari pengajuan dikirim sampai surat terbit, 90 hari terakhir. */
    private function averageHours(): ?int
    {
        $rows = LetterRequest::query()
            ->status(RequestStatus::done())
            ->whereNotNull('submitted_at')->whereNotNull('issued_at')
            ->where('issued_at', '>=', now()->subDays(90))
            ->get(['submitted_at', 'issued_at']);

        if ($rows->isEmpty()) {
            return null;
        }

        return (int) round($rows->avg(fn ($r) => $r->submitted_at->diffInMinutes($r->issued_at)) / 60);
    }
}
