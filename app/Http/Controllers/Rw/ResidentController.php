<?php

namespace App\Http\Controllers\Rw;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Rt;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Data warga lintas RT, hanya-baca. Verifikasi tetap kewenangan Ketua RT masing-masing. */
class ResidentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $residents = Resident::query()->with(['rt', 'user'])
            ->when($request->query('rt'), fn ($q, $id) => $q->where('rt_id', $id))
            ->when(in_array($request->query('status'), ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $request->query('status')))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
                if (preg_match('/^\d{16}$/', $search)) {
                    $q->orWhere('nik_hash', Resident::hashNik($search));
                }
            }))
            ->orderBy('rt_id')->orderBy('full_name')
            ->paginate(20)->withQueryString();

        return view('rw.residents.index', [
            'residents' => $residents,
            'rts' => Rt::query()->orderBy('number')->get(['id', 'number']),
            'search' => $search,
        ]);
    }

    public function show(Resident $resident): View
    {
        return view('rw.residents.show', [
            'resident' => $resident->load(['user', 'rt', 'verifier']),
            'requests' => $resident->letterRequests()->with('letterType')->visibleToRw()->latest('submitted_at')->get(),
        ]);
    }
}
