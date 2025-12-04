<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FasilitasiBab extends Model
{
    use HasFactory;

    protected $table = 'fasilitasi_bab';

    protected $fillable = [
        'permohonan_id',
        'nomor_bab',
        'judul_bab',
        'catatan_fasilitasi',
        'status_pembahasan',
    ];

    protected $casts = [
        'nomor_bab' => 'integer',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    // Scope
    public function scopeOrdered($query)
    {
        return $query->orderBy('nomor_bab');
    }

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
