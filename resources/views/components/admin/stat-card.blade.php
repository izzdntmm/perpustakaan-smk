@props([
    'icon',
    'tone' => 'primary',
    'label',
    'value',
    'caption' => null,
    'href' => null,
])

@php
    $tones = [
        'primary' => 'bg-primary/10 text-primary',
        'accent' => 'bg-accent/10 text-accent',
        'secondary' => 'bg-secondary/10 text-secondary',
        'error' => 'bg-error/10 text-error',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'flex flex-col rounded-box border border-base-300 bg-base-100 p-5',
        'transition hover:border-primary/40 hover:shadow-md' => $href,
    ]) }}>

    <span class="grid size-10 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['primary'] }}">
        @svg('heroicon-o-'.$icon, 'size-5')
    </span>

    <p class="mt-5 text-sm text-neutral">{{ $label }}</p>
    <p class="mt-1 font-heading text-3xl font-bold text-neutral">{{ number_format((int) $value, 0, ',', '.') }}</p>

    @if ($caption)
        <p class="mt-2 text-xs text-base-content/70">{{ $caption }}</p>
    @endif
</{{ $tag }}>