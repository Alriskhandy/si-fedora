<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanTahapan extends Model
{
    use HasFactory;

    protected $table = 'permohonan_tahapan';

    protected $fillable = [
        'permohonan_id',
        'tahapan_id',
        'status',
        'catatan',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function koordinator()
    {
        return $this->belongsTo(User::class, 'koordinator_id');
    }

    public function logs()
    {
        return $this->hasMany(PermohonanTahapanLog::class);
    }

    // Scope
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBelum($query)
    {
        return $query->where('status', 'belum');
    }

    public function scopeProses($query)
    {
        return $query->where('status', 'proses');
    }

    public function scopeRevisi($query)
    {
        return $query->where('status', 'revisi');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }
}
