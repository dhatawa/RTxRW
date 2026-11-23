<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('shared.account', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
        ], ['phone.regex' => 'Gunakan nomor HP Indonesia yang valid.'], ['phone' => 'nomor HP']);

        $user->update(['email' => strtolower($data['email']), 'phone' => $data['phone']]);
        ActivityLogger::log('account.updated', "{$user->name} memperbarui kontak akun", $user);

        return back()->with('success', 'Data akun diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], ['current_password' => 'kata sandi saat ini', 'password' => 'kata sandi baru']);

        $request->user()->update(['password' => $data['password']]);
        ActivityLogger::log('account.password', "{$request->user()->name} mengganti kata sandi", $request->user());

        return back()->with('success', 'Kata sandi berhasil diganti.');
    }
}
