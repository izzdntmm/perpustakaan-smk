<?php

namespace App\Enums;

enum LoanSource: string
{
    case Online = 'online';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Pengajuan Online',
            self::WalkIn => 'Langsung di Meja',
        };
    }
}