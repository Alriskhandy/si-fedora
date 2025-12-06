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
        'catatan',
        'dibuat_oleh',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function hasilUrusan()
    {
        return $this->hasMany(HasilFasilitasiUrusan::class, 'hasil_fasilitasi_id');
    }

    public function hasilSistematika()
    {
        return $this->hasMany(HasilFasilitasiSistematika::class, 'hasil_fasilitasi_id');
    }
}
