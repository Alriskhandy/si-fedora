<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKelengkapanVerifikasi extends Model
{
    use HasFactory;

    protected $table = 'master_kelengkapan_verifikasi';

    protected $fillable = [
        'nama_dokumen',
        'tahapan_id',
        'jenis_dokumen_id',
        'deskripsi',
        'wajib',
        'urutan',
    ];

    protected $casts = [
        'wajib' => 'boolean',
        'urutan' => 'integer',
    ];

    // Relasi
    public function tahapan()
    {
        return $this->belongsTo(MasterTahapan::class, 'tahapan_id');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    public function dokumenVerifikasiDetail()
    {
        return $this->hasMany(DokumenVerifikasiDetail::class, 'master_kelengkapan_id');
    }

    // Scope untuk dokumen wajib
    public function scopeWajib($query)
    {
        return $query->where('wajib', true);
    }

    // Scope untuk dokumen opsional
    public function scopeOpsional($query)
    {
        return $query->where('wajib', false);
    }

    // Scope berdasarkan tahapan
    public function scopeByTahapan($query, $tahapanId)
    {
        return $query->where('tahapan_id', $tahapanId);
    }

    // Scope ordered by urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}
