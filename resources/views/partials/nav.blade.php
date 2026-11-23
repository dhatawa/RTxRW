@php use App\Support\Navigation; @endphp
<nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Menu">
    @foreach ($nav as $group)
        <div @class(['mt-6' => ! $loop->first])>
            @isset($group['heading'])
                <p class="mb-1.5 px-3 text-2xs font-semibold uppercase tracking-[0.08em] text-ink-subtle">{{ $group['heading'] }}</p>
            @endisset
            <ul class="space-y-0.5">
                @foreach ($group['items'] as $item)
                    @php $active = Navigation::isActive($item['active']); @endphp
                    <li>
                        <a href="{{ route($item['route']) }}" class="nav-link" @if ($active) aria-current="page" @endif>
                            <x-svg-icon :name="$item['icon']" class="size-[1.125rem]" />
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if (! empty($item['badge']))
                                <span class="count" aria-label="{{ $item['badge'] }} perlu tindakan">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
