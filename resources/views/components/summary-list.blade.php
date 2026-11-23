{{-- Daftar label–nilai (pola "summary list" GOV.UK). $rows: [[label, value], ...] --}}
@props(['rows'])
<dl {{ $attributes->class(['divide-y divide-line']) }}>
    @foreach ($rows as $row)
        @continue($row === null)
        <div class="grid gap-0.5 py-3 sm:grid-cols-[minmax(10rem,14rem)_1fr] sm:gap-6">
            <dt class="text-sm font-medium text-ink-muted sm:text-[0.9375rem]">{{ $row[0] }}</dt>
            <dd class="min-w-0 break-words text-[0.9375rem]">
                @if (blank($row[1]))
                    <span class="text-ink-subtle">—</span>
                @elseif ($row[1] instanceof \Illuminate\Support\HtmlString)
                    {{ $row[1] }}
                @else
                    {{ $row[1] }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
