{{-- Tahapan proses pengajuan: Dikirim → RT → RW → Terbit. Menunjukkan posisi saat ini secara sekilas. --}}
@props(['request'])
@php
    use App\Enums\RequestStatus as S;
    $status = $request->status;
    $steps = [
        ['label' => 'Dikirim', 'done' => $status !== S::Draft],
        ['label' => 'Verifikasi RT', 'done' => in_array($status, [S::PendingRw, S::Approved, S::Issued, S::Completed, S::RejectedByRw])],
        ['label' => 'Persetujuan RW', 'done' => in_array($status, [S::Approved, S::Issued, S::Completed])],
        ['label' => 'Surat terbit', 'done' => in_array($status, [S::Issued, S::Completed])],
    ];
    $currentIndex = match ($status) {
        S::Draft => 0,
        S::PendingRt, S::RevisionRequired, S::RejectedByRt, S::Submitted => 1,
        S::PendingRw, S::RejectedByRw => 2,
        S::Approved => 3,
        S::Issued, S::Completed => 4,
    };
    $problem = in_array($status, [S::RejectedByRt, S::RejectedByRw, S::RevisionRequired]);
@endphp
<ol {{ $attributes->class(['grid grid-cols-4 gap-1.5']) }} aria-label="Tahapan pengajuan">
    @foreach ($steps as $i => $step)
        @php
            $isCurrent = $i === $currentIndex && ! $step['done'];
        @endphp
        <li class="min-w-0">
            <span @class([
                'block h-1.5 rounded-full',
                'bg-primary' => $step['done'],
                'bg-warning' => $isCurrent && $status === S::RevisionRequired,
                'bg-danger' => $isCurrent && in_array($status, [S::RejectedByRt, S::RejectedByRw]),
                'bg-secondary/60' => $isCurrent && ! $problem,
                'bg-line' => ! $step['done'] && ! $isCurrent,
            ])></span>
            <span @class([
                'mt-1.5 block truncate text-xs',
                'font-semibold text-ink' => $isCurrent || $step['done'],
                'text-ink-subtle' => ! $isCurrent && ! $step['done'],
            ])>
                {{ $step['label'] }}
                @if ($isCurrent)<span class="sr-only">(tahap saat ini)</span>@endif
            </span>
        </li>
    @endforeach
</ol>
