<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilFasilitasi extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi';

    protected $fillable = [
        'permohonan_id',
        'draft_file',
        'final_file',
        'surat_penyampaian',
        'surat_dibuat_oleh',
        'surat_tanggal',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'surat_tanggal' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pembuatSurat()
    {
        return $this->belongsTo(User::class, 'surat_dibuat_oleh');
    }

    public function hasilDetail()
    {
        return $this->hasMany(HasilFasilitasiDetail::class, 'hasil_fasilitasi_id');
    }

    public function hasilSistematika()
    {
        return $this->hasMany(HasilFasilitasiDetail::class, 'hasil_fasilitasi_id')
            ->where('tipe', 'sistematika');
    }

    public function hasilUrusan()
    {
        return $this->hasMany(HasilFasilitasiDetail::class, 'hasil_fasilitasi_id')
            ->where('tipe', 'urusan');
    }
}
