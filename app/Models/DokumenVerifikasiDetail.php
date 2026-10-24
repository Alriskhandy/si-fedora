<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rincian hasil verifikasi per dokumen tahapan.
 */
class DokumenVerifikasiDetail extends Model
{
    use HasFactory;

    protected $table = 'dokumen_verifikasi_detail';

    public $timestamps = false;

    protected $fillable = [
        'dokumen_tahapan_id',
        'master_kelengkapan_id',
        'status',
        'catatan',
        'updated_by',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public function dokumenTahapan(): BelongsTo
    {
        return $this->belongsTo(DokumenTahapan::class);
    }

    public function masterKelengkapan(): BelongsTo
    {
        return $this->belongsTo(MasterKelengkapanVerifikasi::class, 'master_kelengkapan_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
