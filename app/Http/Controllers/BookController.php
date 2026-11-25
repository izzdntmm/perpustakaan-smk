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