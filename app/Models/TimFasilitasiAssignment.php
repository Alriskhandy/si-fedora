<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimFasilitasiAssignment extends Model
{
    use HasFactory;

    protected $table = 'tim_fasilitasi_assignment';

    protected $fillable = [
        'permohonan_id',
        'user_id',
        'peran',
        'ditugaskan_oleh',
    ];

    protected $casts = [
        'ditugaskan_pada' => 'datetime',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ditugaskanOleh()
    {
        return $this->belongsTo(User::class, 'ditugaskan_oleh');
    }

    // Scope
    public function scopeByPeran($query, $peran)
    {
        return $query->where('peran', $peran);
    }

    public function scopeKetuaTim($query)
    {
        return $query->where('peran', 'ketua_tim');
    }

    public function scopeAnggotaTim($query)
    {
        return $query->where('peran', 'anggota_tim');
    }
}
