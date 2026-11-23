<?php

namespace App\Http\Controllers\Rt;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /** Riwayat keputusan yang diambil di tingkat RT ini. */
    public function index(Request $request): View
    {
        $rt = $request->user()->managedRt;

        $histories = ApprovalHistory::query()
            ->where('actor_role', Role::KetuaRt->value)
            ->whereHas('letterRequest', fn ($q) => $q->where('rt_id', $rt->id))
            ->when($request->query('aksi'), fn ($q, $a) => $q->where('action', $a))
            ->with(['letterRequest.letterType', 'letterRequest.resident', 'actor'])
            ->latest('created_at')->latest('id')
            ->paginate(20)->withQueryString();

        return view('rt.history', ['histories' => $histories, 'rt' => $rt, 'action' => $request->query('aksi')]);
    }
}
