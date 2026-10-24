<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// ==================== PERMOHONAN DOKUMEN ====================
/**
 * Dokumen kelengkapan yang diunggah pemohon untuk satu permohonan, beserta status verifikasinya.
 */
class PermohonanDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permohonan_dokumen';

    protected $fillable = [
        'permohonan_id',
        'master_kelengkapan_id', // kolom baru
        'is_ada',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status_verifikasi',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ada' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function masterKelengkapan(): BelongsTo
    {
        return $this->belongsTo(MasterKelengkapanVerifikasi::class, 'master_kelengkapan_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->status_verifikasi === 'verified';
    }
}
