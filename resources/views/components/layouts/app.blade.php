@props(['title' => null, 'description' => null])
@php
    use App\Support\Navigation;
    $user = auth()->user();
    $nav = Navigation::for($user);
    $unread = $user->unreadNotifications()->count();
    $isWarga = $user->isWarga();
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    @include('partials.head')
</head>
<body class="min-h-full" x-data="{ drawer: false }" @keydown.escape.window="drawer = false">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-focus focus:px-4 focus:py-2 focus:text-ink">Langsung ke konten</a>

    {{-- Sidebar (desktop) --}}
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-line bg-surface lg:flex">
        <div class="flex h-16 items-center border-b border-line px-5">
            <a href="{{ route('dashboard') }}" class="no-underline" aria-label="RTxRW, ke beranda"><x-wordmark /></a>
        </div>
        @include('partials.nav', ['nav' => $nav])
        @include('partials.user-menu', ['user' => $user])
    </aside>

    {{-- Drawer (mobile) --}}
    <div x-cloak x-show="drawer" class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi">
        <div x-show="drawer" x-transition.opacity class="absolute inset-0 bg-ink/40" @click="drawer = false"></div>
        <aside x-show="drawer" x-trap.noscroll="drawer"
               x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="absolute inset-y-0 left-0 flex w-[82%] max-w-xs flex-col bg-surface">
            <div class="flex h-14 items-center justify-between border-b border-line px-4">
                <x-wordmark />
                <button type="button" class="btn btn-ghost btn-sm text-ink no-underline" @click="drawer = false">
                    <x-svg-icon name="x" /><span class="sr-only">Tutup menu</span>
                </button>
            </div>
            @include('partials.nav', ['nav' => $nav])
            @include('partials.user-menu', ['user' => $user])
        </aside>
    </div>

    <div class="lg:pl-64">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 border-b border-line bg-surface/95 backdrop-blur-sm">
            <div class="flex h-14 items-center gap-3 px-4 sm:px-6 lg:h-16 lg:px-8">
                <button type="button" class="-ml-2 inline-flex size-10 items-center justify-center rounded-md text-ink hover:bg-canvas lg:hidden" @click="drawer = true" aria-label="Buka menu">
                    <x-svg-icon name="menu" class="size-6" />
                </button>
                <a href="{{ route('dashboard') }}" class="no-underline lg:hidden"><x-wordmark size="sm" /></a>

                <p class="hidden text-sm text-ink-muted lg:block">{{ Navigation::scopeLabel($user) }}</p>

                <div class="ml-auto flex items-center gap-1">
                    <a href="{{ route('notifications.index') }}" class="relative inline-flex size-10 items-center justify-center rounded-md text-ink no-underline hover:bg-canvas"
                       @if (request()->routeIs('notifications.*')) aria-current="page" @endif>
                        <x-svg-icon name="bell" />
                        <span class="sr-only">Notifikasi{{ $unread ? ", {$unread} belum dibaca" : '' }}</span>
                        @if ($unread)
                            <span class="absolute right-1 top-1 inline-flex min-w-[1.125rem] items-center justify-center rounded-full bg-danger px-1 text-2xs font-bold leading-[1.125rem] text-white" aria-hidden="true">{{ $unread > 9 ? '9+' : $unread }}</span>
                        @endif
                    </a>
                    <a href="{{ route('account.edit') }}" class="hidden items-center gap-2 rounded-md py-1 pl-1 pr-2 text-sm font-medium text-ink no-underline hover:bg-canvas sm:inline-flex">
                        <span class="inline-flex size-8 items-center justify-center rounded-full bg-primary-soft text-xs font-bold text-primary">{{ $user->initials() }}</span>
                        <span class="max-w-[10rem] truncate">{{ $user->name }}</span>
                    </a>
                </div>
            </div>
        </header>

        <main id="konten" @class(['mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8', 'pb-24 lg:pb-8' => $isWarga])>
            <x-flash />
            {{ $slot }}
        </main>
    </div>

    {{-- Navigasi bawah khusus warga di ponsel: aksi paling sering dalam jangkauan ibu jari --}}
    @if ($isWarga)
        <nav class="fixed inset-x-0 bottom-0 z-20 border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Navigasi utama">
            <div class="grid grid-cols-4">
                @foreach ([
                    ['Beranda', 'warga.dashboard', 'house', 'warga.dashboard'],
                    ['Ajukan', 'warga.requests.create', 'file-plus', 'warga.requests.create|warga.requests.form'],
                    ['Pengajuan', 'warga.requests.index', 'files', 'warga.requests.index|warga.requests.show|warga.requests.edit'],
                    ['Profil', 'warga.profile.edit', 'id-card', 'warga.profile.*'],
                ] as [$label, $route, $icon, $active])
                    @php $isActive = Navigation::isActive($active); @endphp
                    <a href="{{ route($route) }}" @class([
                        'flex flex-col items-center gap-0.5 py-2 text-xs font-medium no-underline',
                        'text-primary' => $isActive,
                        'text-ink-muted' => ! $isActive,
                    ]) @if ($isActive) aria-current="page" @endif>
                        <x-svg-icon :name="$icon" class="size-[1.375rem]" />
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endif
</body>
</html>
