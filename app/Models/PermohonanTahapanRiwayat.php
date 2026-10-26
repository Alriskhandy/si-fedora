<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat perubahan tanggal selesai atau batas waktu satu tahapan permohonan (PRD #5).
 * Ditampilkan kepada semua pengguna yang boleh melihat permohonan.
 */
class PermohonanTahapanRiwayat extends Model
{
    public const UPDATED_AT = null;

    public const JENIS_TANGGAL_SELESAI = 'tanggal_selesai';

    public const JENIS_DEADLINE = 'deadline';

    protected $table = 'permohonan_tahapan_riwayat';

    protected $fillable = [
        'permohonan_tahapan_id',
        'jenis',
        'nilai_lama',
        'nilai_baru',
        'alasan',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai_lama' => 'datetime',
            'nilai_baru' => 'datetime',
        ];
    }

    public function permohonanTahapan(): BelongsTo
    {
        return $this->belongsTo(PermohonanTahapan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label jenis perubahan untuk tampilan.
     */
    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === self::JENIS_DEADLINE ? 'Batas waktu' : 'Tanggal selesai';
    }
}
