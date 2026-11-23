{{-- Label status: teks + titik warna. Warna mendukung, teks tetap jadi informasi utama. --}}
@props(['status', 'size' => 'md'])
@php
    $tone = $status->tone();
    $classes = match ($tone) {
        'success' => 'bg-primary-soft text-primary border-primary/20',
        'warning' => 'bg-warning-soft text-warning border-warning-line',
        'danger' => 'bg-danger-soft text-danger border-danger-line',
        'info' => 'bg-info-soft text-info border-info-line',
        default => 'bg-canvas text-ink-muted border-line-strong',
    };
    $dot = match ($tone) {
        'success' => 'bg-primary',
        'warning' => 'bg-warning',
        'danger' => 'bg-danger',
        'info' => 'bg-info',
        default => 'bg-ink-subtle',
    };
@endphp
<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 whitespace-nowrap rounded-sm border font-semibold',
    $classes,
    $size === 'sm' ? 'px-1.5 py-0 text-xs leading-5' : 'px-2 py-0.5 text-[0.8125rem]',
]) }}>
    <span class="size-1.5 rounded-full {{ $dot }}" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
