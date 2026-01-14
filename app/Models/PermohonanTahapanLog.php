<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanTahapanLog extends Model
{
    use HasFactory;

    protected $table = 'permohonan_tahapan_log';

    protected $fillable = [
        'permohonan_tahapan_id',
        'status_sebelumnya',
        'status_baru',
        'catatan',
        'diubah_oleh',
    ];

    // Relasi
    public function permohonanTahapan()
    {
        return $this->belongsTo(PermohonanTahapan::class);
    }

    public function diubahOleh()
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }

    // Scope
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
