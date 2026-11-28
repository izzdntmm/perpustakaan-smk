@php
    $info = [
        ['Penerbit', $book->publisher],
        ['Tahun terbit', $book->year_published],
        ['ISBN', $book->isbn],
        ['Format', $book->isDigital() ? 'E-Book (PDF)' : 'Buku cetak'],
    ];

    if ($book->isPhysical()) {
        $info[] = ['Nomor rak', $book->shelf_location];
        $info[] = ['Total eksemplar', $book->stock_total];
    }

    // Warna indikator stok: [latar, ikon, progress, nama ikon]
    $tones = [
        'success' => ['bg-success/10', 'text-success', 'progress-success', 'check-circle'],
        'warning' => ['bg-warning/10', 'text-warning', 'progress-warning', 'exclamation-triangle'],
        'error' => ['bg-error/10', 'text-error', 'progress-error', 'x-circle'],
    ];
    $tone = $borrowable === 0 ? 'error' : ($borrowable <= 2 ? 'warning' : 'success');
    [$toneBg, $toneText, $toneBar, $toneIcon] = $tones[$tone];
@endphp

<x-layouts.public :title="$book->title">
    <x-container class="py-8 lg:py-12">

        {{-- Breadcrumb --}}
        <nav class="mb-6 text-sm text-base-content/70" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-primary">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('catalog') }}" class="hover:text-primary">Katalog</a></li>
                <li aria-hidden="true">/</li>
                <li class="truncate text-neutral">{{ $book->title }}</li>
            </ol>
        </nav>

        <div class="grid gap-8 lg:grid-cols-[18rem_1fr] lg:gap-12">

            {{-- Sampul --}}
            <div>
                <div class="mx-auto max-w-xs overflow-hidden rounded-box border border-base-300 lg:max-w-none">
                    <x-book.cover :book="$book" />
                </div>
            </div>

            {{-- Informasi --}}
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-badge.book-type :book="$book" />
                    <a href="{{ route('catalog', ['category' => $book->category->slug]) }}"
                       class="text-sm font-medium text-primary hover:underline">{{ $book->category->name }}</a>
                </div>

                <h1 class="mt-3 text-3xl font-semibold leading-tight text-neutral sm:text-4xl">{{ $book->title }}</h1>
                <p class="mt-2 text-lg text-base-content/70">{{ $book->author }}</p>

                {{-- Rincian --}}
                <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-4 rounded-box border border-base-300 p-5 sm:grid-cols-3">
                    @foreach ($info as [$label, $value])
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-base-content/50">{{ $label }}</dt>
                            <dd class="mt-1 font-medium text-neutral">{{ filled($value) ? $value : '-' }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Status ketersediaan --}}
                @if ($book->isDigital())
                    @if ($canRead)
                        <div class="mt-6 flex items-start gap-4 rounded-box bg-primary/5 p-5">
                            @svg('heroicon-o-device-tablet', 'size-8 shrink-0 text-primary')
                            <div>
                                <p class="font-semibold text-neutral">Tersedia untuk dibaca online</p>
                                <p class="mt-0.5 text-sm text-base-content/70">
                                    Buka langsung di browser. Buku digital hanya bisa dibaca di layar dan tidak dapat diunduh.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 flex items-start gap-4 rounded-box bg-warning/10 p-5">
                            @svg('heroicon-o-exclamation-triangle', 'size-8 shrink-0 text-warning')
                            <div>
                                <p class="font-semibold text-neutral">File buku digital belum tersedia</p>
                                <p class="mt-0.5 text-sm text-base-content/70">Pustakawan belum mengunggah file untuk buku ini.</p>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="mt-6 rounded-box p-5 {{ $toneBg }}">
                        <div class="flex items-start gap-4">
                            @svg('heroicon-o-'.$toneIcon, 'size-8 shrink-0 '.$toneText)
                            <div class="min-w-0 flex-1">
                                <p class="text-lg font-semibold text-neutral">
                                    {{ $borrowable > 0 ? $borrowable.' eksemplar tersedia' : 'Stok sedang habis' }}
                                </p>
                                <p class="mt-0.5 text-sm text-base-content/70">
                                    Dari {{ $book->stock_total }} eksemplar
                                    @if ($pending > 0)
                                        &middot; {{ $pending }} pengajuan menunggu diambil
                                    @endif
                                </p>
                                <progress class="progress {{ $toneBar }} mt-3 w-full"
                                          value="{{ $borrowable }}"
                                          max="{{ max($book->stock_total, 1) }}"></progress>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Tombol aksi --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($book->isDigital())
                        @if ($canRead)
                            <a href="{{ route('books.read', $book) }}" class="btn btn-primary btn-lg">
                                @svg('heroicon-o-eye', 'size-5')
                                Baca Sekarang
                            </a>
                        @else
                            <button type="button" class="btn btn-lg" disabled>Belum tersedia</button>
                        @endif
                    @else
                        @if ($borrowable > 0)
                            <button type="button"
                                    onclick="document.getElementById('loan-modal').showModal()"
                                    class="btn btn-primary btn-lg">
                                @svg('heroicon-o-clipboard-document-list', 'size-5')
                                Pinjam Buku
                            </button>
                        @else
                            <button type="button" class="btn btn-lg" disabled>Stok Habis</button>
                        @endif
                    @endif

                    <a href="{{ route('catalog') }}" class="btn btn-lg border-base-300 bg-base-100 text-neutral">Kembali ke katalog</a>
                </div>
            </div>
        </div>

        {{-- Sinopsis --}}
        <section class="mt-12 max-w-3xl">
            <h2 class="text-2xl font-semibold text-neutral">Sinopsis</h2>
            <p class="mt-3 whitespace-pre-line leading-relaxed text-base-content/80">{{ $book->synopsis ?: 'Belum ada sinopsis untuk buku ini.' }}</p>
        </section>

        {{-- Buku terkait --}}
        @if ($related->isNotEmpty())
            <section class="mt-14">
                <x-section-heading
                    title="Buku lain di kategori ini"
                    :url="route('catalog', ['category' => $book->category->slug])"
                    urlLabel="Lihat kategori" />

                <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-book.card :book="$item" />
                    @endforeach
                </div>
            </section>
        @endif

    </x-container>

    {{-- ===== Modal formulir peminjaman (Part 11) ===== --}}
    @if ($book->isPhysical() && $borrowable > 0)
        <dialog id="loan-modal" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <form method="dialog">
                    <button class="btn btn-circle btn-ghost btn-sm absolute right-3 top-3" aria-label="Tutup">
                        @svg('heroicon-o-x-mark', 'size-5')
                    </button>
                </form>

                <h3 class="text-xl font-semibold text-neutral">Pinjam buku</h3>
                <p class="mt-1 text-sm text-base-content/70">{{ $book->title }}</p>

                <form method="POST" action="{{ route('books.loans.store', $book) }}" class="mt-5 space-y-4">
                    @csrf

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Nama lengkap</legend>
                        <input type="text" name="borrower_name" value="{{ old('borrower_name') }}"
                               required minlength="3" maxlength="100" autocomplete="name"
                               placeholder="Contoh: Ahmad Fauzi"
                               class="input w-full @error('borrower_name') input-error @enderror">
                        @error('borrower_name') <p class="label text-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Kelas / Jabatan</legend>
                        <input type="text" name="borrower_class" value="{{ old('borrower_class') }}"
                               required maxlength="50"
                               placeholder="Contoh: XI TKJ 1 atau Guru"
                               class="input w-full @error('borrower_class') input-error @enderror">
                        @error('borrower_class') <p class="label text-error">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Tanggal pinjam</legend>
                            <input type="date" name="loan_date"
                                   value="{{ old('loan_date', today()->toDateString()) }}"
                                   min="{{ today()->toDateString() }}"
                                   max="{{ today()->addDays(7)->toDateString() }}"
                                   required
                                   class="input w-full @error('loan_date') input-error @enderror">
                            @error('loan_date') <p class="label text-error">{{ $message }}</p> @enderror
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Durasi pinjam</legend>
                            <select name="duration_days" required class="select w-full @error('duration_days') select-error @enderror">
                                @foreach (\App\Models\Loan::DURATIONS as $d)
                                    <option value="{{ $d }}" @selected((int) old('duration_days', \App\Models\Loan::DEFAULT_DURATION) === $d)>
                                        {{ $d }} hari{{ $d === \App\Models\Loan::DEFAULT_DURATION ? ' (standar)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('duration_days') <p class="label text-error">{{ $message }}</p> @enderror
                        </fieldset>
                    </div>

                    <p class="rounded-lg bg-base-200 p-3 text-sm text-base-content/70">
                        Setelah mengirim, datang ke meja pustakawan untuk mengambil buku. Batas pengembalian dihitung dari tanggal buku diambil.
                    </p>

                    <div class="modal-action mt-2">
                        <button type="button" class="btn" onclick="this.closest('dialog').close()">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            @svg('heroicon-o-paper-airplane', 'size-5')
                            Kirim pengajuan
                        </button>
                    </div>
                </form>
            </div>

            <form method="dialog" class="modal-backdrop"><button aria-label="Tutup">tutup</button></form>
        </dialog>

        {{-- Buka kembali modal bila validasi gagal --}}
        @push('scripts')
            @if ($errors->any())
                <script>document.getElementById('loan-modal')?.showModal();</script>
            @endif
        @endpush
    @endif
</x-layouts.public>