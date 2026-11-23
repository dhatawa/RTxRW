<x-layouts.app title="Audit log">
    <x-page-header title="Audit log" description="Catatan semua tindakan penting di sistem. Log tidak dapat diubah atau dihapus dari aplikasi." />

    <form method="GET" class="mb-5 grid gap-2 sm:grid-cols-[1fr_14rem_11rem_auto]" role="search">
        <label class="sr-only" for="q">Cari</label>
        <input id="q" name="q" value="{{ $search }}" class="input" placeholder="Cari deskripsi atau nama pengguna">
        <label class="sr-only" for="jenis">Jenis aktivitas</label>
        <select id="jenis" name="jenis" class="input">
            <option value="">Semua aktivitas</option>
            @foreach ($groups as $key => $label)<option value="{{ $key }}" @selected($group === $key)>{{ $label }}</option>@endforeach
        </select>
        <label class="sr-only" for="tanggal">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" value="{{ request('tanggal') }}" class="input">
        <x-button variant="secondary" icon="search">Terapkan</x-button>
    </form>

    <div class="panel overflow-hidden">
        @if ($logs->isEmpty())
            <x-empty title="Tidak ada catatan yang cocok" icon="scroll-text" />
        @else
            <table class="table-civic hidden md:table">
                <thead><tr><th class="w-40">Waktu</th><th>Pengguna</th><th>Aktivitas</th><th class="w-32">IP</th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-sm text-ink-muted">{{ $log->created_at->translatedFormat('j M Y') }}<span class="block">{{ $log->created_at->format('H:i:s') }}</span></td>
                            <td class="text-sm">{{ $log->user?->name ?? 'Sistem' }}<span class="block text-xs text-ink-subtle">{{ $log->user?->role->label() }}</span></td>
                            <td>
                                <span class="text-[0.9375rem]">{{ $log->description }}</span>
                                <span class="block font-mono text-xs text-ink-subtle">{{ $log->action }}</span>
                                @if (! empty($log->properties['note']))<span class="mt-1 block border-l-2 border-line-strong pl-2 text-sm text-ink-muted">{{ $log->properties['note'] }}</span>@endif
                            </td>
                            <td class="font-mono text-xs text-ink-muted">{{ $log->ip_address }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <ul class="divide-y divide-line md:hidden">
                @foreach ($logs as $log)
                    <li class="px-4 py-3">
                        <p class="text-[0.9375rem]">{{ $log->description }}</p>
                        <p class="text-xs text-ink-subtle">{{ $log->created_at->translatedFormat('j M Y H:i') }} · {{ $log->user?->name ?? 'Sistem' }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    <div class="mt-6">{{ $logs->links() }}</div>
</x-layouts.app>
