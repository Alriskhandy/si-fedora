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
        'draft_final_file',
        'final_file',
        'status_draft',
        'tanggal_diajukan_kaban',
        'tanggal_disetujui_kaban',
        'keterangan_kaban',
        'surat_penyampaian',
        'surat_dibuat_oleh',
        'surat_tanggal',
        'catatan',
        'dibuat_oleh',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'surat_tanggal' => 'datetime',
        'tanggal_diajukan_kaban' => 'datetime',
        'tanggal_disetujui_kaban' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pembuatSurat()
    {
        return $this->belongsTo(User::class, 'surat_dibuat_oleh');
    }

    public function hasilUrusan()
    {
        return $this->hasMany(HasilFasilitasiUrusan::class, 'hasil_fasilitasi_id');
    }

    public function hasilSistematika()
    {
        return $this->hasMany(HasilFasilitasiSistematika::class, 'hasil_fasilitasi_id');
    }
}
