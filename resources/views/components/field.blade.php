{{--
    Field form standar: label → hint → pesan error → kontrol.
    Pola mengikuti GOV.UK: error ditampilkan di atas input, input diberi aria-invalid.
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'optional' => false,
    'options' => null,
    'placeholder' => null,
    'rows' => 4,
    'width' => null,
])
@php
    $dotName = str_replace(['[', ']'], ['.', ''], $name);
    $id = 'f-'.str_replace(['.', '[', ']'], '-', $name);
    $error = $errors->first($dotName);
    $current = old($dotName, $value);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $widthClass = match ($width) {
        'xs' => 'sm:max-w-[8rem]',
        'sm' => 'sm:max-w-[14rem]',
        'md' => 'sm:max-w-[22rem]',
        default => '',
    };
@endphp
<div {{ $attributes->class(['field-group-error' => $error]) }}>
    <label for="{{ $id }}" class="field-label">
        {{ $label }}
        @if ($optional)<span class="font-normal text-ink-muted">(opsional)</span>@endif
    </label>
    @if ($hint)
        <span id="{{ $id }}-hint" class="field-hint">{{ $hint }}</span>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="field-error"><x-svg-icon name="circle-alert" class="mt-0.5 size-4" /><span>{{ $error }}</span></p>
    @endif

    @if ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" class="input {{ $widthClass }}" @required($required)
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif>
            <option value="">{{ $placeholder ?? 'Pilih…' }}</option>
            @foreach ($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" class="input {{ $widthClass }}" @required($required)
            placeholder="{{ $placeholder }}"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif>{{ $current }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : $current }}"
            class="input {{ $widthClass }}" @required($required) placeholder="{{ $placeholder }}"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif
            {{ $attributes->only(['autocomplete', 'inputmode', 'maxlength', 'min', 'max', 'step', 'pattern', 'readonly', 'autofocus']) }} />
    @endif
</div>
