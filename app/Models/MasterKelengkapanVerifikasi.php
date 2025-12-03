<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKelengkapanVerifikasi extends Model
{
    use HasFactory;

    protected $table = 'master_kelengkapan_verifikasi';

    protected $fillable = [
        'nama_dokumen',
        'deskripsi',
        'wajib',
    ];

    protected $casts = [
        'wajib' => 'boolean',
    ];

    // Relasi ke permohonan dokumen
    public function permohonanDokumen()
    {
        return $this->hasMany(PermohonanDokumen::class, 'kelengkapan_id');
    }

    // Scope untuk dokumen wajib
    public function scopeWajib($query)
    {
        return $query->where('wajib', true);
    }

    // Scope untuk dokumen opsional
    public function scopeOpsional($query)
    {
        return $query->where('wajib', false);
    }
}
