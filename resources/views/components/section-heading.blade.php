@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'url' => null,
    'urlLabel' => 'Lihat semua',
])

<div {{ $attributes->class('flex items-end justify-between gap-4') }}>
    <div>
        @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">{{ $eyebrow }}</p>
        @endif
        <h2 class="mt-2 text-3xl font-semibold text-neutral sm:text-4xl">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-2 text-base-content/70">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($url)
        <a href="{{ $url }}" class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap text-sm font-semibold text-primary hover:underline">
            {{ $urlLabel }}
            @svg('heroicon-o-arrow-right', 'size-4')
        </a>
    @endif
</div>