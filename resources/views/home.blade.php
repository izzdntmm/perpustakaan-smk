<x-layouts.public title="Beranda">

    <section class="mb-6 rounded-box bg-primary p-6 text-primary-content sm:p-8">
        <h1 class="text-xl font-bold sm:text-3xl">Selamat datang di Perpustakaan</h1>
        <p class="mt-2 max-w-xl text-sm opacity-90 sm:text-base">
            Cari buku, baca koleksi digital langsung di browser, dan ajukan peminjaman buku fisik tanpa perlu membuat akun.
        </p>
    </section>

    @if ($books->isEmpty())
        <x-empty-state
            icon="book-open"
            title="Belum ada buku"
            message="Koleksi buku akan tampil di sini setelah pustakawan menambahkannya." />
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
            @foreach ($books as $book)
                <x-book.card :book="$book" />
            @endforeach
        </div>
    @endif

</x-layouts.public>