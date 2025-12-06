<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanVerifikasi extends Model
{
    use HasFactory;

    protected $table = 'laporan_verifikasi';

    protected $fillable = [
        'permohonan_id',
        'ringkasan_verifikasi',
        'catatan_admin',
        'status_kelengkapan',
        'jumlah_dokumen_verified',
        'jumlah_dokumen_revision',
        'total_dokumen',
        'dibuat_oleh',
        'tanggal_laporan',
    ];

    protected $casts = [
        'tanggal_laporan' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function pembuatLaporan()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Helper methods
    public function getPersentaseVerifiedAttribute()
    {
        if ($this->total_dokumen == 0) return 0;
        return round(($this->jumlah_dokumen_verified / $this->total_dokumen) * 100, 2);
    }

    public function isKelengkapanLengkap()
    {
        return $this->status_kelengkapan === 'lengkap';
    }
}
