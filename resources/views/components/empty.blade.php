@props(['title', 'icon' => 'inbox'])
<div {{ $attributes->class(['px-6 py-12 text-center']) }}>
    <x-svg-icon :name="$icon" class="mx-auto size-8 text-ink-subtle" />
    <p class="mt-3 font-semibold">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <div class="mx-auto mt-1 max-w-sm text-sm text-ink-muted">{{ $slot }}</div>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
