@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    @isset($description)<meta name="description" content="{{ $description }}">@endisset
</head>
<body class="flex min-h-screen flex-col bg-surface" x-data="{ menu: false }">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-focus focus:px-4 focus:py-2 focus:text-ink">Langsung ke konten</a>

    <header class="border-b border-line bg-surface">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-8 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="no-underline" aria-label="RTxRW, beranda"><x-wordmark /></a>

            <nav class="hidden items-center gap-6 md:flex" aria-label="Utama">
                @foreach ([['Layanan', 'services'], ['Jenis surat', 'letter-types'], ['Cek keaslian surat', 'verify.index'], ['Tentang', 'about']] as [$label, $route])
                    <a href="{{ route($route) }}" @class([
                        'text-[0.9375rem] font-medium no-underline hover:underline',
                        'text-ink' => request()->routeIs($route.'*'),
                        'text-ink-muted' => ! request()->routeIs($route.'*'),
                    ]) @if (request()->routeIs($route.'*')) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto hidden items-center gap-2 md:flex">
                @auth
                    <x-button :href="route('dashboard')" icon-right="arrow-right">Ke dasbor</x-button>
                @else
                    <a href="{{ route('login') }}" class="px-3 text-[0.9375rem] font-semibold no-underline hover:underline">Masuk</a>
                    <x-button :href="route('register')">Daftar sebagai warga</x-button>
                @endauth
            </div>

            <button type="button" class="ml-auto inline-flex size-10 items-center justify-center rounded-md md:hidden" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-publik">
                <x-svg-icon name="menu" class="size-6" x-show="!menu" />
                <x-svg-icon name="x" class="size-6" x-show="menu" x-cloak />
                <span class="sr-only">Menu</span>
            </button>
        </div>
        <div id="menu-publik" x-show="menu" x-cloak class="border-t border-line md:hidden">
            <nav class="mx-auto flex max-w-6xl flex-col px-4 py-3" aria-label="Utama (ponsel)">
                @foreach ([['Layanan', 'services'], ['Jenis surat', 'letter-types'], ['Cek keaslian surat', 'verify.index'], ['Tentang', 'about']] as [$label, $route])
                    <a href="{{ route($route) }}" class="border-b border-line py-3 font-medium text-ink no-underline">{{ $label }}</a>
                @endforeach
                <div class="grid grid-cols-2 gap-2 pt-4">
                    @auth
                        <x-button :href="route('dashboard')" class="col-span-2">Ke dasbor</x-button>
                    @else
                        <x-button :href="route('login')" variant="secondary">Masuk</x-button>
                        <x-button :href="route('register')">Daftar</x-button>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main id="konten" class="flex-1">
        {{ $slot }}
    </main>

    @php $footerRw = \App\Models\Rw::query()->first(); @endphp
    <footer class="border-t border-line bg-canvas">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <x-wordmark />
                <p class="mt-3 max-w-sm text-sm text-ink-muted">Pelayanan surat pengantar dan keterangan warga secara daring.</p>
                @if ($footerRw)
                    <p class="mt-4 text-sm text-ink-muted">
                        <span class="font-semibold text-ink">Sekretariat RW {{ $footerRw->number }}</span><br>
                        {{ $footerRw->secretariat_address }}<br>
                        Kel. {{ $footerRw->kelurahan }}, Kec. {{ $footerRw->kecamatan }}, {{ $footerRw->city }}
                    </p>
                @endif
            </div>
            <div>
                <p class="text-sm font-semibold">Layanan</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('letter-types') }}">Jenis surat</a></li>
                    <li><a href="{{ route('services') }}">Alur dan waktu layanan</a></li>
                    <li><a href="{{ route('verify.index') }}">Cek keaslian surat</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold">Akun</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('login') }}">Masuk</a></li>
                    <li><a href="{{ route('register') }}">Daftar sebagai warga</a></li>
                    <li><a href="{{ route('about') }}">Tentang RTxRW</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-line">
            <p class="mx-auto max-w-6xl px-4 py-4 text-xs text-ink-subtle sm:px-6">© {{ now()->year }} RTxRW. Data warga hanya digunakan untuk keperluan layanan administrasi RT/RW.</p>
        </div>
    </footer>
</body>
</html>
