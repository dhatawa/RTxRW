<x-layouts.app title="Data RT">
    <x-page-header title="Data RT" description="RT di wilayah RW beserta jumlah warga dan pengajuannya.">
        <x-slot:actions><x-button :href="route('rw.rts.create')" icon="plus">Tambah RT</x-button></x-slot:actions>
    </x-page-header>

    <div class="panel overflow-x-auto">
        <table class="table-civic min-w-[40rem]">
            <thead>
                <tr><th>RT</th><th>Cakupan wilayah</th><th>Ketua RT</th><th class="text-right">Warga</th><th class="text-right">Diproses</th><th class="text-right">Selesai</th><th><span class="sr-only">Aksi</span></th></tr>
            </thead>
            <tbody>
                @foreach ($rts as $rt)
                    <tr @class(['opacity-60' => ! $rt->is_active])>
                        <td class="font-semibold tabular-nums">{{ $rt->number }} @unless ($rt->is_active)<span class="block text-xs font-normal text-ink-subtle">Nonaktif</span>@endunless</td>
                        <td class="text-ink-muted">{{ $rt->area_description ?? '—' }}</td>
                        <td>
                            @if ($rt->ketua)
                                {{ $rt->ketua->name }}
                                @unless ($rt->ketua->is_active)<span class="block text-xs text-danger">Akun nonaktif</span>@endunless
                            @else
                                <a href="{{ route('rw.heads.edit', $rt) }}" class="font-semibold text-warning">Belum ditetapkan</a>
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $rt->residents_count }}<span class="block text-xs text-ink-subtle">{{ $rt->verified_count }} terverifikasi</span></td>
                        <td class="text-right tabular-nums">{{ $rt->active_requests_count }}</td>
                        <td class="text-right tabular-nums">{{ $rt->done_count }}</td>
                        <td class="whitespace-nowrap text-right"><a href="{{ route('rw.rts.edit', $rt) }}" class="font-semibold">Ubah<span class="sr-only"> RT {{ $rt->number }}</span></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
