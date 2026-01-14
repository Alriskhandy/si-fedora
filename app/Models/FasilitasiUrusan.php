<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FasilitasiUrusan extends Model
{
    use HasFactory;

    protected $table = 'fasilitasi_urusan';

    protected $fillable = [
        'permohonan_id',
        'master_urusan_id',
        'catatan_fasilitasi',
        'status_pembahasan',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function masterUrusan()
    {
        return $this->belongsTo(MasterUrusan::class);
    }

    // Scope
    public function scopeBelumDibahas($query)
    {
        return $query->where('status_pembahasan', 'belum_dibahas');
    }

    public function scopeSedangDibahas($query)
    {
        return $query->where('status_pembahasan', 'sedang_dibahas');
    }

    public function scopeSelesaiDibahas($query)
    {
        return $query->where('status_pembahasan', 'selesai_dibahas');
    }

    public function scopePerluRevisi($query)
    {
        return $query->where('status_pembahasan', 'perlu_revisi');
    }
}
