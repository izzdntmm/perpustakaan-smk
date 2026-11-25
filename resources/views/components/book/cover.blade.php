@props(['book'])

<div {{ $attributes->class('aspect-[3/4] w-full overflow-hidden bg-base-300') }}>
    @if ($book->cover_path)
        <img
            src="{{ $book->coverUrl() }}"
            alt="Sampul buku {{ $book->title }}"
            loading="lazy"
            class="size-full object-cover"
        >
    @else
        <div class="flex size-full flex-col items-center justify-center gap-2 bg-linear-to-br from-primary/15 to-secondary/30 p-4 text-center text-primary">
            @if ($book->isDigital())
                @svg('heroicon-o-device-tablet', 'size-10')
            @else
                @svg('heroicon-o-book-open', 'size-10')
            @endif
            <span class="line-clamp-3 text-xs font-medium text-base-content/70">{{ $book->title }}</span>
        </div>
    @endif
</div>