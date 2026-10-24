<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat revisi dokumen tahapan yang diunggah ulang (lihat DokumenTahapan).
 */
class DokumenRevisi extends Model
{
    use HasFactory;

    protected $table = 'dokumen_revisi';

    public $timestamps = false;

    protected $fillable = [
        'dokumen_tahapan_id',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'alasan_revisi',
        'created_by',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function dokumenTahapan(): BelongsTo
    {
        return $this->belongsTo(DokumenTahapan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
