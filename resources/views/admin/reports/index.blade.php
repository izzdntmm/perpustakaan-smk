@php
    $isLoans = $jenis === 'peminjaman';
    $tabUrl = fn (string $j) => route('admin.reports.index', ['jenis' => $j]);
    $statuses = \App\Services\ReportService::STATUSES;
    $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
@endphp

<x-layouts.admin title="Laporan">

    {{-- Jenis laporan --}}
    <div role="tablist" class="tabs tabs-border overflow-x-auto">
        <a role="tab" href="{{ $tabUrl('peminjaman') }}"
           @class(['tab shrink-0 gap-2 whitespace-nowrap', 'tab-active font-semibold' => $isLoans])>
            @svg('heroicon-o-clipboard-document-list', 'size-4')
            Riwayat peminjaman
        </a>
        <a role="tab" href="{{ $tabUrl('buku') }}"
           @class(['tab shrink-0 gap-2 whitespace-nowrap', 'tab-active font-semibold' => ! $isLoans])>
            @svg('heroicon-o-book-open', 'size-4')
            Daftar buku
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.reports.index') }}"
          class="mt-4 rounded-box border border-base-300 bg-base-100 p-5">
        <input type="hidden" name="jenis" value="{{ $jenis }}">

        @if ($isLoans)
            <div class="grid gap-x-4 gap-y-2 sm:grid-cols-2 lg:grid-cols-4">
                <x-admin.field name="periode" label="Periode">
                    <select name="periode" id="periode" class="select w-full">
                        <option value="bulan" @selected($form['periode'] === 'bulan')>Bulanan</option>
                        <option value="semester" @selected($form['periode'] === 'semester')>Semester</option>
                        <option value="rentang" @selected($form['periode'] === 'rentang')>Rentang tanggal</option>
                    </select>
                </x-admin.field>

                <div data-period="bulan" class="lg:col-span-2" @if ($form['periode'] !== 'bulan') hidden @endif>
                    <x-admin.field name="bulan" label="Bulan">
                        <input type="month" name="bulan" value="{{ $form['bulan'] }}" class="input w-full">
                    </x-admin.field>
                </div>

                <div data-period="semester" class="grid grid-cols-2 gap-4 lg:col-span-2" @if ($form['periode'] !== 'semester') hidden @endif>
                    <x-admin.field name="ta" label="Tahun ajaran">
                        <select name="ta" class="select w-full">
                            @foreach ($academicYears as $y)
                                <option value="{{ $y }}" @selected($form['ta'] === $y)>{{ $y }}/{{ $y + 1 }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                    <x-admin.field name="semester" label="Semester">
                        <select name="semester" class="select w-full">
                            <option value="ganjil" @selected($form['semester'] === 'ganjil')>Ganjil (Jul - Des)</option>
                            <option value="genap" @selected($form['semester'] === 'genap')>Genap (Jan - Jun)</option>
                        </select>
                    </x-admin.field>
                </div>

                <div data-period="rentang" class="grid grid-cols-2 gap-4 lg:col-span-2" @if ($form['periode'] !== 'rentang') hidden @endif>
                    <x-admin.field name="dari" label="Dari tanggal">
                        <input type="date" name="dari" value="{{ $form['dari'] }}" class="input w-full">
                    </x-admin.field>
                    <x-admin.field name="sampai" label="Sampai tanggal">
                        <input type="date" name="sampai" value="{{ $form['sampai'] }}" class="input w-full">
                    </x-admin.field>
                </div>

                <x-admin.field name="status" label="Status">
                    <select name="status" class="select w-full">
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected($form['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>
        @else
            <div class="grid gap-x-4 gap-y-2 sm:grid-cols-2 lg:grid-cols-4">
                <x-admin.field name="kategori" label="Kategori">
                    <select name="kategori" class="select w-full">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($form['kategori'] === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>

                <x-admin.field name="tipe" label="Jenis buku">
                    <select name="tipe" class="select w-full">
                        <option value="">Semua jenis</option>
                        <option value="physical" @selected($form['tipe'] === 'physical')>Fisik</option>
                        <option value="digital" @selected($form['tipe'] === 'digital')>Digital</option>
                    </select>
                </x-admin.field>
            </div>
        @endif

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                @svg('heroicon-o-funnel', 'size-5')
                Tampilkan
            </button>
        </div>
    </form>

    {{-- Hasil --}}
    <section class="mt-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-neutral">{{ $title }}</h2>
                <p class="mt-1 text-base-content/70">{{ $subtitle }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.reports.print', $params) }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                    @svg('heroicon-o-printer', 'size-4')
                    Cetak / Simpan PDF
                </a>
                <a href="{{ route('admin.reports.export', $params) }}" class="btn btn-sm">
                    @svg('heroicon-o-arrow-down-tray', 'size-4')
                    Ekspor CSV
                </a>
            </div>
        </div>

        {{-- Ringkasan --}}
        @if ($isLoans)
            <div class="mt-5 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
                <x-admin.stat-card icon="rectangle-stack" tone="primary" label="Total transaksi" :value="$summary['total']" caption="Pada periode ini" />
                <x-admin.stat-card icon="clock" tone="secondary" label="Menunggu pengambilan" :value="$summary['pending']" />
                <x-admin.stat-card icon="book-open" tone="secondary" label="Sedang dipinjam" :value="$summary['borrowed']" />
                <x-admin.stat-card icon="arrow-uturn-left" tone="accent" label="Dikembalikan" :value="$summary['returned']" />
                <x-admin.stat-card icon="x-circle" tone="primary" label="Dibatalkan" :value="$summary['cancelled']" />
                <x-admin.stat-card icon="exclamation-triangle" tone="error" label="Terlambat"
                    :value="$summary['overdue_now'] + $summary['returned_late']"
                    :caption="$summary['overdue_now'].' belum kembali, '.$summary['returned_late'].' sudah kembali'" />
            </div>

            @if ($topBooks->isNotEmpty())
                <div class="mt-4 rounded-box border border-base-300 bg-base-100 p-5">
                    <h3 class="text-lg font-semibold text-neutral">Buku paling sering dipinjam</h3>
                    <ol class="mt-3 divide-y divide-base-300">
                        @foreach ($topBooks as $i => $top)
                            <li class="flex items-center gap-3 py-2.5">
                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-primary/10 text-sm font-bold text-primary">{{ $i + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-neutral">{{ $top->title }}</p>
                                    <p class="truncate text-xs text-base-content/70">{{ $top->author }}</p>
                                </div>
                                <span class="shrink-0 text-sm font-semibold text-neutral">{{ $top->total }}x</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        @else
            <div class="mt-5 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <x-admin.stat-card icon="rectangle-stack" tone="primary" label="Total judul" :value="$summary['titles']" :caption="$summary['digital'].' judul digital'" />
                <x-admin.stat-card icon="archive-box" tone="accent" label="Total eksemplar fisik" :value="$summary['copies']" />
                <x-admin.stat-card icon="check-circle" tone="accent" label="Tersedia di rak" :value="$summary['available']" />
                <x-admin.stat-card icon="book-open" tone="secondary" label="Sedang dipinjam" :value="$summary['borrowed']" />
            </div>
        @endif

        @if ($truncated)
            <div role="alert" class="alert alert-warning mt-4">
                @svg('heroicon-o-exclamation-triangle', 'size-5 shrink-0')
                <span>Menampilkan {{ $fmt($limit) }} dari {{ $fmt($total) }} data. Gunakan Ekspor CSV untuk data lengkap.</span>
            </div>
        @endif

        {{-- Tabel --}}
        <div class="mt-4 rounded-box border border-base-300 bg-base-100">
            @if ($rows->isEmpty())
                <x-empty-state icon="document-chart-bar" title="Tidak ada data"
                               message="Tidak ada data yang cocok dengan pilihan laporan ini." class="border-0" />
            @elseif ($isLoans)
                <div class="overflow-x-auto p-2 sm:p-3">
                    <table class="table">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-base-content/70">
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Peminjam</th>
                                <th>Buku</th>
                                <th>Jatuh tempo</th>
                                <th>Kembali</th>
                                <th>Status</th>
                                <th class="text-right">Terlambat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $loan)
                                <tr @class(['bg-error/5' => $loan->isOverdue()])>
                                    <td class="text-base-content/70">{{ $i + 1 }}</td>
                                    <td class="whitespace-nowrap text-sm">{{ $loan->loan_date->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <p class="font-semibold text-neutral">{{ $loan->borrower_name }}</p>
                                        <p class="text-xs text-base-content/70">{{ $loan->borrower_class }} &middot; {{ $loan->code() }}</p>
                                    </td>
                                    <td><p class="line-clamp-2 max-w-56 text-neutral">{{ $loan->book->title }}</p></td>
                                    <td class="whitespace-nowrap text-sm">{{ $loan->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td class="whitespace-nowrap text-sm">{{ $loan->returned_at?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td><x-badge.loan-status :loan="$loan" /></td>
                                    <td class="whitespace-nowrap text-right text-sm">
                                        @if ($loan->lateDays() > 0)
                                            <span class="font-semibold text-error">{{ $loan->lateDays() }} hari</span>
                                        @else
                                            <span class="text-base-content/40">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="overflow-x-auto p-2 sm:p-3">
                    <table class="table">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-base-content/70">
                                <th>No</th>
                                <th>Buku</th>
                                <th>Kategori</th>
                                <th>Jenis</th>
                                <th>Rak</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Tersedia</th>
                                <th class="text-right">Dipinjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $book)
                                <tr>
                                    <td class="text-base-content/70">{{ $i + 1 }}</td>
                                    <td>
                                        <p class="line-clamp-2 max-w-64 font-semibold text-neutral">{{ $book->title }}</p>
                                        <p class="text-xs text-base-content/70">{{ $book->author }}{{ $book->year_published ? ' · '.$book->year_published : '' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap">{{ $book->category->name }}</td>
                                    <td><x-badge.book-type :book="$book" /></td>
                                    <td class="whitespace-nowrap">{{ $book->shelf_location ?: '-' }}</td>
                                    @if ($book->isPhysical())
                                        <td class="text-right">{{ $book->stock_total }}</td>
                                        <td @class(['text-right', 'font-semibold text-error' => $book->stock_available === 0])>{{ $book->stock_available }}</td>
                                        <td class="text-right">{{ $book->stock_total - $book->stock_available }}</td>
                                    @else
                                        <td colspan="3" class="text-right text-base-content/50">Baca online</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
        <script>
            const periode = document.getElementById('periode');
            if (periode) {
                const sync = () => document.querySelectorAll('[data-period]').forEach((el) => {
                    el.hidden = el.dataset.period !== periode.value;
                });
                periode.addEventListener('change', sync);
                sync();
            }
        </script>
    @endpush

</x-layouts.admin>