<x-layouts.app title="Notifikasi">
    <x-page-header title="Notifikasi">
        <x-slot:actions>
            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-button variant="secondary" icon="check">Tandai semua dibaca</x-button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="panel">
        @forelse ($notifications as $n)
            <a href="{{ route('notifications.open', $n->id) }}" @class([
                'flex gap-4 border-b border-line px-5 py-4 text-ink no-underline last:border-b-0 hover:bg-canvas',
                'bg-primary-soft/40' => ! $n->read_at,
            ])>
                <span @class(['mt-2 size-2 shrink-0 rounded-full', 'bg-primary' => ! $n->read_at, 'bg-transparent' => $n->read_at]) aria-hidden="true"></span>
                <div class="min-w-0 flex-1">
                    <p @class(['font-semibold' => ! $n->read_at, 'font-medium' => $n->read_at])>
                        {{ $n->data['title'] ?? 'Pemberitahuan' }}
                        @unless ($n->read_at)<span class="sr-only">(belum dibaca)</span>@endunless
                    </p>
                    <p class="mt-0.5 text-[0.9375rem] text-ink-muted">{{ $n->data['message'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-ink-subtle"><time datetime="{{ $n->created_at->toIso8601String() }}">{{ $n->created_at->diffForHumans() }}</time></p>
                </div>
                <x-svg-icon name="chevron-right" class="mt-1 size-5 text-ink-subtle" />
            </a>
        @empty
            <x-empty title="Belum ada notifikasi" icon="bell">Perubahan status pengajuan akan diberitahukan di sini.</x-empty>
        @endforelse
    </div>
    <div class="mt-6">{{ $notifications->links() }}</div>
</x-layouts.app>
