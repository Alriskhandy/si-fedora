<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenTahapan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_tahapan';

    protected $fillable = [
        'permohonan_id',
        'tahapan_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'keterangan',
        'diunggah_oleh',
    ];

    protected $casts = [
        'diunggah_pada' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function masterTahapan()
    {
        return $this->belongsTo(MasterTahapan::class, 'tahapan_id');
    }

    public function diunggahOleh()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function verifikasiDetail()
    {
        return $this->hasMany(DokumenVerifikasiDetail::class, 'dokumen_tahapan_id');
    }

    public function revisi()
    {
        return $this->hasMany(DokumenRevisi::class, 'dokumen_tahapan_id');
    }

    // Scope
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis_dokumen', $jenis);
    }

    public function scopeSuratPermohonan($query)
    {
        return $query->where('jenis_dokumen', 'surat_permohonan');
    }

    public function scopeKelengkapanVerifikasi($query)
    {
        return $query->where('jenis_dokumen', 'kelengkapan_verifikasi');
    }

    public function scopeDokumenPendukung($query)
    {
        return $query->where('jenis_dokumen', 'dokumen_pendukung');
    }
}
