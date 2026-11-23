<x-layouts.app title="Akun">
    <x-page-header title="Pengaturan akun" :description="'Masuk sebagai '.$user->role->label().'.'" />

    <div class="max-w-2xl space-y-8">
        <form method="POST" action="{{ route('account.update') }}" class="panel" novalidate>
            @csrf @method('PUT')
            <div class="panel-header"><h2 class="panel-title">Kontak</h2></div>
            <div class="space-y-5 px-5 py-5 sm:px-6">
                <x-field name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
                <x-field name="phone" label="Nomor HP" type="tel" :value="$user->phone" optional inputmode="tel" width="md" />
            </div>
            <div class="border-t border-line bg-canvas px-5 py-4 sm:px-6"><x-button>Simpan</x-button></div>
        </form>

        <form method="POST" action="{{ route('account.password') }}" class="panel" novalidate>
            @csrf @method('PUT')
            <div class="panel-header"><h2 class="panel-title">Ganti kata sandi</h2></div>
            <div class="space-y-5 px-5 py-5 sm:px-6">
                <x-field name="current_password" label="Kata sandi saat ini" type="password" required autocomplete="current-password" />
                <x-field name="password" label="Kata sandi baru" hint="Minimal 8 karakter, berisi huruf dan angka." type="password" required autocomplete="new-password" />
                <x-field name="password_confirmation" label="Ulangi kata sandi baru" type="password" required autocomplete="new-password" />
            </div>
            <div class="border-t border-line bg-canvas px-5 py-4 sm:px-6"><x-button>Ganti kata sandi</x-button></div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
            @csrf
            <x-button variant="secondary" icon="log-out" class="w-full">Keluar</x-button>
        </form>
    </div>
</x-layouts.app>
