<?php

namespace App\Services;

use App\Enums\LoanSource;
use App\Enums\LoanStatus;
use App\Exceptions\CirculationException;
use App\Models\Book;
use App\Models\Loan;
use Closure;
use Illuminate\Support\Facades\DB;

class CirculationService
{
    /** Pengajuan online dikonfirmasi: buku diambil peminjam. */
    public function confirmPickup(Loan $loan): Loan
    {
        return $this->withLocks($loan, function (Loan $loan, Book $book) {
            if ($loan->status !== LoanStatus::Pending) {
                throw new CirculationException('Pengajuan ini sudah diproses sebelumnya.');
            }

            if ($book->stock_available < 1) {
                throw new CirculationException(
                    'Stok di rak sedang habis (semua eksemplar masih dipinjam). Tunggu pengembalian atau batalkan pengajuan ini.'
                );
            }

            $loan->update([
                'status' => LoanStatus::Borrowed,
                'borrowed_at' => now(),
                'due_date' => today()->addDays($loan->duration_days),
            ]);

            $this->adjustStock($book, -1);

            return $loan;
        });
    }

    /** Pengajuan dibatalkan (tidak jadi diambil). Stok rak tidak berubah. */
    public function cancel(Loan $loan): Loan
    {
        return $this->withLocks($loan, function (Loan $loan) {
            if ($loan->status !== LoanStatus::Pending) {
                throw new CirculationException('Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
            }

            $loan->update(['status' => LoanStatus::Cancelled]);

            return $loan;
        });
    }

    /** Buku dikembalikan: stok rak bertambah. */
    public function markReturned(Loan $loan): Loan
    {
        return $this->withLocks($loan, function (Loan $loan, Book $book) {
            if ($loan->status !== LoanStatus::Borrowed) {
                throw new CirculationException('Peminjaman ini tidak berstatus dipinjam, atau sudah dikembalikan.');
            }

            $loan->update([
                'status' => LoanStatus::Returned,
                'returned_at' => now(),
            ]);

            // Jangan sampai stok rak melebihi total eksemplar
            if ($book->stock_available < $book->stock_total) {
                $this->adjustStock($book, 1);
            }

            return $loan;
        });
    }

    /** Peminjaman langsung di meja pustakawan (tanpa pengajuan online). */
    public function walkIn(int $bookId, string $name, string $class, int $days): Loan
    {
        return DB::transaction(function () use ($bookId, $name, $class, $days) {
            $book = Book::whereKey($bookId)->lockForUpdate()->firstOrFail();

            if (! $book->isPhysical()) {
                throw new CirculationException('Hanya buku fisik yang bisa dipinjam.');
            }

            if ($book->borrowableStock() < 1) {
                throw new CirculationException(
                    $book->stock_available > 0
                        ? 'Eksemplar yang tersisa sudah dipesan lewat pengajuan online. Konfirmasi atau batalkan pengajuan tersebut terlebih dahulu.'
                        : 'Stok buku ini sedang habis.'
                );
            }

            $loan = $book->loans()->create([
                'borrower_name' => $name,
                'borrower_class' => $class,
                'status' => LoanStatus::Borrowed,
                'source' => LoanSource::WalkIn,
                'loan_date' => today(),
                'duration_days' => $days,
                'due_date' => today()->addDays($days),
                'borrowed_at' => now(),
            ]);

            $this->adjustStock($book, -1);

            return $loan;
        });
    }

    /* ---------- Internal ---------- */

    /**
     * Jalankan dalam transaksi dengan mengunci baris buku, lalu baris peminjaman.
     * Urutan kunci selalu sama agar tidak terjadi kebuntuan (deadlock).
     */
    private function withLocks(Loan $loan, Closure $callback): Loan
    {
        return DB::transaction(function () use ($loan, $callback) {
            $book = Book::whereKey($loan->book_id)->lockForUpdate()->firstOrFail();
            $fresh = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            return $callback($fresh, $book);
        });
    }

    /** Ubah stok rak lewat query dasar, sehingga updated_at buku tidak ikut berubah. */
    private function adjustStock(Book $book, int $delta): void
    {
        $query = Book::whereKey($book->id)->toBase();

        $delta > 0
            ? $query->increment('stock_available', $delta)
            : $query->decrement('stock_available', abs($delta));
    }
}