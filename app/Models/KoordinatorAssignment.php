<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KoordinatorAssignment extends Model
{
    use HasFactory;

    protected $table = 'koordinator_assignment';

    protected $fillable = [
        'permohonan_id',
        'koordinator_id',
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

    public function koordinator()
    {
        return $this->belongsTo(User::class, 'koordinator_id');
    }

    public function ditugaskanOleh()
    {
        return $this->belongsTo(User::class, 'ditugaskan_oleh');
    }
}
