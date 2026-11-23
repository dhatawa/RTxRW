<x-layouts.public title="Jenis surat" description="Daftar jenis surat yang dilayani beserta persyaratan dokumennya.">
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 md:py-16">
        <p class="eyebrow">Layanan</p>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Jenis surat dan persyaratannya</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-muted">Siapkan dokumen dalam bentuk foto atau PDF, maksimal 2 MB per file. Dokumen bertanda <em>opsional</em> boleh dilewati.</p>

        <div class="mt-10 divide-y divide-line border-y border-line">
            @foreach ($letterTypes as $type)
                <section id="{{ $type->slug }}" class="py-7" x-data="{ open: false }">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold"><a href="{{ route('letter-types.show', $type) }}" class="text-ink no-underline hover:underline">{{ $type->name }}</a></h2>
                            <p class="mt-1 text-sm text-ink-subtle">Estimasi {{ $type->processing_days }} hari kerja · Ditandatangani {{ $type->signatory->label() }}</p>
                        </div>
                        <span class="font-mono text-xs text-ink-subtle">{{ $type->code }}</span>
                    </div>
                    <p class="mt-3 max-w-2xl text-ink-muted">{{ $type->description }}</p>
                    <h3 class="mt-4 text-sm font-semibold">Dokumen yang perlu disiapkan</h3>
                    <ul class="mt-2 space-y-1.5 text-[0.9375rem]">
                        @foreach ($type->requirements as $req)
                            <li class="flex gap-2">
                                <x-svg-icon name="paperclip" class="mt-1 size-4 text-ink-subtle" />
                                <span>{{ $req->name }} @unless ($req->is_required)<span class="text-ink-subtle">(opsional)</span>@endunless</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-10">
            @auth
                <x-button :href="route('dashboard')" icon-right="arrow-right">Ke dasbor</x-button>
            @else
                <x-button :href="route('register')" icon-right="arrow-right">Daftar untuk mengajukan</x-button>
            @endauth
        </div>
    </div>
</x-layouts.public>
