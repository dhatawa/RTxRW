{{-- Tabel pengajuan untuk RT/RW: tabel di layar lebar, daftar di ponsel. --}}
<div class="panel overflow-hidden">
    @if ($requests->isEmpty())
        <x-empty title="Tidak ada pengajuan di sini" icon="inbox">Coba pilih tab lain atau ubah pencarian.</x-empty>
    @else
        <table class="table-civic hidden md:table">
            <thead>
                <tr>
                    <th>Pemohon</th>
                    <th>Jenis surat</th>
                    @if ($showRt)<th>RT</th>@endif
                    <th>Dikirim</th>
                    <th>Status</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $req)
                    <tr>
                        <td>
                            <span class="font-semibold">{{ $req->resident->full_name }}</span>
                            <span class="block font-mono text-xs text-ink-subtle">{{ $req->reference_number }}</span>
                        </td>
                        <td>{{ $req->letterType->name }}</td>
                        @if ($showRt)<td class="tabular-nums">{{ $req->rt->number }}</td>@endif
                        <td class="whitespace-nowrap text-ink-muted">
                            {{ $req->submitted_at?->translatedFormat('j M Y') }}
                            <span class="block text-xs text-ink-subtle">{{ $req->submitted_at?->diffForHumans() }}</span>
                        </td>
                        <td>
                            <x-status :status="$req->status" size="sm" />
                            @if ($req->revision_count)<span class="mt-1 block text-xs text-ink-subtle">{{ $req->revision_count }}× diperbaiki</span>@endif
                        </td>
                        <td class="text-right"><a href="{{ route($routeName, $req) }}" class="font-semibold">Buka<span class="sr-only"> pengajuan {{ $req->reference_number }}</span></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <ul class="divide-y divide-line md:hidden">
            @foreach ($requests as $req)
                <li>
                    <a href="{{ route($routeName, $req) }}" class="block px-4 py-3.5 text-ink no-underline">
                        <span class="flex items-start justify-between gap-3">
                            <span class="font-semibold">{{ $req->resident->full_name }}</span>
                            <x-status :status="$req->status" size="sm" />
                        </span>
                        <span class="mt-0.5 block text-sm text-ink-muted">{{ $req->letterType->name }}@if ($showRt) · RT {{ $req->rt->number }}@endif</span>
                        <span class="block text-xs text-ink-subtle">{{ $req->submitted_at?->diffForHumans() }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
<div class="mt-6">{{ $requests->links() }}</div>
