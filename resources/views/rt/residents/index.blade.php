<x-layouts.app title="Data warga">
    <x-page-header :eyebrow="'RT '.$rt->number" title="Data warga" description="Warga yang terdaftar di RT Anda. Verifikasi pendaftar baru agar mereka bisa mengajukan surat." />

    <x-tabs param="status" :current="$status ?? ''" :keep="['q']" :items="[
        '' => ['label' => 'Semua', 'count' => null],
        'pending' => ['label' => 'Menunggu verifikasi', 'count' => $counts['pending'] ?? 0],
        'verified' => 'Terverifikasi',
        'rejected' => 'Perlu perbaikan',
    ]" />

    <form method="GET" class="mb-5 flex gap-2 sm:max-w-md" role="search">
        <input type="hidden" name="status" value="{{ $status }}">
        <label for="q" class="sr-only">Cari warga</label>
        <input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari nama, alamat, atau NIK lengkap">
        <x-button variant="secondary" icon="search"><span class="sr-only sm:not-sr-only">Cari</span></x-button>
    </form>

    <div class="panel overflow-hidden">
        @if ($residents->isEmpty())
            <x-empty title="Tidak ada warga yang cocok" icon="users" />
        @else
            <table class="table-civic hidden md:table">
                <thead><tr><th>Nama</th><th>NIK</th><th>Alamat</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    @foreach ($residents as $res)
                        <tr>
                            <td><span class="font-semibold">{{ $res->full_name }}</span><span class="block text-sm text-ink-subtle">{{ $res->user?->phone }}</span></td>
                            <td class="font-mono text-sm">{{ $res->maskedNik() }}</td>
                            <td class="text-ink-muted">{{ $res->address }}{{ $res->house_number ? ' No. '.$res->house_number : '' }}</td>
                            <td><x-verification-badge :status="$res->verification_status" /></td>
                            <td class="text-right"><a href="{{ route('rt.residents.show', $res) }}" class="font-semibold">{{ $res->verification_status === \App\Enums\VerificationStatus::Pending ? 'Periksa' : 'Detail' }}<span class="sr-only"> {{ $res->full_name }}</span></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <ul class="divide-y divide-line md:hidden">
                @foreach ($residents as $res)
                    <li>
                        <a href="{{ route('rt.residents.show', $res) }}" class="block px-4 py-3.5 text-ink no-underline">
                            <span class="flex items-center justify-between gap-3"><span class="font-semibold">{{ $res->full_name }}</span><x-svg-icon name="chevron-right" class="size-5 text-ink-subtle" /></span>
                            <span class="block text-sm text-ink-muted">{{ $res->address }}</span>
                            <x-verification-badge :status="$res->verification_status" class="mt-1" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="mt-6">{{ $residents->links() }}</div>
</x-layouts.app>
