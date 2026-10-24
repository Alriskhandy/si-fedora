<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tindak lanjut hasil fasilitasi oleh pemohon.
 */
class TindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'permohonan_id',
        'jenis_tindak_lanjut',
        'file_tindak_lanjut',
        'keterangan',
        'file_laporan',
        'tanggal_upload',
        'is_submitted',
        'submitted_at',
        'batas_waktu',
        'catatan_admin',
        'diupload_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_upload' => 'datetime',
            'submitted_at' => 'datetime',
            'batas_waktu' => 'datetime',
            'is_submitted' => 'boolean',
        ];
    }

    // Relasi ke Permohonan
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    // Relasi ke User (yang upload)
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diupload_oleh');
    }
}
