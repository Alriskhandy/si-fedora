<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenis_dokumen';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->hasMany(Permohonan::class, 'jenis_dokumen_id');
    }

    public function persyaratanDokumen()
    {
        return $this->hasMany(PersyaratanDokumen::class, 'jenis_dokumen_id');
    }

    public function jadwalFasilitasi()
    {
        return $this->hasMany(JadwalFasilitasi::class, 'jenis_dokumen_id');
    }
    // Tambahin ini di model JenisDokumen
    public function persyaratan()
    {
        return $this->hasMany(PersyaratanDokumen::class, 'jenis_dokumen_id');
    }
}