<?php

namespace App\Http\Controllers\Warga;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Rt;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('warga.profile', [
            'resident' => $request->user()->resident()->with('rt', 'verifier')->firstOrFail(),
            'rts' => Rt::query()->where('is_active', true)->orderBy('number')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $resident = $request->user()->resident()->firstOrFail();
        $locked = $resident->isVerified();

        $rules = [
            'kk_number' => ['nullable', 'digits:16'],
            'birth_place' => ['required', 'string', 'max:80'],
            'birth_date' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'religion' => ['required', Rule::in(Resident::RELIGIONS)],
            'marital_status' => ['required', Rule::in(Resident::MARITAL_STATUSES)],
            'occupation' => ['required', 'string', 'max:80'],
            'nationality' => ['required', Rule::in(['WNI', 'WNA'])],
            'address' => ['required', 'string', 'max:200'],
            'house_number' => ['nullable', 'string', 'max:10'],
        ];

        // Identitas inti dikunci setelah terverifikasi agar tidak bisa diubah diam-diam.
        if (! $locked) {
            $rules += [
                'full_name' => ['required', 'string', 'max:100'],
                'nik' => ['required', 'digits:16', function ($attr, $value, $fail) use ($resident) {
                    if (Resident::nikExists($value, $resident->id)) {
                        $fail('NIK ini sudah terdaftar pada akun lain.');
                    }
                }],
                'rt_id' => ['required', Rule::exists('rts', 'id')->where('is_active', true)->whereNull('deleted_at')],
            ];
        }

        $data = $request->validate($rules, [], [
            'kk_number' => 'nomor KK', 'birth_place' => 'tempat lahir', 'birth_date' => 'tanggal lahir',
            'gender' => 'jenis kelamin', 'religion' => 'agama', 'marital_status' => 'status perkawinan',
            'occupation' => 'pekerjaan', 'nationality' => 'kewarganegaraan', 'address' => 'alamat',
            'full_name' => 'nama lengkap', 'nik' => 'NIK', 'rt_id' => 'RT',
        ]);

        $resident->fill($data);

        // Warga yang datanya ditolak dan sudah memperbaiki, kembali menunggu verifikasi.
        if ($resident->verification_status === VerificationStatus::Rejected) {
            $resident->verification_status = VerificationStatus::Pending;
        }

        $resident->save();

        if (! $locked && isset($data['full_name'])) {
            $request->user()->update(['name' => $data['full_name']]);
        }

        ActivityLogger::log('resident.profile_updated', "{$resident->full_name} memperbarui data kependudukan", $resident, [
            'fields' => array_values(array_diff(array_keys($data), ['nik', 'kk_number'])),
        ]);

        return back()->with('success', 'Data kependudukan tersimpan.');
    }
}
