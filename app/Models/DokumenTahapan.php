<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Dokumen yang diunggah pada tahapan tertentu (pelaksanaan, tindak lanjut, penetapan PERDA, dll.).
 */
class DokumenTahapan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_tahapan';

    protected $fillable = [
        'permohonan_id',
        'tahapan_id',
        'user_id',
        'nama_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status',
        'catatan_verifikator',
        'verified_by',
        'verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    // Relasi
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function masterTahapan(): BelongsTo
    {
        return $this->belongsTo(MasterTahapan::class, 'tahapan_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dokumenRevisi(): HasMany
    {
        return $this->hasMany(DokumenRevisi::class);
    }

    public function verifikasiDetail(): HasMany
    {
        return $this->hasMany(DokumenVerifikasiDetail::class);
    }

    // Scope
    public function scopeByStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeMenunggu(Builder $query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeDiterima(Builder $query)
    {
        return $query->where('status', 'diterima');
    }

    public function scopeDitolak(Builder $query)
    {
        return $query->where('status', 'ditolak');
    }
}
