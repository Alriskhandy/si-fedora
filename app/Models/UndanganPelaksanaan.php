<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UndanganPelaksanaan extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'undangan_pelaksanaan';

    protected $fillable = [
        'permohonan_id',
        'penetapan_jadwal_id',
        'file_undangan',
        'status',
        'dibuat_oleh',
        'tanggal_dikirim',
    ];

    protected $casts = [
        'tanggal_dikirim' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['file_undangan', 'status', 'tanggal_dikirim'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function penetapanJadwal()
    {
        return $this->belongsTo(PenetapanJadwalFasilitasi::class, 'penetapan_jadwal_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penerima()
    {
        return $this->hasMany(UndanganPenerima::class, 'undangan_id');
    }

    // Helper methods
    public function isTerkirim()
    {
        return $this->status === 'terkirim';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function getJumlahPenerimaAttribute()
    {
        return $this->penerima()->count();
    }

    public function getJumlahDibacaAttribute()
    {
        return $this->penerima()->where('dibaca', true)->count();
    }
}
