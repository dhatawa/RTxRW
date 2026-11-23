<x-layouts.app title="Data warga">
    <x-page-header title="Data warga" description="Warga terdaftar di seluruh RT. Verifikasi dilakukan oleh Ketua RT masing-masing." />

    <form method="GET" class="mb-5 grid gap-2 sm:grid-cols-[1fr_9rem_12rem_auto]" role="search">
        <label class="sr-only" for="q">Cari</label>
        <input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari nama atau NIK lengkap">
        <label class="sr-only" for="rt">RT</label>
        <select id="rt" name="rt" class="input">
            <option value="">Semua RT</option>
            @foreach ($rts as $rt)<option value="{{ $rt->id }}" @selected(request('rt') == $rt->id)>RT {{ $rt->number }}</option>@endforeach
        </select>
        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status" class="input">
            <option value="">Semua status</option>
            @foreach (\App\Enums\VerificationStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>@endforeach
        </select>
        <x-button variant="secondary" icon="search">Terapkan</x-button>
    </form>

    <div class="panel overflow-hidden">
        @if ($residents->isEmpty())
            <x-empty title="Tidak ada warga yang cocok" icon="users" />
        @else
            <table class="table-civic hidden md:table">
                <thead><tr><th>Nama</th><th>RT</th><th>NIK</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                    @foreach ($residents as $res)
                        <tr>
                            <td class="font-semibold">{{ $res->full_name }}<span class="block text-sm font-normal text-ink-subtle">{{ $res->address }}</span></td>
                            <td class="tabular-nums">{{ $res->rt->number }}</td>
                            <td class="font-mono text-sm">{{ $res->maskedNik() }}</td>
                            <td><x-verification-badge :status="$res->verification_status" /></td>
                            <td class="text-right"><a href="{{ route('rw.residents.show', $res) }}" class="font-semibold">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <ul class="divide-y divide-line md:hidden">
                @foreach ($residents as $res)
                    <li><a href="{{ route('rw.residents.show', $res) }}" class="block px-4 py-3.5 text-ink no-underline">
                        <span class="font-semibold">{{ $res->full_name }}</span>
                        <span class="block text-sm text-ink-muted">RT {{ $res->rt->number }} · {{ $res->address }}</span>
                        <x-verification-badge :status="$res->verification_status" class="mt-1" />
                    </a></li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="mt-6">{{ $residents->links() }}</div>
</x-layouts.app>
