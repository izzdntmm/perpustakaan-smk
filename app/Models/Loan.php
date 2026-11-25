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
}