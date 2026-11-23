{{-- Riwayat keputusan pengajuan, urut dari yang terlama. --}}
@props(['histories'])
@php
    $toneFor = fn ($action) => match ($action) {
        \App\Enums\WorkflowAction::RtReject, \App\Enums\WorkflowAction::RwReject => 'danger',
        \App\Enums\WorkflowAction::RtRevision, \App\Enums\WorkflowAction::RwRevision => 'warning',
        \App\Enums\WorkflowAction::Issue, \App\Enums\WorkflowAction::Complete, \App\Enums\WorkflowAction::RwApprove => 'success',
        default => 'neutral',
    };
@endphp
<ol {{ $attributes->class(['relative']) }}>
    @forelse ($histories as $history)
        @php $tone = $toneFor($history->action); @endphp
        <li class="relative flex gap-4 pb-6 last:pb-0">
            @unless ($loop->last)
                <span class="absolute left-[7px] top-5 h-full w-px bg-line-strong" aria-hidden="true"></span>
            @endunless
            <span @class([
                'relative z-10 mt-1.5 size-[15px] shrink-0 rounded-full border-[3px] bg-surface',
                'border-primary' => $tone === 'success' || $tone === 'neutral',
                'border-warning' => $tone === 'warning',
                'border-danger' => $tone === 'danger',
            ]) aria-hidden="true"></span>
            <div class="min-w-0 flex-1">
                <p class="font-semibold leading-snug">{{ $history->action->label() }}</p>
                <p class="mt-0.5 text-sm text-ink-muted">
                    {{ $history->actor?->name ?? 'Sistem' }} · {{ $history->actor_role->label() }}
                    <span aria-hidden="true">·</span>
                    <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->translatedFormat('j M Y, H:i') }}</time>
                </p>
                @if ($history->note && $history->action !== \App\Enums\WorkflowAction::Issue)
                    <blockquote @class([
                        'mt-2 border-l-[3px] py-1 pl-3 text-[0.9375rem]',
                        'border-warning text-ink' => $tone === 'warning',
                        'border-danger text-ink' => $tone === 'danger',
                        'border-line-strong text-ink-muted' => ! in_array($tone, ['warning', 'danger']),
                    ])>{{ $history->note }}</blockquote>
                @elseif ($history->note)
                    <p class="mt-1 text-sm text-ink-muted">{{ $history->note }}</p>
                @endif
            </div>
        </li>
    @empty
        <li class="text-sm text-ink-muted">Belum ada riwayat. Pengajuan masih berupa draf.</li>
    @endforelse
</ol>
