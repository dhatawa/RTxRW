<?php

namespace App\Http\Controllers\Rw;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Rt;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RtHeadController extends Controller
{
    public function index(): View
    {
        return view('rw.heads.index', [
            'rts' => Rt::query()->with('ketua')->orderBy('number')->get(),
        ]);
    }

    public function edit(Rt $rt): View
    {
        return view('rw.heads.form', ['rt' => $rt->load('ketua')]);
    }

    /**
     * Tetapkan / perbarui akun Ketua RT. Akun ketua lama (jika diganti) dinonaktifkan,
     * bukan dihapus, agar riwayat keputusannya tetap utuh.
     */
    public function update(Request $request, Rt $rt): RedirectResponse
    {
        $current = $rt->ketua;
        $replacing = $request->input('mode') === 'replace' || ! $current;

        $data = $request->validate([
            'mode' => ['nullable', Rule::in(['update', 'replace'])],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($replacing ? null : $current?->id)],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'password' => [$replacing ? 'required' : 'nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ], ['phone.regex' => 'Gunakan nomor HP Indonesia yang valid.'], ['name' => 'nama', 'phone' => 'nomor HP', 'password' => 'kata sandi']);

        DB::transaction(function () use ($rt, $current, $replacing, $data) {
            if ($replacing) {
                if ($current) {
                    $current->update(['is_active' => false]);
                    $rt->update(['ketua_user_id' => null]);
                    ActivityLogger::log('rt_head.deactivated', "Ketua RT {$rt->number} lama ({$current->name}) dinonaktifkan", $current);
                }

                $user = User::create([
                    'name' => $data['name'],
                    'email' => strtolower($data['email']),
                    'phone' => $data['phone'] ?? null,
                    'password' => $data['password'],
                    'role' => Role::KetuaRt,
                ]);
                $rt->update(['ketua_user_id' => $user->id]);
                ActivityLogger::log('rt_head.assigned', "Menetapkan {$user->name} sebagai Ketua RT {$rt->number}", $rt);

                return;
            }

            $current->fill([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
            ]);
            if (! empty($data['password'])) {
                $current->password = $data['password'];
            }
            $current->save();

            ActivityLogger::log('rt_head.updated', "Memperbarui akun Ketua RT {$rt->number}", $current, [
                'password_reset' => ! empty($data['password']),
            ]);
        });

        return redirect()->route('rw.heads.index')->with('success', "Akun Ketua RT {$rt->number} tersimpan.");
    }

    public function toggle(Rt $rt): RedirectResponse
    {
        $ketua = $rt->ketua;
        abort_unless($ketua, 404);

        $ketua->update(['is_active' => ! $ketua->is_active]);
        ActivityLogger::log('rt_head.'.($ketua->is_active ? 'activated' : 'deactivated'),
            ($ketua->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." akun Ketua RT {$rt->number}", $ketua);

        return back()->with('success', "Akun {$ketua->name} ".($ketua->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }
}
