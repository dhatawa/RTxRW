@props([
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'icon' => null,
    'iconRight' => null,
    'type' => 'submit',
])
@php
    $classes = 'btn btn-'.$variant.($size === 'sm' ? ' btn-sm' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-svg-icon :name="$icon" class="size-[1.125rem]" />@endif
        {{ $slot }}
        @if ($iconRight)<x-svg-icon :name="$iconRight" class="size-[1.125rem]" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-svg-icon :name="$icon" class="size-[1.125rem]" />@endif
        {{ $slot }}
        @if ($iconRight)<x-svg-icon :name="$iconRight" class="size-[1.125rem]" />@endif
    </button>
@endif
