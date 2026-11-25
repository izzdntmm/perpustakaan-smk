<?php

namespace App\Models;

use App\Enums\BookType;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'publisher',
        'year_published',
        'isbn',
        'synopsis',
        'cover_path',
        'type',
        'pdf_path',
        'stock_total',
        'stock_available',
        'shelf_location',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'type' => BookType::class,
            'year_published' => 'integer',
            'stock_total' => 'integer',
            'stock_available' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    /* ---------- Boot: slug otomatis ---------- */

    protected static function booted(): void
    {
        static::saving(function (Book $book) {
            if (empty($book->slug) || $book->isDirty('title') && !$book->isDirty('slug')) {
                $book->slug = static::makeUniqueSlug($book->title, $book->id);
            }
        });
    }

    protected static function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------- Relasi ---------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /* ---------- Scope (dipakai di pencarian & filter) ---------- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        // Pecah jadi kata-kata (maks. 5). Setiap kata harus cocok
        // di judul ATAU penulis, sehingga urutan kata tidak penting.
        $words = collect(preg_split('/\s+/', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY))
            ->take(5);

        foreach ($words as $word) {
            // Escape karakter wildcard agar "50%" atau "a_b" dicari apa adanya
            $like = '%' . addcslashes($word, '\\%_') . '%';

            $query->where(function (Builder $q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('author', 'like', $like);
            });
        }

        return $query;
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        $enum = BookType::tryFrom((string) $type);

        return $enum ? $query->where('type', $enum->value) : $query;
    }

    public function scopeInCategory(Builder $query, ?string $categorySlug): Builder
    {
        if (blank($categorySlug)) {
            return $query;
        }

        return $query->whereHas('category', fn(Builder $q) => $q->where('slug', $categorySlug));
    }

    /* ---------- Helper ---------- */

    public function isDigital(): bool
    {
        return $this->type === BookType::Digital;
    }

    public function isPhysical(): bool
    {
        return $this->type === BookType::Physical;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path
            ? Storage::disk('public')->url($this->cover_path)
            : null;
    }

    public function hasPdf(): bool
    {
        return $this->isDigital()
            && filled($this->pdf_path)
            && Storage::disk('local')->exists($this->pdf_path);
    }

    /**
     * Jumlah pengajuan yang masih menunggu diambil.
     */
    public function pendingLoansCount(): int
    {
        return $this->loans()->where('status', LoanStatus::Pending)->count();
    }

    /**
     * Sisa stok yang masih bisa diajukan (stok rak dikurangi pengajuan pending).
     */
    public function borrowableStock(): int
    {
        if (!$this->isPhysical()) {
            return 0;
        }

        return max(0, $this->stock_available - $this->pendingLoansCount());
    }
}