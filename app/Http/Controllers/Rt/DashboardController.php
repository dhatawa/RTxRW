<?php

namespace App\Http\Controllers\Rt;

use App\Enums\RequestStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ApprovalHistory;
use App\Models\LetterRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $rt = $request->user()->managedRt;
        $base = LetterRequest::query()->forRt($rt->id)->visibleToRw();

        $monthStart = now()->startOfMonth();

        return view('rt.dashboard', [
            'rt' => $rt,
            'stats' => [
                'pending' => (clone $base)->status(RequestStatus::PendingRt)->count(),
                'pending_residents' => $rt->residents()->where('verification_status', VerificationStatus::Pending)->count(),
                'residents' => $rt->residents()->count(),
                'month_total' => (clone $base)->where('submitted_at', '>=', $monthStart)->count(),
                'month_forwarded' => ApprovalHistory::query()
                    ->whereIn('letter_request_id', (clone $base)->select('id'))
                    ->where('action', 'rt_approved')->where('created_at', '>=', $monthStart)->count(),
                'month_done' => (clone $base)->status(RequestStatus::done())->where('issued_at', '>=', $monthStart)->count(),
            ],
            'queue' => (clone $base)->with(['letterType', 'resident'])
                ->status(RequestStatus::PendingRt)->oldest('submitted_at')->take(6)->get(),
            'newResidents' => $rt->residents()->with('user')
                ->where('verification_status', VerificationStatus::Pending)->latest()->take(5)->get(),
            'recent' => ApprovalHistory::query()
                ->whereIn('letter_request_id', (clone $base)->select('id'))
                ->with(['letterRequest.letterType', 'letterRequest.resident', 'actor'])
                ->latest('created_at')->latest('id')->take(8)->get(),
        ]);
    }
}
