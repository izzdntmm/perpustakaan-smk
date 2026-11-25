@props(['book'])

<span {{ $attributes->class([
    'text-xs font-semibold',
    'text-primary' => $book->isDigital(),
    'text-accent' => ! $book->isDigital(),
]) }}>{{ $book->type->label() }}</span>