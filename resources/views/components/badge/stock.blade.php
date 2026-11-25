@props(['book'])

@if ($book->isDigital())
    <span {{ $attributes->class('badge badge-sm badge-ghost gap-1') }}>
        @svg('heroicon-o-eye', 'size-3.5')
        Baca online
    </span>
@elseif ($book->stock_available > 0)
    <span {{ $attributes->class('badge badge-sm badge-success badge-soft gap-1') }}>
        @svg('heroicon-o-check-circle', 'size-3.5')
        Tersedia ({{ $book->stock_available }})
    </span>
@else
    <span {{ $attributes->class('badge badge-sm badge-error badge-soft gap-1') }}>
        @svg('heroicon-o-x-circle', 'size-3.5')
        Habis
    </span>
@endif