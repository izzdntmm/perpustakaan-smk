<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookReaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

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

        // Part 14 sampai 17 menambah route di sini:
        // admin.books.*, admin.loans.*, admin.returns.*, admin.reports.*
    });
});