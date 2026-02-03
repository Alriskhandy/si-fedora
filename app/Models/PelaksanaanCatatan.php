<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelaksanaanCatatan extends Model
{
    use HasFactory;

    protected $table = 'pelaksanaan_catatan';

    protected $fillable = [
        'permohonan_id',
        'catatan',
        'peserta',
        'hasil_pembahasan',
        'dokumentasi',
        'created_by',
    ];

    protected $casts = [
        'peserta' => 'array',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
