<?php

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Support\Facades\Route;

// SEMENTARA: dipindahkan ke controller di Part 7
Route::get('/', function () {
    $books = Book::with('category')->latest()->get();

    return view('home', compact('books'));
})->name('home');

// SEMENTARA: dihapus di Part 12
Route::get('/admin-preview', function () {
    $loans = Loan::with('book')->latest()->get();

    return view('admin-preview', compact('loans'));
});