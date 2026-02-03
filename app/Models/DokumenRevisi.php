<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenRevisi extends Model
{
    use HasFactory;

    protected $table = 'dokumen_revisi';

    protected $fillable = [
        'dokumen_id',
        'versi',
        'catatan_revisi',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'created_by',
    ];

    protected $casts = [
        'versi' => 'integer',
        'file_size' => 'integer',
    ];

    // Relasi
    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope
    public function scopeLatestVersion($query)
    {
        return $query->orderBy('versi', 'desc');
    }
}
