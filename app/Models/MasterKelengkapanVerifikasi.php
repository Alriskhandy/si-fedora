<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Master daftar dokumen kelengkapan yang wajib diunggah pemohon.
 */
class MasterKelengkapanVerifikasi extends Model
{
    use HasFactory;

    protected $table = 'master_kelengkapan_verifikasi';

    protected $fillable = [
        'nama_dokumen',
        'tahapan_id',
        'jenis_dokumen_id',
        'deskripsi',
        'wajib',
        'urutan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wajib' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    // Relasi
    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(MasterTahapan::class, 'tahapan_id');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    // Scope untuk dokumen wajib
    public function scopeWajib(Builder $query)
    {
        return $query->where('wajib', true);
    }

    // Scope untuk dokumen opsional
    public function scopeOpsional(Builder $query)
    {
        return $query->where('wajib', false);
    }

    // Scope berdasarkan tahapan
    public function scopeByTahapan(Builder $query, $tahapanId)
    {
        return $query->where('tahapan_id', $tahapanId);
    }

    // Scope ordered by urutan
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('urutan');
    }
}
