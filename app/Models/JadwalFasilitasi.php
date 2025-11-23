<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalFasilitasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_fasilitasi';

    protected $fillable = [
        'tahun_anggaran_id',
        'jenis_dokumen_id',
        'nama_kegiatan',      // ← ganti dari nama_jadwal
        'tanggal_mulai',
        'tanggal_selesai',
        'batas_permohonan',   // ← tambah
        'keterangan',         // ← ganti dari deskripsi
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'batas_permohonan' => 'date',  // ← tambah
    ];

    // Relasi tetap sama...
    public function tahunAnggaran()
    {
        return $this->belongsTo(TahunAnggaran::class, 'tahun_anggaran_id');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}