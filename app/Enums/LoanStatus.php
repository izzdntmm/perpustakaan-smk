<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Borrowed = 'borrowed';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pengambilan',
            self::Borrowed => 'Dipinjam',
            self::Returned => 'Dikembalikan',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning',
            self::Borrowed => 'badge-info',
            self::Returned => 'badge-success',
            self::Cancelled => 'badge-neutral',
        };
    }
}