{{-- Deret angka ringkas dalam satu bidang bergaris pemisah — bukan kartu-kartu terpisah. --}}
@props(['items'])
@php
    $cols = match (count($items)) {
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        5 => 'lg:grid-cols-5',
        default => 'lg:grid-cols-4',
    };
@endphp
<dl {{ $attributes->class(['grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-line bg-line', $cols]) }}>
    @foreach ($items as $item)
        <div @class(['bg-surface px-4 py-4 sm:px-5', 'col-span-2 lg:col-span-1' => $loop->last && count($items) % 2 === 1])>
            <dt class="text-sm text-ink-muted">{{ $item['label'] }}</dt>
            <dd class="mt-1.5 flex items-center gap-2">
                @if (! empty($item['href']))
                    <a href="{{ $item['href'] }}" class="text-[1.75rem] font-bold leading-none tabular-nums text-ink no-underline hover:underline">{{ $item['value'] }}</a>
                @else
                    <span class="text-[1.75rem] font-bold leading-none tabular-nums">{{ $item['value'] }}</span>
                @endif
                @if (! empty($item['emphasis']) && $item['value'] > 0)
                    <span class="rounded-sm bg-warning-soft px-1.5 text-xs font-semibold leading-5 text-warning">Perlu tindakan</span>
                @endif
            </dd>
            @if (! empty($item['hint']))
                <dd class="mt-1.5 text-xs text-ink-subtle">{{ $item['hint'] }}</dd>
            @endif
        </div>
    @endforeach
</dl>
