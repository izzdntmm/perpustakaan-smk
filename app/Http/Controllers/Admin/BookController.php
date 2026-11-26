<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookType;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $q = Str::of($this->stringParam($request, 'q'))->squish()->limit(100, '')->toString();
        $type = BookType::tryFrom($this->stringParam($request, 'type'));
        $category = Category::where('slug', $this->stringParam($request, 'category'))->first();

        $books = Book::with('category')
            ->search($q)
            ->ofType($type?->value)
            ->inCategory($category?->slug)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'categories' => Category::orderBy('id')->get(),
            'filters' => ['q' => $q, 'type' => $type?->value, 'category' => $category?->slug],
            'featuredCount' => Book::featured()->count(),
        ]);
    }

    public function create()
    {
        return view('admin.books.create', [
            'book' => new Book(['type' => BookType::Physical]),
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function store(BookRequest $request)
    {
        $type = BookType::from($request->validated('type'));
        $fields = $this->fields($request) + ['type' => $type];
        $uploaded = [];

        try {
            if ($request->hasFile('cover')) {
                $fields['cover_path'] = $this->putUpload($uploaded, 'public', 'covers', $request->file('cover'));
            }

            if ($type === BookType::Physical) {
                $total = (int) $request->validated('stock_total');

                $fields += [
                    'stock_total' => $total,
                    'stock_available' => $total,
                    'shelf_location' => $request->validated('shelf_location'),
                ];
            } else {
                $fields += ['stock_total' => 0, 'stock_available' => 0, 'shelf_location' => null];
                $fields['pdf_path'] = $this->putUpload($uploaded, 'local', 'books', $request->file('pdf'));
            }

            $book = Book::create($fields);
        } catch (Throwable $e) {
            $this->discard($uploaded);
            throw $e;
        }

        return redirect()->route('admin.books.index')
            ->with('success', "Buku \"{$book->title}\" berhasil ditambahkan.");
    }

    public function edit(Book $book)
    {
        return view('admin.books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function update(BookRequest $request, Book $book)
    {
        $fields = $this->fields($request);
        $oldCover = $book->cover_path;
        $oldPdf = $book->pdf_path;
        $uploaded = [];

        try {
            if ($request->hasFile('cover')) {
                $fields['cover_path'] = $this->putUpload($uploaded, 'public', 'covers', $request->file('cover'));
            } elseif ($request->boolean('remove_cover')) {
                $fields['cover_path'] = null;
            }

            if ($book->isDigital() && $request->hasFile('pdf')) {
                $fields['pdf_path'] = $this->putUpload($uploaded, 'local', 'books', $request->file('pdf'));
            }

            DB::transaction(function () use ($book, $request, &$fields) {
                // Kunci baris agar tidak bentrok dengan konfirmasi peminjaman yang bersamaan
                $locked = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();

                if ($locked->isPhysical()) {
                    $total = (int) $request->validated('stock_total');
                    $borrowed = $locked->stock_total - $locked->stock_available;

                    if ($total < $borrowed) {
                        throw ValidationException::withMessages([
                            'stock_total' => "Total eksemplar tidak boleh kurang dari jumlah yang sedang dipinjam ({$borrowed}).",
                        ]);
                    }

                    $fields['stock_total'] = $total;
                    $fields['stock_available'] = $total - $borrowed;
                    $fields['shelf_location'] = $request->validated('shelf_location');
                }

                $locked->update($fields);
            });
        } catch (Throwable $e) {
            $this->discard($uploaded);
            throw $e;
        }

        // Berkas lama dihapus hanya setelah penyimpanan berhasil
        if (array_key_exists('cover_path', $fields)) {
            $this->deleteIfUnused('public', 'cover_path', $oldCover);
        }
        if (array_key_exists('pdf_path', $fields)) {
            $this->deleteIfUnused('local', 'pdf_path', $oldPdf);
        }

        return redirect()->route('admin.books.index')
            ->with('success', "Buku \"{$request->validated('title')}\" berhasil diperbarui.");
    }

    public function destroy(Book $book)
    {
        if ($book->loans()->exists()) {
            return back()->with('error', "Buku \"{$book->title}\" memiliki riwayat peminjaman sehingga tidak dapat dihapus, agar laporan tetap utuh.");
        }

        $title = $book->title;
        $cover = $book->cover_path;
        $pdf = $book->pdf_path;

        $book->delete();

        $this->deleteIfUnused('public', 'cover_path', $cover);
        $this->deleteIfUnused('local', 'pdf_path', $pdf);

        return redirect()->route('admin.books.index')
            ->with('success', "Buku \"{$title}\" berhasil dihapus.");
    }

    public function toggleFeatured(Book $book)
    {
        $book->update(['is_featured' => ! $book->is_featured]);

        return back()->with('success', $book->is_featured
            ? "\"{$book->title}\" ditambahkan ke Buku Rekomendasi."
            : "\"{$book->title}\" dihapus dari Buku Rekomendasi.");
    }

    /* ---------- Helper ---------- */

    /** Kolom yang sama untuk buku fisik maupun digital. */
    private function fields(BookRequest $request): array
    {
        $v = $request->validated();

        return [
            'category_id' => $v['category_id'],
            'title' => $v['title'],
            'author' => $v['author'],
            'publisher' => $v['publisher'] ?? null,
            'year_published' => $v['year_published'] ?? null,
            'isbn' => $v['isbn'] ?? null,
            'synopsis' => $v['synopsis'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    /** Simpan berkas dengan nama acak, dan catat agar bisa dibersihkan bila gagal. */
    private function putUpload(array &$uploaded, string $disk, string $dir, UploadedFile $file): string
    {
        $path = Storage::disk($disk)->putFile($dir, $file);
        $uploaded[] = [$disk, $path];

        return $path;
    }

    private function discard(array $uploaded): void
    {
        foreach ($uploaded as [$disk, $path]) {
            Storage::disk($disk)->delete($path);
        }
    }

    /** Hapus berkas hanya bila tidak ada buku lain yang memakainya. */
    private function deleteIfUnused(string $disk, string $column, ?string $path): void
    {
        if (blank($path) || Book::where($column, $path)->exists()) {
            return;
        }

        Storage::disk($disk)->delete($path);
    }

    private function stringParam(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}