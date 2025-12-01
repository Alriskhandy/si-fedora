<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi';

    protected $fillable = [
        'permohonan_id',
        'tahun_anggaran_id',
        'draft_rekomendasi',
        'file_draft',
        'catatan_evaluasi',
        'catatan_kaban',
        'approved_by_kaban',
        'approved_at',
        'rejected_by_kaban',
        'rejected_at',
        'evaluated_by',
        'evaluated_at',
    ];
 
   
  

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function tahunAnggaran()
    {
        return $this->belongsTo(TahunAnggaran::class, 'tahun_anggaran_id');
    }

    public function evaluatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'evaluated_by');
    }
    public function approvedByKaban()
{
    return $this->belongsTo(\App\Models\User::class, 'approved_by_kaban');
}

public function rejectedByKaban()
{
    return $this->belongsTo(\App\Models\User::class, 'rejected_by_kaban');
}
}