<?php

namespace App\Http\Controllers\Warga;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $resident = $request->user()->resident()->with('rt.rw')->firstOrFail();
        $requests = $resident->letterRequests();

        $counts = (clone $requests)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $sum = fn (array $statuses) => collect($statuses)->sum(fn ($s) => $counts[$s->value] ?? 0);

        return view('warga.dashboard', [
            'resident' => $resident,
            'stats' => [
                'total' => $counts->sum(),
                'in_progress' => $sum([RequestStatus::PendingRt, RequestStatus::PendingRw, RequestStatus::Approved]),
                'needs_action' => $sum([RequestStatus::RevisionRequired, RequestStatus::Draft]),
                'done' => $sum(RequestStatus::done()),
            ],
            'attention' => (clone $requests)->with('letterType')
                ->status([RequestStatus::RevisionRequired, RequestStatus::Issued, RequestStatus::Draft])
                // Surat siap unduh dulu, lalu yang perlu diperbaiki, terakhir draf.
                ->orderByRaw("CASE status WHEN 'issued' THEN 0 WHEN 'revision_required' THEN 1 ELSE 2 END")
                ->latest('updated_at')->take(5)->get(),
            'active' => (clone $requests)->with('letterType')
                ->status(RequestStatus::inProgress())
                ->latest('submitted_at')->take(5)->get(),
            'activity' => ApprovalHistory::query()
                ->whereIn('letter_request_id', (clone $requests)->select('id'))
                ->with(['letterRequest.letterType', 'actor'])
                ->latest('created_at')->latest('id')->take(8)->get(),
        ]);
    }
}
