@php
    $isEdit = $book->exists;
    $type = old('type', $book->type?->value ?? 'physical');
    $borrowed = $isEdit && $book->isPhysical() ? $book->stock_total - $book->stock_available : 0;
    $checkedFeatured = session()->hasOldInput() ? (bool) old('is_featured') : (bool) $book->is_featured;
@endphp

<form id="book-form" method="POST" action="{{ $action }}" enctype="multipart/form-data"
      data-type="{{ $type }}" class="grid gap-6 lg:grid-cols-3">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="space-y-6 lg:col-span-2">

        {{-- Informasi buku --}}
        <section class="rounded-box border border-base-300 bg-base-100 p-5 sm:p-6">
            <h2 class="text-xl font-semibold text-neutral">Informasi buku</h2>

            <div class="mt-4 grid gap-x-4 gap-y-2 sm:grid-cols-2">
                <x-admin.field name="title" label="Judul buku" class="sm:col-span-2">
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" required maxlength="255"
                           class="input w-full @error('title') input-error @enderror">
                </x-admin.field>

                <x-admin.field name="author" label="Penulis">
                    <input type="text" name="author" value="{{ old('author', $book->author) }}" required maxlength="255"
                           class="input w-full @error('author') input-error @enderror">
                </x-admin.field>

                <x-admin.field name="category_id" label="Kategori">
                    <select name="category_id" required class="select w-full @error('category_id') select-error @enderror">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $book->category_id) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.field>

                <x-admin.field name="publisher" label="Penerbit (opsional)">
                    <input type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" maxlength="255"
                           class="input w-full @error('publisher') input-error @enderror">
                </x-admin.field>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.field name="year_published" label="Tahun terbit">
                        <input type="number" name="year_published" value="{{ old('year_published', $book->year_published) }}"
                               min="1800" max="{{ now()->year + 1 }}" inputmode="numeric"
                               class="input w-full @error('year_published') input-error @enderror">
                    </x-admin.field>

                    <x-admin.field name="isbn" label="ISBN">
                        <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" maxlength="20"
                               class="input w-full @error('isbn') input-error @enderror">
                    </x-admin.field>
                </div>

                <x-admin.field name="synopsis" label="Sinopsis (opsional)" class="sm:col-span-2">
                    <textarea name="synopsis" rows="5" maxlength="3000"
                              class="textarea w-full @error('synopsis') textarea-error @enderror">{{ old('synopsis', $book->synopsis) }}</textarea>
                </x-admin.field>
            </div>
        </section>

        {{-- Jenis dan ketersediaan --}}
        <section class="rounded-box border border-base-300 bg-base-100 p-5 sm:p-6">
            <h2 class="text-xl font-semibold text-neutral">Jenis dan ketersediaan</h2>

            @if ($isEdit)
                <p class="mt-3 flex items-center gap-2 text-sm text-base-content/70">
                    <x-badge.book-type :book="$book" />
                    Jenis buku tidak dapat diubah setelah dibuat.
                </p>
            @else
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['physical', 'Buku Fisik', 'Buku cetak dengan stok dan nomor rak.', 'book-open'],
                        ['digital', 'Buku Digital', 'File PDF yang dibaca online.', 'device-tablet'],
                    ] as [$value, $label, $desc, $icon])
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-base-300 p-4 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="type" value="{{ $value }}" class="radio radio-primary mt-1"
                                   @checked($type === $value)>
                            <span>
                                <span class="flex items-center gap-2 font-semibold text-neutral">
                                    @svg('heroicon-o-'.$icon, 'size-5 text-primary')
                                    {{ $label }}
                                </span>
                                <span class="mt-0.5 block text-sm text-base-content/70">{{ $desc }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-error">{{ $message }}</p>
                @enderror
            @endif

            {{-- Bagian fisik --}}
            <div data-for="physical" @if ($type !== 'physical') hidden @endif class="mt-4 grid gap-x-4 gap-y-2 sm:grid-cols-2">
                <x-admin.field name="stock_total" label="Total eksemplar"
                               :hint="$isEdit ? ($borrowed > 0
                                    ? $borrowed.' eksemplar sedang dipinjam; total tidak boleh kurang dari itu.'
                                    : 'Saat ini tidak ada eksemplar yang sedang dipinjam.')
                                    : 'Stok di rak otomatis sama dengan total saat buku ditambahkan.'">
                    <input type="number" name="stock_total" value="{{ old('stock_total', $book->stock_total ?: ($isEdit ? 0 : 1)) }}"
                           min="0" max="9999" inputmode="numeric"
                           class="input w-full @error('stock_total') input-error @enderror">
                </x-admin.field>

                <x-admin.field name="shelf_location" label="Nomor rak (opsional)">
                    <input type="text" name="shelf_location" value="{{ old('shelf_location', $book->shelf_location) }}"
                           maxlength="50" placeholder="Contoh: A-01"
                           class="input w-full @error('shelf_location') input-error @enderror">
                </x-admin.field>
            </div>

            {{-- Bagian digital --}}
            <div data-for="digital" @if ($type !== 'digital') hidden @endif class="mt-4">
                <x-admin.field name="pdf" label="File PDF"
                               hint="Maksimal 30 MB. Buku digital hanya bisa dibaca di layar dan tidak dapat diunduh pengunjung.">
                    <input type="file" name="pdf" accept="application/pdf"
                           class="file-input w-full @error('pdf') file-input-error @enderror">
                </x-admin.field>

                @if ($isEdit && $book->hasPdf())
                    <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-base-content/70">
                        <span class="inline-flex items-center gap-1 text-accent">
                            @svg('heroicon-o-check-circle', 'size-4') File PDF sudah terunggah.
                        </span>
                        Pilih file baru hanya jika ingin menggantinya.
                        <a href="{{ route('books.read', $book) }}" target="_blank" rel="noopener" class="link link-primary">Uji baca</a>
                    </p>
                @endif
            </div>
        </section>
    </div>

    {{-- Kolom kanan --}}
    <aside class="space-y-6">

        <section class="rounded-box border border-base-300 bg-base-100 p-5 sm:p-6">
            <h2 class="text-xl font-semibold text-neutral">Sampul</h2>

            <div class="mx-auto mt-4 w-40 overflow-hidden rounded-lg border border-base-300 bg-base-200">
                <div class="aspect-[3/4]">
                    <img id="cover-preview" alt="Pratinjau sampul" class="size-full object-cover"
                         @if ($book->cover_path) src="{{ $book->coverUrl() }}" @else hidden @endif>
                    <div id="cover-empty" class="grid size-full place-items-center text-base-content/30"
                         @if ($book->cover_path) hidden @endif>
                        @svg('heroicon-o-photo', 'size-10')
                    </div>
                </div>
            </div>

            <x-admin.field name="cover" label="Unggah sampul" class="mt-4"
                           hint="JPG, PNG, atau WebP. Maksimal 2 MB. Rasio 3:4 paling pas.">
                <input id="cover-input" type="file" name="cover" accept="image/jpeg,image/png,image/webp"
                       class="file-input w-full @error('cover') file-input-error @enderror">
            </x-admin.field>

            @if ($isEdit && $book->cover_path)
                <label class="mt-2 flex cursor-pointer items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_cover" value="1" class="checkbox checkbox-sm">
                    Hapus sampul saat ini
                </label>
            @endif
        </section>

        <section class="rounded-box border border-base-300 bg-base-100 p-5 sm:p-6">
            <h2 class="text-xl font-semibold text-neutral">Tampilan di beranda</h2>
            <label class="mt-4 flex cursor-pointer items-start justify-between gap-4">
                <span>
                    <span class="block font-medium text-neutral">Buku rekomendasi</span>
                    <span class="mt-0.5 block text-sm text-base-content/70">Tampilkan di bagian pilihan pustakawan.</span>
                </span>
                <input type="checkbox" name="is_featured" value="1" class="toggle toggle-primary mt-1"
                       @checked($checkedFeatured)>
            </label>
        </section>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary flex-1">
                @svg('heroicon-o-check', 'size-5')
                {{ $isEdit ? 'Simpan perubahan' : 'Simpan buku' }}
            </button>
            <a href="{{ route('admin.books.index') }}" class="btn">Batal</a>
        </div>
    </aside>
</form>

@push('scripts')
    <script>
        const form = document.getElementById('book-form');

        // Tampilkan bagian fisik atau digital sesuai jenis buku
        function syncType() {
            const checked = form.querySelector('input[name="type"]:checked');
            const type = checked ? checked.value : form.dataset.type;
            form.querySelectorAll('[data-for]').forEach((el) => {
                el.hidden = el.dataset.for !== type;
            });
        }
        form.querySelectorAll('input[name="type"]').forEach((r) => r.addEventListener('change', syncType));
        syncType();

        // Pratinjau sampul sebelum diunggah
        const input = document.getElementById('cover-input');
        const preview = document.getElementById('cover-preview');
        const empty = document.getElementById('cover-empty');
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            empty.hidden = true;
        });
    </script>
@endpush