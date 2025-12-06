<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'permohonan_id',
        'keterangan',
        'file_laporan',
        'tanggal_upload',
        'diupload_oleh',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime',
    ];

    // Relasi ke Permohonan
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    // Relasi ke User (yang upload)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'diupload_oleh');
    }
}
