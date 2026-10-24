<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master jenis dokumen perencanaan (RPJPD, RPJMD, RKPD).
 */
class MasterJenisDokumen extends Model
{
    use HasFactory;

    protected $table = 'master_jenis_dokumen';

    protected $fillable = [
        'nama',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    // Relasi ke bab-bab
    public function babs(): HasMany
    {
        return $this->hasMany(MasterBab::class, 'jenis_dokumen_id');
    }

    // Relasi ke permohonan
    public function permohonan(): HasMany
    {
        return $this->hasMany(Permohonan::class, 'jenis_dokumen_id');
    }

    // Scope untuk jenis dokumen aktif
    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    // Scope untuk jenis dokumen non-aktif
    public function scopeInactive(Builder $query)
    {
        return $query->where('status', false);
    }
}
