<?php

namespace App\Http\Controllers\Rw;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Rt;
use App\Models\Rw;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RtController extends Controller
{
    public function index(): View
    {
        return view('rw.rts.index', [
            'rts' => Rt::query()->with('ketua')->orderBy('number')
                ->withCount([
                    'residents',
                    'residents as verified_count' => fn ($q) => $q->where('verification_status', 'verified'),
                    'letterRequests as active_requests_count' => fn ($q) => $q->whereIn('status', collect(RequestStatus::inProgress())->map->value),
                    'letterRequests as done_count' => fn ($q) => $q->whereIn('status', collect(RequestStatus::done())->map->value),
                ])->get(),
        ]);
    }

    public function create(): View
    {
        return view('rw.rts.form', ['rt' => new Rt(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rw = Rw::current();
        $data = $this->validated($request, $rw, null);

        $rt = $rw->rts()->create($data);
        ActivityLogger::log('rt.created', "Menambah RT {$rt->number}", $rt);

        return redirect()->route('rw.heads.edit', $rt)
            ->with('success', "RT {$rt->number} ditambahkan. Sekarang tetapkan Ketua RT-nya.");
    }

    public function edit(Rt $rt): View
    {
        return view('rw.rts.form', ['rt' => $rt]);
    }

    public function update(Request $request, Rt $rt): RedirectResponse
    {
        $data = $this->validated($request, Rw::current(), $rt);
        $before = $rt->only(array_keys($data));

        $rt->update($data);
        ActivityLogger::log('rt.updated', "Memperbarui data RT {$rt->number}", $rt, ['before' => $before, 'after' => $data]);

        return redirect()->route('rw.rts.index')->with('success', "Data RT {$rt->number} diperbarui.");
    }

    private function validated(Request $request, Rw $rw, ?Rt $rt): array
    {
        $request->merge(['number' => str_pad((string) (int) $request->input('number'), 2, '0', STR_PAD_LEFT)]);

        return $request->validate([
            'number' => ['required', 'regex:/^\d{2,3}$/', Rule::unique('rts', 'number')->where('rw_id', $rw->id)->ignore($rt?->id)],
            'area_description' => ['nullable', 'string', 'max:200'],
            'is_active' => ['boolean'],
        ], ['number.unique' => 'Nomor RT ini sudah ada.'], ['number' => 'nomor RT', 'area_description' => 'cakupan wilayah']) + ['is_active' => $request->boolean('is_active')];
    }
}
