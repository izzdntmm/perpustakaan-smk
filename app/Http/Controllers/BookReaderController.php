<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class BookReaderController extends Controller
{
    /**
     * Halaman pembaca. Link ke file PDF dibuat bertanda tangan dan kedaluwarsa.
     */
    public function show(Book $book)
    {
        abort_unless($book->hasPdf(), 404);

        $pdfUrl = URL::temporarySignedRoute(
            'books.pdf',
            now()->addHours(4),
            ['book' => $book]
        );

        return view('books.read', compact('book', 'pdfUrl'));
    }

    /**
     * Mengalirkan file PDF dari penyimpanan privat.
     * Hanya bisa diakses dengan link bertanda tangan yang masih berlaku.
     */
    public function pdf(Book $book)
    {
        abort_unless($book->hasPdf(), 404);

        $response = response()->file(Storage::disk('local')->path($book->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="buku.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}