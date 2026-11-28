<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookReaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\ReportController;

/* ---------- Publik ---------- */

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [BookController::class, 'index'])->name('catalog');
Route::get('/buku/{book}', [BookController::class, 'show'])->name('books.show');

Route::get('/buku/{book}/baca', [BookReaderController::class, 'show'])->name('books.read');
Route::get('/buku/{book}/berkas', [BookReaderController::class, 'pdf'])
    ->middleware('signed')
    ->name('books.pdf');

Route::post('/buku/{book}/pinjam', [LoanController::class, 'store'])
    ->middleware('throttle:15,1')
    ->name('books.loans.store');

/* ---------- Admin ---------- */

Route::redirect('/admin', '/admin/dashboard');

Route::prefix('admin')->name('admin.')->middleware('no.store')->group(function () {

    // Hanya untuk tamu (belum login)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('login.store');
    });

    // Hanya untuk admin yang sudah login
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Katalog buku
        Route::patch('buku/{book}/rekomendasi', [AdminBookController::class, 'toggleFeatured'])
            ->name('books.featured');
        Route::resource('buku', AdminBookController::class)
            ->names('books')
            ->parameters(['buku' => 'book'])
            ->except('show');

        // Sirkulasi peminjaman
        Route::get('peminjaman', [AdminLoanController::class, 'index'])->name('loans.index');
        Route::get('peminjaman/langsung', [AdminLoanController::class, 'create'])->name('loans.create');
        Route::post('peminjaman/langsung', [AdminLoanController::class, 'store'])->name('loans.store');
        Route::patch('peminjaman/{loan}/konfirmasi', [AdminLoanController::class, 'confirm'])->name('loans.confirm');
        Route::patch('peminjaman/{loan}/batal', [AdminLoanController::class, 'cancel'])->name('loans.cancel');

        // Pengembalian
        Route::get('pengembalian', [ReturnController::class, 'index'])->name('returns.index');
        Route::patch('pengembalian/{loan}', [ReturnController::class, 'update'])->name('returns.update');

                // Laporan
        Route::get('laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('laporan/cetak', [ReportController::class, 'print'])->name('reports.print');
        Route::get('laporan/ekspor', [ReportController::class, 'export'])->name('reports.export');
    });
});