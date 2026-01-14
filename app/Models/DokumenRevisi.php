<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenRevisi extends Model
{
    use HasFactory;

    protected $table = 'dokumen_revisi';

    protected $fillable = [
        'dokumen_tahapan_id',
        'versi',
        'catatan_revisi',
        'file_path',
        'diunggah_oleh',
    ];

    protected $casts = [
        'versi' => 'integer',
    ];

    // Relasi
    public function dokumenTahapan()
    {
        return $this->belongsTo(DokumenTahapan::class);
    }

    public function diunggahOleh()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    // Scope
    public function scopeLatestVersion($query)
    {
        return $query->orderBy('versi', 'desc');
    }
}
