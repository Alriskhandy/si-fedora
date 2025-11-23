<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahunAnggaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tahun_anggaran';

    protected $fillable = [
        'tahun',
        'nama',
        'deskripsi',
        'is_active',
        'is_current',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'tahun' => 'integer',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->hasMany(Permohonan::class, 'tahun_anggaran_id');
    }

    public function jadwalFasilitasi()
    {
        return $this->hasMany(JadwalFasilitasi::class, 'tahun_anggaran_id');
    }

    public function evaluasi()
    {
        return $this->hasMany(Evaluasi::class, 'tahun_anggaran_id');
    }
}