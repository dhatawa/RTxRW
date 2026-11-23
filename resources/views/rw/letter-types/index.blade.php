<x-layouts.app title="Jenis surat">
    <x-page-header title="Jenis surat" description="Atur jenis surat yang bisa diajukan warga, persyaratan dokumennya, dan siapa yang menandatangani.">
        <x-slot:actions><x-button :href="route('rw.letter-types.create')" icon="plus">Tambah jenis surat</x-button></x-slot:actions>
    </x-page-header>

    <div class="panel overflow-x-auto">
        <table class="table-civic min-w-[46rem]">
            <thead><tr><th>Jenis surat</th><th>Penanda tangan</th><th class="text-right">Dokumen</th><th>Template</th><th class="text-right">Dipakai</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($letterTypes as $type)
                    <tr>
                        <td><span class="font-semibold">{{ $type->name }}</span><span class="block font-mono text-xs text-ink-subtle">{{ $type->code }}</span></td>
                        <td class="text-sm">{{ $type->signatory->label() }}</td>
                        <td class="text-right tabular-nums">{{ $type->requirements_count }}</td>
                        <td class="text-sm">
                            @if ($type->activeTemplate)
                                <a href="{{ route('rw.templates.edit', $type) }}">Versi {{ $type->activeTemplate->version }}</a>
                            @else
                                <a href="{{ route('rw.templates.edit', $type) }}" class="font-semibold text-warning">Belum ada</a>
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $type->letter_requests_count }}</td>
                        <td><span @class(['text-sm font-semibold', 'text-primary' => $type->is_active, 'text-ink-subtle' => ! $type->is_active])>{{ $type->is_active ? 'Dilayani' : 'Nonaktif' }}</span></td>
                        <td class="whitespace-nowrap text-right">
                            <a href="{{ route('rw.letter-types.edit', $type) }}" class="font-semibold">Ubah</a>
                            <form method="POST" action="{{ route('rw.letter-types.toggle', $type) }}" class="ml-3 inline">
                                @csrf
                                <button class="cursor-pointer font-semibold text-ink-muted underline underline-offset-[3px] hover:text-ink">{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-sm text-ink-muted">Jenis surat yang dinonaktifkan tidak bisa diajukan lagi, tetapi pengajuan yang sedang berjalan tetap diproses.</p>
</x-layouts.app>
