<?php

namespace App\Enums;

/**
 * Status jadwal fasilitasi (`jadwal_fasilitasi.status`).
 */
enum StatusJadwal: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Closed => 'Closed',
        };
    }
}
