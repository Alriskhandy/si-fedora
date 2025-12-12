<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJenisDokumen extends Model
{
    use HasFactory;

    protected $table = 'master_jenis_dokumen';

    protected $fillable = [
        'nama',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relasi ke bab-bab
    public function babs()
    {
        return $this->hasMany(MasterBab::class, 'jenis_dokumen_id');
    }

    // Scope untuk jenis dokumen aktif
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // Scope untuk jenis dokumen non-aktif
    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
}
