<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookType;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'titles' => Book::count(),
            'copies' => (int) Book::where('type', BookType::Physical->value)->sum('stock_total'),
            'borrowed' => Loan::borrowed()->count(),
            'overdue' => Loan::overdue()->count(),
            'pending' => Loan::pending()->count(),
        ];

        $recentLoans = Loan::with('book')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        // Paling lama terlambat tampil paling atas
        $overdueLoans = Loan::overdue()
            ->with('book')
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $activities = $this->recentActivity();

        return view('admin.dashboard', compact('stats', 'recentLoans', 'overdueLoans', 'activities'));
    }

    /**
     * Susun aktivitas terbaru dari kolom waktu yang sudah ada,
     * jadi tidak perlu tabel log tersendiri.
     */
    private function recentActivity(): Collection
    {
        $loanEvents = fn (string $column, string $type, string $title) => Loan::with('book')
            ->whereNotNull($column)
            ->orderByDesc($column)
            ->take(5)
            ->get()
            ->map(fn (Loan $loan) => [
                'type' => $type,
                'title' => $title,
                'detail' => $loan->book->title,
                'at' => $loan->{$column},
            ]);

        $bookUpdates = Book::orderByDesc('updated_at')
            ->take(5)
            ->get()
            ->map(fn (Book $book) => [
                'type' => 'book',
                'title' => 'Data buku diperbarui',
                'detail' => $book->title,
                'at' => $book->updated_at,
            ]);

        return collect()
            ->concat($loanEvents('created_at', 'request', 'Pengajuan baru'))
            ->concat($loanEvents('borrowed_at', 'borrowed', 'Buku diambil peminjam'))
            ->concat($loanEvents('returned_at', 'returned', 'Pengembalian buku'))
            ->concat($bookUpdates)
            ->sortByDesc(fn (array $event) => $event['at']->getTimestamp())
            ->take(6)
            ->values();
    }
}