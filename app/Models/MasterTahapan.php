<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master tahapan proses fasilitasi (permohonan sampai penetapan PERDA), berurutan menurut kolom urutan.
 */
class MasterTahapan extends Model
{
    use HasFactory;

    protected $table = 'master_tahapan';

    protected $fillable = [
        'kode',
        'nama_tahapan',
        'deskripsi',
        'urutan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    // Relasi
    public function permohonanTahapan(): HasMany
    {
        return $this->hasMany(PermohonanTahapan::class, 'tahapan_id');
    }

    public function masterKelengkapan(): HasMany
    {
        return $this->hasMany(MasterKelengkapanVerifikasi::class, 'tahapan_id');
    }

    // Scope
    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('urutan');
    }
}
