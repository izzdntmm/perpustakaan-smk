@props(['book'])

<span {{ $attributes->class(['badge badge-sm gap-1', $book->type->badgeClass()]) }}>
    @if ($book->isDigital())
        @svg('heroicon-s-device-tablet', 'size-3.5')
    @else
        @svg('heroicon-s-book-open', 'size-3.5')
    @endif
    {{ $book->type->label() }}
</span>