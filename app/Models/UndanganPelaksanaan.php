<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UndanganPelaksanaan extends Model
{
    use HasFactory;

    protected $table = 'undangan_pelaksanaan';

    protected $fillable = [
        'permohonan_id',
        'penetapan_jadwal_id',
        'nomor_undangan',
        'perihal',
        'isi_undangan',
        'file_undangan',
        'status',
        'created_by',
        'tanggal_dibuat',
        'tanggal_dikirim',
    ];

    protected $casts = [
        'tanggal_dibuat' => 'datetime',
        'tanggal_dikirim' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function penetapanJadwal()
    {
        return $this->belongsTo(PenetapanJadwalFasilitasi::class, 'penetapan_jadwal_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penerima()
    {
        return $this->hasMany(UndanganPenerima::class, 'undangan_id');
    }

    // Helper methods
    public function isTerkirim()
    {
        return $this->status === 'terkirim';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function getJumlahPenerimaAttribute()
    {
        return $this->penerima()->count();
    }

    public function getJumlahDibacaAttribute()
    {
        return $this->penerima()->where('dibaca', true)->count();
    }
}
