{{-- Tab berbasis tautan (filter lewat query string), bisa digeser di layar kecil. --}}
@props(['items', 'current', 'param' => 'tab', 'keep' => []])
<nav {{ $attributes->class(['-mx-4 mb-5 overflow-x-auto border-b border-line px-4 sm:mx-0 sm:px-0']) }} aria-label="Filter">
    <div class="flex gap-6">
        @foreach ($items as $key => $item)
            @php
                $query = array_merge(request()->only($keep), [$param => $key]);
                $label = is_array($item) ? $item['label'] : $item;
                $count = is_array($item) ? ($item['count'] ?? null) : null;
            @endphp
            <a href="{{ request()->url().'?'.http_build_query($query) }}" class="tab" @if ((string) $current === (string) $key) aria-current="page" @endif>
                {{ $label }}
                @if ($count)<span class="count">{{ $count }}</span>@endif
            </a>
        @endforeach
    </div>
</nav>
