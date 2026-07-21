<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerpanjanganWaktu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'perpanjangan_waktu';

    protected $fillable = [
        'permohonan_id',
        'jadwal_fasilitasi_id',
        'user_id',
        'alasan',
        'surat_permohonan',
        'batas_waktu',
        'catatan_admin',
        'diproses_oleh',
        'diproses_at',
    ];

    protected $casts = [
        'batas_waktu' => 'datetime',
        'diproses_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function (self $model) {
            if (is_null($model->permohonan_id) === is_null($model->jadwal_fasilitasi_id)) {
                throw new \InvalidArgumentException(
                    'PerpanjanganWaktu harus terkait tepat satu: permohonan_id atau jadwal_fasilitasi_id.'
                );
            }
        });
    }

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function jadwalFasilitasi()
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    // Accessor
    public function getRelatedJadwalAttribute()
    {
        return $this->jadwalFasilitasi ?: $this->permohonan?->jadwalFasilitasi;
    }
}
