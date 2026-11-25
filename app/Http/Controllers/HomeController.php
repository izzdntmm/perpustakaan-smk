<?php

namespace App\Http\Controllers;

use App\Enums\BookType;
use App\Models\Book;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $stats = [
            'books' => Book::count(),
            'digital' => Book::where('type', BookType::Digital->value)->count(),
            'categories' => Category::count(),
        ];

        $categories = Category::withCount('books')->orderBy('id')->get();

        $featured = Book::with('category')
            ->featured()
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact('stats', 'categories', 'featured'));
    }
}