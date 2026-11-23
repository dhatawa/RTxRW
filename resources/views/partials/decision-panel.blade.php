{{--
    Panel keputusan berjenjang. Tiga pilihan eksplisit; catatan wajib untuk tolak / minta perbaikan.
    $action: URL form, $approveLabel: label tombol setuju, $approveHint: penjelasan dampak.
--}}
<form method="POST" action="{{ $action }}" class="panel" novalidate
      x-data="{ decision: '{{ old('decision', '') }}' }">
    @csrf
    <div class="panel-header"><h2 class="panel-title">Keputusan Anda</h2></div>
    <div class="space-y-4 px-5 py-5">
        <fieldset class="space-y-2">
            <legend class="sr-only">Pilih keputusan</legend>
            <label class="flex cursor-pointer gap-3 rounded-md border border-line px-3 py-3 has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                <input type="radio" name="decision" value="approve" x-model="decision" class="mt-0.5 size-5 accent-primary">
                <span><span class="block font-semibold">{{ $approveLabel }}</span><span class="block text-sm text-ink-muted">{{ $approveHint }}</span></span>
            </label>
            <label class="flex cursor-pointer gap-3 rounded-md border border-line px-3 py-3 has-[:checked]:border-warning has-[:checked]:bg-warning-soft">
                <input type="radio" name="decision" value="revision" x-model="decision" class="mt-0.5 size-5 accent-warning">
                <span><span class="block font-semibold">Minta perbaikan</span><span class="block text-sm text-ink-muted">Dikembalikan ke warga untuk diperbaiki, lalu diperiksa ulang oleh Ketua RT.</span></span>
            </label>
            <label class="flex cursor-pointer gap-3 rounded-md border border-line px-3 py-3 has-[:checked]:border-danger has-[:checked]:bg-danger-soft">
                <input type="radio" name="decision" value="reject" x-model="decision" class="mt-0.5 size-5 accent-danger">
                <span><span class="block font-semibold">Tolak</span><span class="block text-sm text-ink-muted">Pengajuan ditutup. Warga perlu membuat pengajuan baru.</span></span>
            </label>
        </fieldset>

        @error('decision')<p class="field-error"><x-svg-icon name="circle-alert" class="mt-0.5 size-4" />{{ $message }}</p>@enderror

        <div x-show="decision && decision !== 'approve'" x-cloak>
            <x-field name="note" type="textarea" rows="4"
                     label="Catatan untuk warga"
                     hint="Tulis dengan jelas. Catatan ini dikirim ke warga dan tersimpan di riwayat." />
        </div>

        <x-button class="w-full" x-bind:disabled="!decision"
                  x-bind:class="decision === 'reject' ? 'btn-danger' : ''">
            <span x-text="decision === 'approve' ? '{{ $approveLabel }}' : (decision === 'revision' ? 'Kirim permintaan perbaikan' : (decision === 'reject' ? 'Tolak pengajuan' : 'Pilih keputusan'))">Simpan keputusan</span>
        </x-button>
        <p class="text-xs text-ink-subtle">Keputusan tercatat atas nama {{ auth()->user()->name }} dan tidak dapat dibatalkan.</p>
    </div>
</form>
