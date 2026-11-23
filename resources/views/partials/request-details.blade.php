{{-- Isi pengajuan (keperluan, isian tambahan, dokumen). Dipakai di halaman warga, RT, dan RW. --}}
@php $type = $letterRequest->letterType; @endphp
<section class="panel">
    <div class="panel-header"><h2 class="panel-title">Keterangan surat</h2></div>
    <div class="px-5 sm:px-6">
        <x-summary-list :rows="array_merge(
            [['Keperluan', $letterRequest->purpose]],
            collect($type->fieldDefinitions())->map(function ($f) use ($letterRequest) {
                $v = $letterRequest->fieldValue($f['key']);
                if ($f['type'] === 'date' && filled($v)) { $v = \Illuminate\Support\Carbon::parse($v)->translatedFormat('j F Y'); }
                if ($f['type'] === 'number' && is_numeric($v)) { $v = number_format((float) $v, 0, ',', '.'); }
                return [$f['label'], $v];
            })->all()
        )" />
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Dokumen persyaratan</h2>
        <span class="text-sm text-ink-muted">{{ $letterRequest->documents->count() }} file</span>
    </div>
    <ul class="divide-y divide-line">
        @foreach ($type->requirements as $req)
            @php $doc = $letterRequest->documents->firstWhere('letter_requirement_id', $req->id); @endphp
            <li class="flex items-start gap-3 px-5 py-3.5 sm:px-6">
                <x-svg-icon :name="$doc ? 'file-check' : ($req->is_required ? 'circle-alert' : 'file-text')" @class(['mt-0.5 size-5', 'text-primary' => $doc, 'text-warning' => ! $doc && $req->is_required, 'text-ink-subtle' => ! $doc && ! $req->is_required]) />
                <div class="min-w-0 flex-1">
                    <p class="text-[0.9375rem] font-medium">{{ $req->name }} @unless ($req->is_required)<span class="font-normal text-ink-subtle">(opsional)</span>@endunless</p>
                    @if ($doc)
                        <p class="text-sm text-ink-muted"><span class="break-all">{{ $doc->original_name }}</span> · {{ $doc->humanSize() }}</p>
                    @else
                        <p class="text-sm text-ink-subtle">Belum diunggah</p>
                    @endif
                </div>
                @if ($doc)
                    <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm shrink-0">
                        <x-svg-icon name="eye" class="size-4" />Lihat
                    </a>
                @endif
            </li>
        @endforeach
        @foreach ($letterRequest->documents->whereNull('letter_requirement_id') as $doc)
            <li class="flex items-start gap-3 px-5 py-3.5 sm:px-6">
                <x-svg-icon name="file-text" class="mt-0.5 size-5 text-ink-subtle" />
                <div class="min-w-0 flex-1"><p class="text-[0.9375rem]">{{ $doc->original_name }}</p><p class="text-sm text-ink-subtle">Persyaratan sudah dihapus dari jenis surat ini</p></div>
                <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm shrink-0">Lihat</a>
            </li>
        @endforeach
    </ul>
</section>
