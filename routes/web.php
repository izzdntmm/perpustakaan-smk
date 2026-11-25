<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookReaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Models\Loan;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [BookController::class, 'index'])->name('catalog');

Route::get('/buku/{book}', [BookController::class, 'show'])->name('books.show');

// Pembaca digital
Route::get('/buku/{book}/baca', [BookReaderController::class, 'show'])->name('books.read');
Route::get('/buku/{book}/berkas', [BookReaderController::class, 'pdf'])
    ->middleware('signed')
    ->name('books.pdf');

// Pengajuan peminjaman (batas 15 kali per menit per IP; disesuaikan di Part 19)
Route::post('/buku/{book}/pinjam', [LoanController::class, 'store'])
    ->middleware('throttle:15,1')
    ->name('books.loans.store');

// SEMENTARA: dihapus di Part 12
Route::get('/admin-preview', function () {
    $loans = Loan::with('book')->latest()->get();

    return view('admin-preview', compact('loans'));
});