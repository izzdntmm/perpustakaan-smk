<x-layouts.admin title="Data Buku">

    {{-- Bilah atas --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.books.index') }}" role="search"
              class="flex flex-1 flex-wrap items-center gap-2">
            <label class="input w-full bg-base-100 sm:w-64">
                @svg('heroicon-o-magnifying-glass', 'size-5 opacity-60')
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="100"
                       placeholder="Cari judul atau penulis" aria-label="Cari judul atau penulis" class="grow">
            </label>

            <select name="type" class="select w-[48%] bg-base-100 sm:w-36" aria-label="Filter jenis">
                <option value="">Semua jenis</option>
                <option value="physical" @selected($filters['type'] === 'physical')>Fisik</option>
                <option value="digital" @selected($filters['type'] === 'digital')>Digital</option>
            </select>

            <select name="category" class="select w-[48%] bg-base-100 sm:w-44" aria-label="Filter kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">Cari</button>

            @if (filled($filters['q']) || $filters['type'] || $filters['category'])
                <a href="{{ route('admin.books.index') }}" class="link link-primary text-sm">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            @svg('heroicon-o-plus', 'size-5')
            Tambah Buku
        </a>
    </div>

    {{-- Info rekomendasi --}}
    <p class="mt-4 flex items-start gap-2 text-sm text-base-content/70">
        @svg('heroicon-s-star', 'mt-0.5 size-4 shrink-0 text-secondary')
        <span>
            {{ $featuredCount }} buku ditandai rekomendasi. Beranda menampilkan maksimal 4 buku rekomendasi yang terbaru ditambahkan.
        </span>
    </p>

    {{-- Tabel --}}
    <section class="mt-4 rounded-box border border-base-300 bg-base-100">
        @if ($books->isEmpty())
            <x-empty-state icon="book-open" title="Buku tidak ditemukan"
                           message="Belum ada buku yang cocok dengan pencarian ini." class="border-0" />
        @else
            <div class="overflow-x-auto p-2 sm:p-3">
                <table class="table">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-base-content/70">
                            <th>Buku</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Stok / PDF</th>
                            <th class="text-center">Rekomendasi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($books as $book)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="h-14 w-10 shrink-0 overflow-hidden rounded-md border border-base-300 bg-base-200">
                                            @if ($book->cover_path)
                                                <img src="{{ $book->coverUrl() }}" alt="" loading="lazy" class="size-full object-cover">
                                            @else
                                                <div class="grid size-full place-items-center text-base-content/30">
                                                    @svg($book->isDigital() ? 'heroicon-o-device-tablet' : 'heroicon-o-book-open', 'size-5')
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('books.show', $book) }}" target="_blank" rel="noopener"
                                               class="line-clamp-2 font-semibold text-neutral hover:text-primary">{{ $book->title }}</a>
                                            <p class="text-xs text-base-content/70">{{ $book->author }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap">{{ $book->category->name }}</td>

                                <td><x-badge.book-type :book="$book" /></td>

                                <td class="whitespace-nowrap text-sm">
                                    @if ($book->isPhysical())
                                        <span @class([
                                            'font-semibold',
                                            'text-error' => $book->stock_available === 0,
                                            'text-neutral' => $book->stock_available > 0,
                                        ])>{{ $book->stock_available }}</span>
                                        <span class="text-base-content/70">/ {{ $book->stock_total }}</span>
                                    @elseif ($book->hasPdf())
                                        <span class="inline-flex items-center gap-1 text-accent">
                                            @svg('heroicon-o-check-circle', 'size-4') PDF ada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-error">
                                            @svg('heroicon-o-exclamation-triangle', 'size-4') PDF belum ada
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.books.featured', $book) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-square btn-sm"
                                                aria-label="{{ $book->is_featured ? 'Hapus dari rekomendasi' : 'Jadikan rekomendasi' }}"
                                                title="{{ $book->is_featured ? 'Hapus dari rekomendasi' : 'Jadikan rekomendasi' }}">
                                            @if ($book->is_featured)
                                                @svg('heroicon-s-star', 'size-5 text-secondary')
                                            @else
                                                @svg('heroicon-o-star', 'size-5 text-base-content/40')
                                            @endif
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.books.edit', $book) }}"
                                           class="btn btn-ghost btn-square btn-sm" aria-label="Ubah" title="Ubah">
                                            @svg('heroicon-o-pencil-square', 'size-5')
                                        </a>
                                        <button type="button"
                                                class="btn btn-ghost btn-square btn-sm text-error"
                                                data-delete-url="{{ route('admin.books.destroy', $book) }}"
                                                data-delete-title="{{ $book->title }}"
                                                aria-label="Hapus" title="Hapus">
                                            @svg('heroicon-o-trash', 'size-5')
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="mt-6">{{ $books->links() }}</div>

    {{-- Konfirmasi hapus --}}
    <dialog id="delete-modal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="text-lg font-semibold text-neutral">Hapus buku?</h3>
            <p class="mt-2 text-sm text-base-content/70">
                Buku <span id="delete-title" class="font-semibold text-neutral"></span> akan dihapus permanen
                beserta sampul dan file PDF-nya. Tindakan ini tidak dapat dibatalkan.
            </p>
            <form id="delete-form" method="POST" class="modal-action">
                @csrf
                @method('DELETE')
                <button type="button" class="btn" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="btn btn-error">Hapus</button>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button aria-label="Tutup">tutup</button></form>
    </dialog>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-delete-url]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    document.getElementById('delete-form').action = btn.dataset.deleteUrl;
                    document.getElementById('delete-title').textContent = '\u201C' + btn.dataset.deleteTitle + '\u201D';
                    document.getElementById('delete-modal').showModal();
                });
            });
        </script>
    @endpush

</x-layouts.admin>