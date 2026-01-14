<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenetapanPerda extends Model
{
    use HasFactory;

    protected $table = 'penetapan_perda';

    protected $fillable = [
        'permohonan_id',
        'nomor_perda',
        'tanggal_penetapan',
        'file_perda',
        'keterangan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_penetapan' => 'date',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
