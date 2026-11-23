{{-- Logotype teks: "RT" dan "RW" dihubungkan tanda × sebagai simbol kerja sama dua tingkat. --}}
@props(['size' => 'md', 'inverse' => false])
<span {{ $attributes->class([
    'inline-flex items-baseline font-bold tracking-[-0.03em]',
    'text-lg' => $size === 'sm',
    'text-xl' => $size === 'md',
    'text-3xl' => $size === 'lg',
    $inverse ? 'text-white' : 'text-primary',
]) }}>RT<span @class(['mx-[0.08em] font-medium', $inverse ? 'text-white/60' : 'text-secondary'])>×</span>RW</span>
