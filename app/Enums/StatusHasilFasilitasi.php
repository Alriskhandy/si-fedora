<?php

namespace App\Enums;

/**
 * Status draft hasil fasilitasi (`hasil_fasilitasi.status_draft`).
 */
enum StatusHasilFasilitasi: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case MenungguPersetujuanKaban = 'menunggu_persetujuan_kaban';
    case DisetujuiKaban = 'disetujui_kaban';
    case DitolakKaban = 'ditolak_kaban';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Penyusunan Masukan',
            self::Diajukan => 'Diajukan',
            self::MenungguPersetujuanKaban => 'Menunggu Persetujuan Kaban',
            self::DisetujuiKaban => 'Disetujui Kaban',
            self::DitolakKaban => 'Dikembalikan Kaban',
        };
    }

    /**
     * Kelas badge lengkap untuk tampilan (Sneat bg-label-*).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-label-primary',
            self::Diajukan => 'bg-label-info',
            self::MenungguPersetujuanKaban => 'bg-label-warning',
            self::DisetujuiKaban => 'bg-label-success',
            self::DitolakKaban => 'bg-label-danger',
        };
    }

    /**
     * Masukan terkunci: sedang dinilai atau sudah disetujui Kaban (keputusan K5).
     */
    public function terkunci(): bool
    {
        return in_array($this, [self::MenungguPersetujuanKaban, self::DisetujuiKaban], true);
    }
}
