@props(['status'])
@php
    $classes = match ($status->tone()) {
        'success' => 'text-primary',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        default => 'text-ink-muted',
    };
    $icon = match ($status->tone()) {
        'success' => 'shield-check',
        'warning' => 'clock',
        'danger' => 'circle-x',
        default => 'info',
    };
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5 text-sm font-semibold', $classes]) }}>
    <x-svg-icon :name="$icon" class="size-4" />{{ $status->label() }}
</span>
