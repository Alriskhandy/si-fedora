<?php

namespace App\Enums;

/**
 * Status dokumen tahapan (`dokumen_tahapan.status`).
 */
enum StatusDokumen: string
{
    case Menunggu = 'menunggu';
    case Diterima = 'diterima';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Diterima => 'Diterima',
            self::Ditolak => 'Ditolak',
        };
    }
}
