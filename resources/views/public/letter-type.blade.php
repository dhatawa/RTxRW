<x-layouts.public :title="$type->name" :description="$type->description">
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 md:py-16">
        <a href="{{ route('letter-types') }}" class="inline-flex items-center gap-1 text-sm font-medium"><x-svg-icon name="chevron-left" class="size-4" />Semua jenis surat</a>
        <h1 class="mt-4 text-3xl font-bold sm:text-4xl">{{ $type->name }}</h1>
        <p class="mt-4 text-lg text-ink-muted">{{ $type->description }}</p>

        <x-summary-list class="mt-8 border-y border-line" :rows="[
            ['Estimasi proses', $type->processing_days.' hari kerja'],
            ['Ditandatangani oleh', $type->signatory->label()],
            ['Alur persetujuan', 'Ketua RT, lalu Ketua RW'],
        ]" />

        <h2 class="mt-10 text-xl font-semibold">Dokumen yang perlu disiapkan</h2>
        <ol class="mt-4 space-y-4">
            @foreach ($type->requirements as $req)
                <li class="flex gap-4">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full border border-line-strong text-sm font-semibold">{{ $loop->iteration }}</span>
                    <div>
                        <p class="font-semibold">{{ $req->name }} @unless ($req->is_required)<span class="font-normal text-ink-subtle">(opsional)</span>@endunless</p>
                        @if ($req->description)<p class="text-sm text-ink-muted">{{ $req->description }}</p>@endif
                    </div>
                </li>
            @endforeach
        </ol>

        @if ($type->fieldDefinitions())
            <h2 class="mt-10 text-xl font-semibold">Informasi yang akan ditanyakan</h2>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-ink-muted">
                @foreach ($type->fieldDefinitions() as $field)
                    <li>{{ $field['label'] }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mt-10 border-t border-line pt-8">
            @auth
                @if (auth()->user()->isWarga())
                    <x-button :href="route('warga.requests.form', $type)" icon-right="arrow-right">Ajukan surat ini</x-button>
                @endif
            @else
                <x-button :href="route('register')" icon-right="arrow-right">Daftar untuk mengajukan</x-button>
                <p class="mt-3 text-sm text-ink-muted">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
            @endauth
        </div>
    </div>
</x-layouts.public>
