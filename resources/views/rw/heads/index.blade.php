<x-layouts.app title="Ketua RT">
    <x-page-header title="Ketua RT" description="Akun Ketua RT yang berwenang memverifikasi warga dan pengajuan di wilayahnya masing-masing." />

    <div class="panel overflow-x-auto">
        <table class="table-civic min-w-[44rem]">
            <thead><tr><th>RT</th><th>Nama</th><th>Kontak</th><th>Terakhir masuk</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach ($rts as $rt)
                    <tr>
                        <td class="font-semibold tabular-nums">{{ $rt->number }}</td>
                        @if ($k = $rt->ketua)
                            <td class="font-medium">{{ $k->name }}</td>
                            <td class="text-sm">{{ $k->email }}<span class="block text-ink-subtle">{{ $k->phone }}</span></td>
                            <td class="text-sm text-ink-muted">{{ $k->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</td>
                            <td>
                                <span @class(['text-sm font-semibold', 'text-primary' => $k->is_active, 'text-danger' => ! $k->is_active])>{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <a href="{{ route('rw.heads.edit', $rt) }}" class="font-semibold">Kelola</a>
                                <form method="POST" action="{{ route('rw.heads.toggle', $rt) }}" class="ml-3 inline" x-data
                                      @submit="if (!confirm({{ \Illuminate\Support\Js::from(($k->is_active ? 'Nonaktifkan' : 'Aktifkan').' akun '.$k->name.'?') }})) $event.preventDefault()">
                                    @csrf
                                    <button class="cursor-pointer font-semibold text-ink-muted underline underline-offset-[3px] hover:text-ink">{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </td>
                        @else
                            <td colspan="4" class="text-ink-muted">Belum ada Ketua RT. Pengajuan dari RT ini tidak bisa diproses.</td>
                            <td class="text-right"><x-button :href="route('rw.heads.edit', $rt)" size="sm">Tetapkan</x-button></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
