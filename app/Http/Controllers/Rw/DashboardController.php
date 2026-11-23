<?php

namespace App\Http\Controllers\Rw;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\LetterRequest;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth();
        $base = LetterRequest::query()->visibleToRw();

        $perRt = Rt::query()->orderBy('number')->with('ketua')
            ->withCount([
                'residents',
                'letterRequests as pending_rt_count' => fn ($q) => $q->where('status', RequestStatus::PendingRt->value),
                'letterRequests as month_count' => fn ($q) => $q->where('submitted_at', '>=', $monthStart),
            ])->get();

        return view('rw.dashboard', [
            'rw' => Rw::current(),
            'stats' => [
                'pending_rw' => (clone $base)->status(RequestStatus::PendingRw)->count(),
                'to_issue' => (clone $base)->status(RequestStatus::Approved)->count(),
                'pending_rt' => (clone $base)->status(RequestStatus::PendingRt)->count(),
                'month_issued' => (clone $base)->status(RequestStatus::done())->where('issued_at', '>=', $monthStart)->count(),
                'residents' => Resident::query()->count(),
            ],
            'queue' => (clone $base)->with(['letterType', 'resident', 'rt'])
                ->status([RequestStatus::PendingRw, RequestStatus::Approved])
                ->orderByRaw("CASE status WHEN 'approved' THEN 0 ELSE 1 END")
                ->oldest('rt_decided_at')->take(8)->get(),
            'perRt' => $perRt,
        ]);
    }
}
