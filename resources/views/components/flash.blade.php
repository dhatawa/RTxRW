@if (session('success'))
    <x-alert tone="success" class="mb-6">{{ session('success') }}</x-alert>
@endif
@if (session('error'))
    <x-alert tone="danger" class="mb-6">{{ session('error') }}</x-alert>
@endif
@if ($errors->any() && ! session('error'))
    <x-alert tone="danger" title="Ada yang perlu diperbaiki" class="mb-6">
        <ul class="mt-1 list-disc space-y-0.5 pl-5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
