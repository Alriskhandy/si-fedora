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
        'user_id',
        'nama_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status',
        'catatan_verifikator',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
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

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
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
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeDiterima($query)
    {
        return $query->where('status', 'diterima');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }
}
