<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Anggota tim fasilitasi per permohonan (tabel lama; penugasan aktif ada di UserKabkotaAssignment).
 */
class TimFasilitasiAssignment extends Model
{
    use HasFactory;

    /**
     * Tabel hanya memiliki kolom created_at.
     */
    public const UPDATED_AT = null;

    protected $table = 'tim_fasilitasi_assignment';

    protected $fillable = [
        'permohonan_id',
        'user_id',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
