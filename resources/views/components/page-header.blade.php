@props(['title', 'eyebrow' => null, 'description' => null, 'back' => null, 'backLabel' => 'Kembali'])
<header {{ $attributes->class(['mb-6 sm:mb-8']) }}>
    @if ($back)
        <a href="{{ $back }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium">
            <x-svg-icon name="chevron-left" class="size-4" />{{ $backLabel }}
        </a>
    @endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            @if ($eyebrow)
                <p class="eyebrow mb-1.5">{{ $eyebrow }}</p>
            @endif
            <h1 class="text-2xl font-bold sm:text-[1.875rem]">{{ $title }}</h1>
            @if ($description)
                <p class="mt-2 max-w-2xl text-ink-muted">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex shrink-0 flex-wrap gap-2">{{ $actions }}</div>
        @endisset
    </div>
</header>
