<?php

namespace App\Enums;

/**
 * Status verifikasi dokumen permohonan (`permohonan_dokumen.status_verifikasi`).
 */
enum StatusVerifikasiDokumen: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Revision = 'revision';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Verified => 'Sesuai',
            self::Rejected => 'Ditolak',
            self::Revision => 'Perlu Revisi',
        };
    }
}
