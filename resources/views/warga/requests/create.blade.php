<x-layouts.app title="Ajukan surat">
    <x-page-header title="Surat apa yang Anda butuhkan?" description="Pilih jenis surat. Anda akan melihat dokumen yang perlu diunggah sebelum mengisi formulir." />

    @unless ($resident->canSubmitLetters())
        <x-alert tone="warning" title="Anda belum bisa mengajukan surat" class="mb-6">
            @if (! $resident->isProfileComplete())
                Data kependudukan Anda belum lengkap. <a href="{{ route('warga.profile.edit') }}">Lengkapi data</a>
            @elseif ($resident->verification_status === \App\Enums\VerificationStatus::Rejected)
                Ketua RT meminta perbaikan data: {{ $resident->verification_note }} <a href="{{ route('warga.profile.edit') }}">Perbaiki data</a>
            @else
                Data Anda sedang diverifikasi Ketua RT {{ $resident->rt->number }}. Daftar di bawah bisa Anda pelajari terlebih dahulu.
            @endif
        </x-alert>
    @endunless

    <ul class="panel divide-y divide-line">
        @foreach ($letterTypes as $type)
            <li>
                @if ($resident->canSubmitLetters())
                    <a href="{{ route('warga.requests.form', $type) }}" class="group flex items-center gap-4 px-5 py-4 text-ink no-underline hover:bg-canvas">
                @else
                    <div class="flex items-center gap-4 px-5 py-4">
                @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold group-hover:underline">{{ $type->name }}</p>
                        <p class="mt-0.5 line-clamp-2 text-sm text-ink-muted">{{ $type->description }}</p>
                        <p class="mt-1.5 text-xs text-ink-subtle">{{ $type->requirements_count }} dokumen · estimasi {{ $type->processing_days }} hari kerja</p>
                    </div>
                    @if ($resident->canSubmitLetters())
                        <x-svg-icon name="chevron-right" class="size-5 text-ink-subtle group-hover:text-primary" />
                    @endif
                @if ($resident->canSubmitLetters())
                    </a>
                @else
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.app>
