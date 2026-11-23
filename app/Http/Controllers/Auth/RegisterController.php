<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Notifications\NewResidentRegistered;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'rts' => Rt::query()->where('is_active', true)->orderBy('number')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'digits:16', function ($attr, $value, $fail) {
                if (Resident::nikExists($value)) {
                    $fail('NIK ini sudah terdaftar. Jika Anda merasa belum pernah mendaftar, hubungi Ketua RT.');
                }
            }],
            'rt_id' => ['required', Rule::exists('rts', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'consent' => ['accepted'],
        ], [
            'phone.regex' => 'Gunakan nomor HP Indonesia yang valid, contoh 081234567890.',
            'consent.accepted' => 'Anda perlu menyetujui penggunaan data untuk keperluan layanan surat.',
        ], [
            'name' => 'nama lengkap', 'nik' => 'NIK', 'rt_id' => 'RT', 'phone' => 'nomor HP', 'password' => 'kata sandi',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => Role::Warga,
            ]);

            $resident = Resident::create([
                'user_id' => $user->id,
                'rt_id' => $data['rt_id'],
                'nik' => $data['nik'],
                'full_name' => $data['name'],
            ]);

            ActivityLogger::log('resident.registered', "Warga baru mendaftar: {$user->name}", $resident);

            DB::afterCommit(fn () => $resident->rt->ketua?->notify(new NewResidentRegistered($resident)));

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('warga.profile.edit')
            ->with('success', 'Akun berhasil dibuat. Lengkapi data kependudukan agar Ketua RT dapat memverifikasi Anda.');
    }
}
