@props(['tone' => 'info', 'title' => null])
@php
    [$classes, $icon] = match ($tone) {
        'success' => ['border-primary bg-primary-soft', 'circle-check'],
        'warning' => ['border-warning bg-warning-soft', 'triangle-alert'],
        'danger' => ['border-danger bg-danger-soft', 'circle-alert'],
        default => ['border-info bg-info-soft', 'info'],
    };
    $iconColor = match ($tone) {
        'success' => 'text-primary',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        default => 'text-info',
    };
@endphp
<div {{ $attributes->class(['flex gap-3 border-l-4 px-4 py-3', $classes]) }} role="{{ in_array($tone, ['danger', 'warning']) ? 'alert' : 'status' }}">
    <x-svg-icon :name="$icon" class="mt-0.5 size-5 {{ $iconColor }}" />
    <div class="min-w-0 flex-1 text-[0.9375rem]">
        @if ($title)
            <p class="font-semibold text-ink">{{ $title }}</p>
        @endif
        <div class="text-ink-muted [&_a]:font-semibold">{{ $slot }}</div>
    </div>
</div>
