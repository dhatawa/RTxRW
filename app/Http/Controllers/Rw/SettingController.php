<?php

namespace App\Http\Controllers\Rw;

use App\Http\Controllers\Controller;
use App\Models\Rw;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Identitas RW yang dipakai di kop surat dan halaman publik. */
class SettingController extends Controller
{
    public function edit(): View
    {
        return view('rw.settings', ['rw' => Rw::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rw = Rw::current();

        $data = $request->validate([
            'number' => ['required', 'regex:/^\d{2,3}$/'],
            'kelurahan' => ['required', 'string', 'max:80'],
            'kecamatan' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
            'province' => ['required', 'string', 'max:80'],
            'postal_code' => ['nullable', 'digits:5'],
            'secretariat_address' => ['nullable', 'string', 'max:200'],
        ], [], [
            'number' => 'nomor RW', 'city' => 'kota/kabupaten', 'province' => 'provinsi',
            'postal_code' => 'kode pos', 'secretariat_address' => 'alamat sekretariat',
        ]);

        $before = $rw->only(array_keys($data));
        $rw->update($data);
        ActivityLogger::log('settings.updated', 'Memperbarui identitas RW', $rw, ['before' => $before, 'after' => $data]);

        return back()->with('success', 'Identitas RW diperbarui. Perubahan berlaku untuk surat yang terbit berikutnya.');
    }
}
