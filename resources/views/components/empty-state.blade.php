@props([
    'icon' => 'inbox',
    'title' => 'Belum ada data',
    'message' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center gap-3 rounded-box bg-base-100 px-6 py-16 text-center') }}>
    @svg('heroicon-o-'.$icon, 'size-12 text-base-content/30')
    <h3 class="text-lg font-semibold">{{ $title }}</h3>

    @if ($message)
        <p class="max-w-md text-sm text-base-content/60">{{ $message }}</p>
    @endif

    {{ $slot }}
</div>