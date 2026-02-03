<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenetapanJadwalFasilitasi extends Model
{
    use HasFactory;

    protected $table = 'penetapan_jadwal_fasilitasi';

    protected $fillable = [
        'permohonan_id',
        'jadwal_fasilitasi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'latitude',
        'longitude',
        'catatan',
        'created_by',
        'tanggal_penetapan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_penetapan' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function jadwalFasilitasi()
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    public function penetap()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper methods
    public function getDurasiHariAttribute()
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function isAktif()
    {
        return now()->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    public function isBelumMulai()
    {
        return now()->lt($this->tanggal_mulai);
    }

    public function isSudahSelesai()
    {
        return now()->gt($this->tanggal_selesai);
    }
}
