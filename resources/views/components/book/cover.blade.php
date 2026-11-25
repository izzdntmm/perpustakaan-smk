@props(['book'])

<div {{ $attributes->class('flex items-center justify-center bg-base-200 px-6 py-7') }}>
    <div class="relative aspect-[3/4] w-[58%] rounded-l-sm rounded-r-lg bg-base-100 shadow-[6px_6px_0_0_var(--color-base-300)]">
        @if ($book->cover_path)
            <img src="{{ $book->coverUrl() }}"
                 alt="Sampul buku {{ $book->title }}"
                 loading="lazy"
                 class="size-full rounded-l-sm rounded-r-lg object-cover">
        @else
            <div class="flex size-full flex-col justify-between p-2.5 sm:p-3">
                <span class="truncate text-[8px] font-medium uppercase tracking-widest text-base-content/40 sm:text-[9px]">{{ $book->category?->name }}</span>
                <span class="line-clamp-4 font-heading text-[11px] font-bold leading-tight text-base-content/35 sm:text-sm">{{ $book->title }}</span>
                <span class="truncate text-[8px] text-base-content/40 sm:text-[9px]">{{ $book->author }}</span>
            </div>
        @endif
    </div>
</div>