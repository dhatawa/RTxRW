<x-layouts.auth title="Masuk">
    <h1 class="text-2xl font-bold sm:text-[1.875rem]">Masuk ke RTxRW</h1>
    <p class="mt-2 text-ink-muted">Untuk warga, Ketua RT, dan Ketua RW.</p>

    @if ($errors->any())
        <x-alert tone="danger" class="mt-6">{{ $errors->first() }}</x-alert>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5" novalidate>
        @csrf
        <x-field name="email" label="Email" type="email" required autocomplete="email" autofocus inputmode="email" />
        <x-field name="password" label="Kata sandi" type="password" required autocomplete="current-password" />

        <label class="flex items-center gap-3 text-[0.9375rem]">
            <input type="checkbox" name="remember" value="1" class="check" @checked(old('remember'))>
            Tetap masuk di perangkat ini
        </label>

        <x-button class="w-full sm:w-auto">Masuk</x-button>
    </form>

    <div class="mt-10 border-t border-line pt-6 text-[0.9375rem]">
        <p class="text-ink-muted">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold">Daftar sebagai warga</a></p>
        <p class="mt-2 text-sm text-ink-subtle">Lupa kata sandi? Hubungi Ketua RT Anda untuk mengatur ulang akun.</p>
    </div>

    @if (app()->environment('local'))
        <details class="mt-8 rounded-md border border-dashed border-line-strong bg-surface px-4 py-3 text-sm">
            <summary class="cursor-pointer font-semibold">Akun demo (hanya tampil di mode lokal)</summary>
            <table class="mt-3 w-full text-left">
                <tbody class="divide-y divide-line">
                    @foreach ([['Ketua RW', 'rw@rtxrw.test'], ['Ketua RT 01', 'rt01@rtxrw.test'], ['Ketua RT 02', 'rt02@rtxrw.test'], ['Warga RT 01', 'warga@rtxrw.test']] as [$role, $email])
                        <tr><td class="py-1.5 pr-3 text-ink-muted">{{ $role }}</td><td class="py-1.5 font-mono text-xs">{{ $email }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-2 text-ink-muted">Kata sandi semua akun: <span class="font-mono text-xs">password123</span></p>
        </details>
    @endif
</x-layouts.auth>
