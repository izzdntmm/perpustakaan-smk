@props(['book'])

@php
    // Route detail buku dibuat di Part 9. Sementara tautan menuju '#'.
    $url = Route::has('books.show') ? route('books.show', $book) : '#';
@endphp

<a href="{{ $url }}"
   class="card bg-base-100 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-2 focus-visible:outline-primary">
    <figure>
        <x-book.cover :book="$book" />
    </figure>

    <div class="card-body gap-1.5 p-3 sm:p-4">
        <div>
            <x-badge.book-type :book="$book" />
        </div>

        <h3 class="card-title line-clamp-2 text-sm leading-snug sm:text-base">{{ $book->title }}</h3>
        <p class="line-clamp-1 text-xs text-base-content/70 sm:text-sm">{{ $book->author }}</p>

        <div class="mt-auto flex flex-wrap items-center justify-between gap-1 pt-2">
            <span class="max-w-full truncate text-xs text-base-content/60">{{ $book->category?->name }}</span>
            <x-badge.stock :book="$book" />
        </div>
    </div>
</a>