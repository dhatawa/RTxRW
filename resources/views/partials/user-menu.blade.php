@php use App\Support\Navigation; @endphp
<div class="border-t border-line p-3">
    <a href="{{ route('account.edit') }}" class="nav-link" @if (request()->routeIs('account.*')) aria-current="page" @endif>
        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-bold text-primary">{{ $user->initials() }}</span>
        <span class="min-w-0 flex-1 leading-tight">
            <span class="block truncate text-sm font-semibold text-ink">{{ $user->name }}</span>
            <span class="block truncate text-xs text-ink-subtle">{{ Navigation::scopeLabel($user) }}</span>
        </span>
    </a>
    <form method="POST" action="{{ route('logout') }}" class="mt-1">
        @csrf
        <button type="submit" class="nav-link w-full cursor-pointer">
            <x-svg-icon name="log-out" class="size-[1.125rem]" />
            Keluar
        </button>
    </form>
</div>
