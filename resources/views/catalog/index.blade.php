<x-layouts.public title="Katalog Buku">
    <x-container class="py-10">

        <x-section-heading
            eyebrow="Katalog"
            title="Katalog buku"
            subtitle="Cari dan jelajahi koleksi buku fisik maupun digital." />

        <div class="mt-8">
            <x-catalog.filters
                :filters="$filters"
                :categories="$categories"
                :total="$books->total()" />
        </div>

        <div class="mt-8">
            @if ($books->isEmpty())
                <x-empty-state
                    icon="magnifying-glass"
                    title="Buku tidak ditemukan"
                    :message="filled($filters['q'])
                        ? 'Tidak ada buku yang cocok dengan kata kunci tersebut. Coba kata lain atau kurangi filter.'
                        : 'Belum ada buku pada pilihan ini. Coba filter lain.'">
                    <a href="{{ route('catalog') }}" class="btn btn-primary btn-sm mt-2">Lihat semua buku</a>
                </x-empty-state>
            @else
                <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
                    @foreach ($books as $book)
                        <x-book.card :book="$book" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $books->links() }}
                </div>
            @endif
        </div>

    </x-container>
</x-layouts.public>