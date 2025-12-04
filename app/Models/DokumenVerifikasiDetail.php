<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenVerifikasiDetail extends Model
{
    use HasFactory;

    protected $table = 'dokumen_verifikasi_detail';

    protected $fillable = [
        'dokumen_tahapan_id',
        'master_kelengkapan_id',
        'status_verifikasi',
        'catatan_verifikasi',
        'diverifikasi_oleh',
    ];

    protected $casts = [
        'diverifikasi_pada' => 'datetime',
    ];

    // Relasi
    public function dokumenTahapan()
    {
        return $this->belongsTo(DokumenTahapan::class);
    }

    public function masterKelengkapan()
    {
        return $this->belongsTo(MasterKelengkapanVerifikasi::class);
    }

    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    // Scope
    public function scopeLengkap($query)
    {
        return $query->where('status_verifikasi', 'lengkap');
    }

    public function scopeTidakLengkap($query)
    {
        return $query->where('status_verifikasi', 'tidak_lengkap');
    }

    public function scopeTidakSesuai($query)
    {
        return $query->where('status_verifikasi', 'tidak_sesuai');
    }
}
