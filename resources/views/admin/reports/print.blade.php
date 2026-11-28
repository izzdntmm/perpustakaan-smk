@php
    $isLoans = $jenis === 'peminjaman';
    $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
    $cell = 'border border-neutral/50 px-2 py-1 align-top';
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => $title.' - '.$subtitle])
    <style>
        @page { size: A4 {{ $isLoans ? 'landscape' : 'portrait' }}; margin: 12mm; }
        @media print {
            body { background: #fff !important; }
            thead { display: table-header-group; }
            tr, .keep-together { break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-base-200 text-neutral antialiased print:bg-white">

    {{-- Toolbar (tidak ikut tercetak) --}}
    <div class="sticky top-0 z-10 border-b border-base-300 bg-base-100 print:hidden">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center gap-3 px-4 py-3">
            <a href="{{ route('admin.reports.index', $params) }}" class="btn btn-ghost btn-sm gap-1">
                @svg('heroicon-o-arrow-left', 'size-4')
                Kembali
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                @svg('heroicon-o-printer', 'size-4')
                Cetak
            </button>
            <p class="text-sm text-base-content/70">
                Untuk membuat PDF, pilih <strong>Simpan sebagai PDF</strong> pada dialog cetak.
            </p>
        </div>
    </div>

    <main class="mx-auto my-6 max-w-5xl bg-white p-8 shadow print:my-0 print:max-w-none print:p-0 print:shadow-none">

        {{-- Kop --}}
        <header class="border-b-2 border-neutral pb-3 text-center">
            <p class="font-heading text-xl font-bold uppercase tracking-wide">Perpustakaan SMK Roudlotul Mubtadiin</p>
            <p class="mt-1 text-sm">{{ $title }}</p>
            <p class="text-sm font-semibold">{{ $subtitle }}</p>
        </header>

        {{-- Ringkasan --}}
        <section class="keep-together mt-4">
            <h2 class="text-sm font-bold">Ringkasan</h2>
            <table class="mt-1 w-full border-collapse text-xs">
                @if ($isLoans)
                    <tr>
                        <td class="{{ $cell }}">Total transaksi: <strong>{{ $fmt($summary['total']) }}</strong></td>
                        <td class="{{ $cell }}">Menunggu: <strong>{{ $fmt($summary['pending']) }}</strong></td>
                        <td class="{{ $cell }}">Dipinjam: <strong>{{ $fmt($summary['borrowed']) }}</strong></td>
                        <td class="{{ $cell }}">Dikembalikan: <strong>{{ $fmt($summary['returned']) }}</strong></td>
                        <td class="{{ $cell }}">Dibatalkan: <strong>{{ $fmt($summary['cancelled']) }}</strong></td>
                        <td class="{{ $cell }}">
                            Terlambat: <strong>{{ $fmt($summary['overdue_now'] + $summary['returned_late']) }}</strong>
                            ({{ $summary['overdue_now'] }} belum kembali, {{ $summary['returned_late'] }} sudah kembali)
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="{{ $cell }}">Total judul: <strong>{{ $fmt($summary['titles']) }}</strong> ({{ $fmt($summary['digital']) }} digital)</td>
                        <td class="{{ $cell }}">Total eksemplar fisik: <strong>{{ $fmt($summary['copies']) }}</strong></td>
                        <td class="{{ $cell }}">Tersedia di rak: <strong>{{ $fmt($summary['available']) }}</strong></td>
                        <td class="{{ $cell }}">Sedang dipinjam: <strong>{{ $fmt($summary['borrowed']) }}</strong></td>
                    </tr>
                @endif
            </table>
        </section>

        {{-- Tabel data --}}
        <section class="mt-4">
            @if ($truncated)
                <p class="mb-2 text-xs italic">
                    Catatan: menampilkan {{ $fmt($limit) }} dari {{ $fmt($total) }} data. Gunakan ekspor CSV untuk data lengkap.
                </p>
            @endif

            @if ($rows->isEmpty())
                <p class="py-8 text-center text-sm">Tidak ada data pada pilihan laporan ini.</p>
            @elseif ($isLoans)
                <table class="w-full border-collapse text-xs">
                    <thead>
                        <tr class="font-bold">
                            <th class="{{ $cell }} w-8 text-left">No</th>
                            <th class="{{ $cell }} text-left">Kode</th>
                            <th class="{{ $cell }} text-left">Tgl Pinjam</th>
                            <th class="{{ $cell }} text-left">Peminjam</th>
                            <th class="{{ $cell }} text-left">Kelas / Jabatan</th>
                            <th class="{{ $cell }} text-left">Judul Buku</th>
                            <th class="{{ $cell }} text-left">Jatuh Tempo</th>
                            <th class="{{ $cell }} text-left">Kembali</th>
                            <th class="{{ $cell }} text-left">Status</th>
                            <th class="{{ $cell }} text-left">Terlambat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $loan)
                            <tr>
                                <td class="{{ $cell }}">{{ $i + 1 }}</td>
                                <td class="{{ $cell }} whitespace-nowrap">{{ $loan->code() }}</td>
                                <td class="{{ $cell }} whitespace-nowrap">{{ $loan->loan_date->format('d/m/Y') }}</td>
                                <td class="{{ $cell }}">{{ $loan->borrower_name }}</td>
                                <td class="{{ $cell }}">{{ $loan->borrower_class }}</td>
                                <td class="{{ $cell }}">{{ $loan->book->title }}</td>
                                <td class="{{ $cell }} whitespace-nowrap">{{ $loan->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="{{ $cell }} whitespace-nowrap">{{ $loan->returned_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="{{ $cell }}">{{ $loan->isOverdue() ? 'Terlambat' : $loan->status->label() }}</td>
                                <td class="{{ $cell }} whitespace-nowrap">{{ $loan->lateDays() > 0 ? $loan->lateDays().' hari' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <table class="w-full border-collapse text-xs">
                    <thead>
                        <tr class="font-bold">
                            <th class="{{ $cell }} w-8 text-left">No</th>
                            <th class="{{ $cell }} text-left">Judul</th>
                            <th class="{{ $cell }} text-left">Penulis</th>
                            <th class="{{ $cell }} text-left">Kategori</th>
                            <th class="{{ $cell }} text-left">Jenis</th>
                            <th class="{{ $cell }} text-left">Rak</th>
                            <th class="{{ $cell }} text-right">Total</th>
                            <th class="{{ $cell }} text-right">Tersedia</th>
                            <th class="{{ $cell }} text-right">Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $book)
                            <tr>
                                <td class="{{ $cell }}">{{ $i + 1 }}</td>
                                <td class="{{ $cell }}">{{ $book->title }}</td>
                                <td class="{{ $cell }}">{{ $book->author }}</td>
                                <td class="{{ $cell }}">{{ $book->category->name }}</td>
                                <td class="{{ $cell }}">{{ $book->type->label() }}</td>
                                <td class="{{ $cell }}">{{ $book->shelf_location ?: '-' }}</td>
                                @if ($book->isPhysical())
                                    <td class="{{ $cell }} text-right">{{ $book->stock_total }}</td>
                                    <td class="{{ $cell }} text-right">{{ $book->stock_available }}</td>
                                    <td class="{{ $cell }} text-right">{{ $book->stock_total - $book->stock_available }}</td>
                                @else
                                    <td class="{{ $cell }} text-right" colspan="3">Baca online</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        {{-- Tanda tangan --}}
        <section class="keep-together mt-8 grid grid-cols-2 gap-8 text-center text-sm">
            <div>
                <p>Mengetahui,</p>
                <p>Kepala Sekolah</p>
                <div class="h-20"></div>
                <p class="border-t border-neutral/60 pt-1">(............................................)</p>
            </div>
            <div>
                <p>&nbsp;</p>
                <p>Pustakawan</p>
                <div class="h-20"></div>
                <p class="border-t border-neutral/60 pt-1">{{ $printedBy }}</p>
            </div>
        </section>

        <p class="mt-6 text-[10px] text-neutral/70">
            Dicetak pada {{ $printedAt->translatedFormat('d F Y, H:i') }} oleh {{ $printedBy }}.
        </p>
    </main>
</body>
</html>