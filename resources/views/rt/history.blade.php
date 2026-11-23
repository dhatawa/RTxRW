<x-layouts.app title="Riwayat verifikasi">
    <x-page-header :eyebrow="'RT '.$rt->number" title="Riwayat verifikasi" description="Semua keputusan yang diambil di tingkat RT ini, termasuk oleh Ketua RT sebelumnya." />

    <x-tabs param="aksi" :current="$action ?? ''" :items="['' => 'Semua', 'rt_approved' => 'Diteruskan ke RW', 'rt_revision' => 'Diminta perbaikan', 'rt_rejected' => 'Ditolak']" />

    <div class="panel overflow-hidden">
        @if ($histories->isEmpty())
            <x-empty title="Belum ada keputusan" icon="history" />
        @else
            <table class="table-civic hidden md:table">
                <thead><tr><th>Waktu</th><th>Keputusan</th><th>Pengajuan</th><th>Catatan</th><th>Oleh</th></tr></thead>
                <tbody>
                    @foreach ($histories as $h)
                        <tr>
                            <td class="whitespace-nowrap text-ink-muted">{{ $h->created_at->translatedFormat('j M Y, H:i') }}</td>
                            <td class="font-medium">{{ $h->action->label() }}</td>
                            <td>
                                <a href="{{ route('rt.requests.show', $h->letterRequest) }}">{{ $h->letterRequest->resident->full_name }}</a>
                                <span class="block text-sm text-ink-subtle">{{ $h->letterRequest->letterType->name }}</span>
                            </td>
                            <td class="max-w-xs text-sm text-ink-muted">{{ $h->note ?? '—' }}</td>
                            <td class="text-sm">{{ $h->actor?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <ul class="divide-y divide-line md:hidden">
                @foreach ($histories as $h)
                    <li class="px-4 py-3.5">
                        <p class="font-medium">{{ $h->action->label() }}</p>
                        <p class="text-sm"><a href="{{ route('rt.requests.show', $h->letterRequest) }}">{{ $h->letterRequest->resident->full_name }}</a> · {{ $h->letterRequest->letterType->name }}</p>
                        @if ($h->note)<p class="mt-1 text-sm text-ink-muted">{{ $h->note }}</p>@endif
                        <p class="text-xs text-ink-subtle">{{ $h->created_at->translatedFormat('j M Y, H:i') }} · {{ $h->actor?->name }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="mt-6">{{ $histories->links() }}</div>
</x-layouts.app>
