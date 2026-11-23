<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\IssuedLetter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($code = $request->query('kode')) {
            return redirect()->route('verify.show', strtoupper(trim($code)));
        }

        return view('public.verify', ['letter' => null, 'code' => null]);
    }

    /** Hanya menampilkan data minimum agar keaslian bisa dicek tanpa membocorkan data pribadi. */
    public function show(string $code): View
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', $code));

        $letter = IssuedLetter::query()->with('letterRequest')->where('verification_code', $code)->first();

        return view('public.verify', ['letter' => $letter, 'code' => $code]);
    }
}
