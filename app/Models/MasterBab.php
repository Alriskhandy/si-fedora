<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master sistematika (bab dan sub-bab) dokumen perencanaan per jenis dokumen.
 */
class MasterBab extends Model
{
    use HasFactory;

    protected $table = 'master_bab';

    protected $fillable = [
        'nama_bab',
        'jenis_dokumen_id',
        'parent_id',
        'urutan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_dokumen_id' => 'integer',
            'parent_id' => 'integer',
            'urutan' => 'integer',
        ];
    }

    // Relasi ke jenis dokumen
    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    // Relasi ke parent bab (untuk sub-bab)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterBab::class, 'parent_id');
    }

    // Relasi ke children (sub-bab)
    public function children(): HasMany
    {
        return $this->hasMany(MasterBab::class, 'parent_id')->orderBy('urutan');
    }

    // Relasi ke dokumen persyaratan
    // Persyaratan dokumen sekarang menggunakan master_kelengkapan_verifikasi

    // Scope untuk bab utama (tanpa parent)
    public function scopeMainBab(Builder $query)
    {
        return $query->whereNull('parent_id')->orderBy('urutan');
    }

    // Scope untuk sub-bab
    public function scopeSubBab(Builder $query)
    {
        return $query->whereNotNull('parent_id')->orderBy('urutan');
    }
}
