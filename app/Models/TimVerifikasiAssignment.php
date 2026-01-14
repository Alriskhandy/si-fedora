<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimVerifikasiAssignment extends Model
{
    use HasFactory;

    protected $table = 'tim_verifikasi_assignment';

    protected $fillable = [
        'permohonan_id',
        'user_id',
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
}
