<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::with('category')
            ->search($request->query('q'))
            ->ofType($request->query('type'))
            ->inCategory($request->query('category'))
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('catalog.index', compact('books'));
    }
}