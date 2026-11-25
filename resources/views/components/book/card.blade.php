@props(['book'])

@php
    // Route detail dibuat di Part 9. Sementara tautan menuju '#'.
    $url = Route::has('books.show') ? route('books.show', $book) : '#';
@endphp

<a href="{{ $url }}"
   class="group flex flex-col overflow-hidden rounded-box border border-base-300 bg-base-100 transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-primary">

    <div class="relative">
        <x-book.cover :book="$book" />
        <x-badge.book-type :book="$book" class="absolute right-3 top-3" />
    </div>

    <div class="flex flex-1 flex-col p-4">
        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-neutral">{{ $book->category?->name }}</p>
        <h3 class="mt-1.5 line-clamp-2 text-sm font-semibold leading-snug text-neutral sm:text-base">{{ $book->title }}</h3>
        <p class="mt-0.5 line-clamp-1 text-sm text-base-content/60">{{ $book->author }}</p>

        <div class="mt-auto pt-4">
            <div class="flex items-center justify-between border-t border-base-300 pt-3 text-sm">
                @if ($book->isDigital())
                    <span class="text-base-content/70">Baca online</span>
                @elseif ($book->stock_available > 0)
                    <span class="text-base-content/70">{{ $book->stock_available }} tersedia</span>
                @else
                    <span class="font-medium text-error">Stok habis</span>
                @endif

                <span class="grid size-8 place-items-center rounded-lg border border-base-300 text-primary transition group-hover:bg-primary group-hover:text-primary-content">
                    @svg('heroicon-o-arrow-up-right', 'size-4')
                </span>
            </div>
        </div>
    </div>
</a>