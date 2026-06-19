<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenVerifikasiDetail extends Model
{
    use HasFactory;

    protected $table = 'dokumen_verifikasi_detail';

    public $timestamps = false;

    protected $fillable = [
        'dokumen_tahapan_id',
        'master_kelengkapan_id',
        'status',
        'catatan',
        'updated_by',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function dokumenTahapan()
    {
        return $this->belongsTo(DokumenTahapan::class);
    }

    public function masterKelengkapan()
    {
        return $this->belongsTo(MasterKelengkapanVerifikasi::class, 'master_kelengkapan_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
