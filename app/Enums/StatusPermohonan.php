<?php

namespace App\Enums;

/**
 * Status proses permohonan (`permohonan.status_akhir`) dan tiap tahapannya (`permohonan_tahapan.status`).
 */
enum StatusPermohonan: string
{
    case Belum = 'belum';
    case Proses = 'proses';
    case Revisi = 'revisi';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Belum => 'Belum Dimulai',
            self::Proses => 'Dalam Proses',
            self::Revisi => 'Perlu Revisi',
            self::Selesai => 'Selesai',
        };
    }

    /**
     * Warna badge Bootstrap (tanpa awalan bg-).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Belum => 'secondary',
            self::Proses => 'warning',
            self::Revisi => 'danger',
            self::Selesai => 'success',
        };
    }

    /**
     * Pemohon masih boleh mengubah dokumen permohonan.
     */
    public function bisaUbahDokumen(): bool
    {
        return in_array($this, [self::Belum, self::Revisi], true);
    }
}
