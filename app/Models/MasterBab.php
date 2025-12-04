<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBab extends Model
{
    use HasFactory;

    protected $table = 'master_bab';

    protected $fillable = [
        'nama_bab',
        'parent_id',
        'urutan',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'urutan' => 'integer',
    ];

    // Relasi ke parent bab (untuk sub-bab)
    public function parent()
    {
        return $this->belongsTo(MasterBab::class, 'parent_id');
    }

    // Relasi ke children (sub-bab)
    public function children()
    {
        return $this->hasMany(MasterBab::class, 'parent_id')->orderBy('urutan');
    }

    // Relasi ke dokumen persyaratan
    // Persyaratan dokumen sekarang menggunakan master_kelengkapan_verifikasi

    // Scope untuk bab utama (tanpa parent)
    public function scopeMainBab($query)
    {
        return $query->whereNull('parent_id')->orderBy('urutan');
    }

    // Scope untuk sub-bab
    public function scopeSubBab($query)
    {
        return $query->whereNotNull('parent_id')->orderBy('urutan');
    }
}
