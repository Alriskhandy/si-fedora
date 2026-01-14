<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'urutan' => 'integer',
    ];

    // Relasi
    public function permohonanTahapan()
    {
        return $this->hasMany(PermohonanTahapan::class, 'tahapan_id');
    }

    public function masterKelengkapan()
    {
        return $this->hasMany(MasterKelengkapanVerifikasi::class, 'tahapan_id');
    }

    // Scope
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}
