<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public const STATUSES = [
        'all' => 'Semua status',
        'pending' => 'Menunggu pengambilan',
        'borrowed' => 'Sedang dipinjam',
        'overdue' => 'Terlambat',
        'returned' => 'Dikembalikan',
        'cancelled' => 'Dibatalkan',
    ];

    /* ---------- Periode ---------- */

    /** Ubah input form menjadi rentang tanggal dan label. */
    public function period(array $in): array
    {
        $mode = $in['periode'] ?? 'bulan';

        if ($mode === 'semester') {
            $ta = (int) ($in['ta'] ?? $this->defaultAcademicYear());
            $semester = $in['semester'] ?? $this->defaultSemester();

            [$from, $to] = $semester === 'genap'
                ? [Carbon::create($ta + 1, 1, 1), Carbon::create($ta + 1, 6, 30)]
                : [Carbon::create($ta, 7, 1), Carbon::create($ta, 12, 31)];

            $label = 'Semester '.($semester === 'genap' ? 'Genap' : 'Ganjil')
                .' Tahun Ajaran '.$ta.'/'.($ta + 1);
        } elseif ($mode === 'rentang') {
            $from = isset($in['dari']) ? Carbon::createFromFormat('!Y-m-d', $in['dari']) : now()->startOfMonth();
            $to = isset($in['sampai']) ? Carbon::createFromFormat('!Y-m-d', $in['sampai']) : today();

            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }

            $label = $from->translatedFormat('d F Y').' sampai '.$to->translatedFormat('d F Y');
        } else {
            // "!" mengatur hari ke tanggal 1, supaya tidak meloncat bulan di akhir bulan
            $month = isset($in['bulan']) ? Carbon::createFromFormat('!Y-m', $in['bulan']) : now();
            $from = $month->copy()->startOfMonth();
            $to = $month->copy()->endOfMonth();
            $label = 'Bulan '.$from->translatedFormat('F Y');
        }

        return [
            'mode' => $mode,
            'from' => $from->copy()->startOfDay(),
            'to' => $to->copy()->endOfDay(),
            'label' => $label,
        ];
    }

    public function defaultAcademicYear(): int
    {
        return now()->month >= 7 ? now()->year : now()->year - 1;
    }

    public function defaultSemester(): string
    {
        return now()->month >= 7 ? 'ganjil' : 'genap';
    }

    /** Daftar tahun ajaran untuk pilihan di form (terbaru di atas). */
    public function academicYears(): array
    {
        return range($this->defaultAcademicYear() + 1, $this->defaultAcademicYear() - 4);
    }

    /* ---------- Riwayat peminjaman ---------- */

    public function loans(Carbon $from, Carbon $to, string $status = 'all'): Builder
    {
        $query = Loan::with('book')
            ->whereBetween('loan_date', [$from->toDateString(), $to->toDateString()]);

        if ($status === 'overdue') {
            $query->overdue();
        } elseif (in_array($status, ['pending', 'borrowed', 'returned', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        return $query->orderBy('loan_date')->orderBy('id');
    }

    public function loanSummary(Carbon $from, Carbon $to): array
    {
        $range = [$from->toDateString(), $to->toDateString()];
        $base = fn () => Loan::whereBetween('loan_date', $range);

        $byStatus = $base()->toBase()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'pending' => (int) ($byStatus['pending'] ?? 0),
            'borrowed' => (int) ($byStatus['borrowed'] ?? 0),
            'returned' => (int) ($byStatus['returned'] ?? 0),
            'cancelled' => (int) ($byStatus['cancelled'] ?? 0),
            // Belum kembali dan sudah lewat jatuh tempo
            'overdue_now' => $base()->overdue()->count(),
            // Sudah kembali tetapi melewati jatuh tempo
            'returned_late' => $base()
                ->where('status', 'returned')
                ->whereNotNull('due_date')
                ->whereRaw('DATE(returned_at) > due_date')
                ->count(),
        ];
    }

    /** Buku yang paling sering dipinjam (pengajuan yang dibatalkan tidak dihitung). */
    public function topBooks(Carbon $from, Carbon $to, int $limit = 5): Collection
    {
        return Loan::query()
            ->join('books', 'books.id', '=', 'loans.book_id')
            ->whereBetween('loans.loan_date', [$from->toDateString(), $to->toDateString()])
            ->where('loans.status', '!=', 'cancelled')
            ->selectRaw('books.title, books.author, COUNT(*) as total')
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('total')
            ->orderBy('books.title')
            ->limit($limit)
            ->toBase()
            ->get();
    }

    /* ---------- Daftar buku ---------- */

    public function books(?string $categorySlug, ?string $type): Builder
    {
        return Book::with('category')->inCategory($categorySlug)->ofType($type);
    }

    public function bookSummary(?string $categorySlug, ?string $type): array
    {
        $row = Book::query()
            ->inCategory($categorySlug)
            ->ofType($type)
            ->toBase()
            ->selectRaw("
                COUNT(*) AS titles,
                SUM(CASE WHEN type = 'digital' THEN 1 ELSE 0 END) AS digital,
                SUM(CASE WHEN type = 'physical' THEN stock_total ELSE 0 END) AS copies,
                SUM(CASE WHEN type = 'physical' THEN stock_available ELSE 0 END) AS available
            ")
            ->first();

        $copies = (int) ($row->copies ?? 0);
        $available = (int) ($row->available ?? 0);

        return [
            'titles' => (int) ($row->titles ?? 0),
            'digital' => (int) ($row->digital ?? 0),
            'copies' => $copies,
            'available' => $available,
            'borrowed' => $copies - $available,
        ];
    }
}