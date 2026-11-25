<?php

namespace App\Http\Controllers;

use App\Enums\BookType;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // Bersihkan input: hanya string, rapikan spasi, batasi panjang
        $q = Str::of($this->stringParam($request, 'q'))->squish()->limit(100, '')->toString();

        // Tipe dan kategori hanya diterima jika nilainya valid
        $type = BookType::tryFrom($this->stringParam($request, 'type'));
        $category = Category::where('slug', $this->stringParam($request, 'category'))->first();

        $books = Book::with('category')
            ->search($q)
            ->ofType($type?->value)
            ->inCategory($category?->slug)
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('id')->get();

        $filters = [
            'q' => $q,
            'type' => $type?->value,
            'category' => $category?->slug,
        ];

        return view('catalog.index', compact('books', 'categories', 'filters'));
    }

    public function show(Book $book)
    {
        $book->load('category');

        // Stok yang bisa diajukan = stok di rak dikurangi pengajuan yang menunggu diambil
        $pending = $book->isPhysical() ? $book->pendingLoansCount() : 0;
        $borrowable = $book->isPhysical()
            ? max(0, $book->stock_available - $pending)
            : 0;

        $canRead = $book->hasPdf();

        $related = Book::with('category')
            ->where('category_id', $book->category_id)
            ->whereKeyNot($book->id)
            ->latest()
            ->take(4)
            ->get();

        return view('books.show', compact('book', 'pending', 'borrowable', 'canRead', 'related'));
    }

    /**
     * Ambil parameter query sebagai string. Jika bukan string
     * (misalnya ?q[]=x), kembalikan string kosong.
     */
    private function stringParam(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}