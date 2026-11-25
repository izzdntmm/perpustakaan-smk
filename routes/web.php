<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Models\Loan;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [BookController::class, 'index'])->name('catalog');

// SEMENTARA: dihapus di Part 12
Route::get('/admin-preview', function () {
    $loans = Loan::with('book')->latest()->get();

    return view('admin-preview', compact('loans'));
});