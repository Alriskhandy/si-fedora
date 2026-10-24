<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan pada tahapan pelaksanaan fasilitasi.
 */
class PelaksanaanCatatan extends Model
{
    use HasFactory;

    protected $table = 'pelaksanaan_catatan';

    protected $fillable = [
        'permohonan_id',
        'catatan',
        'peserta',
        'hasil_pembahasan',
        'dokumentasi',
        'dibuat_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'peserta' => 'array',
        ];
    }

    // Relasi
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
