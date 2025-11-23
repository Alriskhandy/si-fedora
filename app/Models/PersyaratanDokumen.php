<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersyaratanDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'persyaratan_dokumen';

    protected $fillable = [
        'jenis_dokumen_id',
        'kode',
        'nama',
        'deskripsi',
        'urutan',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'urutan' => 'integer',
    ];

    // Relasi
    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    public function permohonanDokumen()
    {
        return $this->hasMany(PermohonanDokumen::class, 'persyaratan_dokumen_id');
    }
}