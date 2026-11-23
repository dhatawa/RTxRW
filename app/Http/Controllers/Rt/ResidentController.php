<?php

namespace App\Http\Controllers\Rt;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Notifications\ResidentVerificationUpdated;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResidentController extends Controller
{
    public function index(Request $request): View
    {
        $rt = $request->user()->managedRt;
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));

        $residents = $rt->residents()->with('user')
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                    // NIK terenkripsi: pencarian NIK hanya bisa persis sama (lewat hash).
                    if (preg_match('/^\d{16}$/', $search)) {
                        $q->orWhere('nik_hash', Resident::hashNik($search));
                    }
                });
            })
            ->orderByRaw("CASE verification_status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->orderBy('full_name')
            ->paginate(15)->withQueryString();

        return view('rt.residents.index', [
            'rt' => $rt,
            'residents' => $residents,
            'status' => $status,
            'search' => $search,
            'counts' => $rt->residents()->selectRaw('verification_status, count(*) as total')->groupBy('verification_status')->pluck('total', 'verification_status'),
        ]);
    }

    public function show(Resident $resident): View
    {
        Gate::authorize('view', $resident);

        return view('rt.residents.show', [
            'resident' => $resident->load(['user', 'rt', 'verifier']),
            'requests' => $resident->letterRequests()->with('letterType')->visibleToRw()->latest('submitted_at')->take(10)->get(),
        ]);
    }

    public function verify(Request $request, Resident $resident): RedirectResponse
    {
        Gate::authorize('verify', $resident);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['verify', 'reject'])],
            'note' => ['nullable', 'required_if:decision,reject', 'string', 'max:500'],
        ], ['note.required_if' => 'Tuliskan data apa yang perlu diperbaiki warga.']);

        if ($data['decision'] === 'verify' && ! $resident->isProfileComplete()) {
            return back()->with('error', 'Data warga belum lengkap. Minta warga melengkapi profil terlebih dahulu, atau tolak dengan catatan.');
        }

        $resident->forceFill([
            'verification_status' => $data['decision'] === 'verify' ? VerificationStatus::Verified : VerificationStatus::Rejected,
            'verification_note' => $data['note'] ?? null,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ])->save();

        ActivityLogger::log(
            'resident.'.($data['decision'] === 'verify' ? 'verified' : 'rejected'),
            ($data['decision'] === 'verify' ? 'Memverifikasi' : 'Menolak data').' warga '.$resident->full_name,
            $resident,
            ['note' => $data['note'] ?? null],
        );

        $resident->user?->notify(new ResidentVerificationUpdated($resident));

        return redirect()->route('rt.residents.show', $resident)->with('success', $data['decision'] === 'verify'
            ? "{$resident->full_name} terverifikasi dan kini dapat mengajukan surat."
            : 'Catatan perbaikan dikirim ke warga.');
    }
}
