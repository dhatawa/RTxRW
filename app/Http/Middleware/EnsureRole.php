<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $allowed = array_map(fn ($r) => Role::from($r), $roles);

        abort_unless(in_array($user->role, $allowed, true), 403, 'Halaman ini tidak tersedia untuk peran Anda.');

        // Ketua RT wajib terhubung dengan satu RT agar scope data jelas.
        if ($user->role === Role::KetuaRt && ! $user->managedRt) {
            abort(403, 'Akun Ketua RT Anda belum ditautkan ke RT mana pun. Hubungi Ketua RW.');
        }

        return $next($request);
    }
}
