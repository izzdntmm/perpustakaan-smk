<?php

namespace App\Models;

use App\Enums\LoanSource;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'book_id',
        'borrower_name',
        'borrower_class',
        'status',
        'source',
        'loan_date',
        'duration_days',
        'due_date',
        'borrowed_at',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'source' => LoanSource::class,
            'loan_date' => 'date',
            'due_date' => 'date',
            'borrowed_at' => 'datetime',
            'returned_at' => 'datetime',
            'duration_days' => 'integer',
        ];
    }

    /* ---------- Relasi ---------- */

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /* ---------- Scope ---------- */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LoanStatus::Pending);
    }

    public function scopeBorrowed(Builder $query): Builder
    {
        return $query->where('status', LoanStatus::Borrowed);
    }

    /**
     * Terlambat = sedang dipinjam dan sudah lewat jatuh tempo.
     * Tidak disimpan di database, selalu dihitung.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', LoanStatus::Borrowed)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        // Kode pengajuan, misalnya PJ-00012
        if (preg_match('/^PJ-?0*(\d+)$/i', $term, $m)) {
            return $query->whereKey((int) $m[1]);
        }

        foreach (collect(preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY))->take(5) as $word) {
            $like = '%' . addcslashes($word, '\\%_') . '%';

            $query->where(function (Builder $q) use ($like) {
                $q->where('borrower_name', 'like', $like)
                    ->orWhere('borrower_class', 'like', $like)
                    ->orWhereHas('book', fn(Builder $b) => $b->where('title', 'like', $like));
            });
        }

        return $query;
    }

    /* ---------- Helper ---------- */

    public function isOverdue(): bool
    {
        return $this->status === LoanStatus::Borrowed
            && $this->due_date !== null
            && $this->due_date->lt(today());
    }

    public function daysOverdue(): int
    {
        return $this->isOverdue() ? (int) $this->due_date->diffInDays(today()) : 0;
    }

    public const DURATIONS = [3, 7, 14];
    public const DEFAULT_DURATION = 7;

    public function code(): string
    {
        return sprintf('PJ-%05d', $this->id);
    }

    /**
     * Jumlah hari terlambat.
     * Dipinjam: dihitung sampai hari ini. Dikembalikan: sampai tanggal kembali.
     */
    public function lateDays(): int
    {
        if ($this->due_date === null) {
            return 0;
        }

        $end = match ($this->status) {
            LoanStatus::Returned => $this->returned_at?->copy()->startOfDay(),
            LoanStatus::Borrowed => today(),
            default => null,
        };

        if ($end === null || $end->lte($this->due_date)) {
            return 0;
        }

        return (int) $this->due_date->diffInDays($end);
    }
}