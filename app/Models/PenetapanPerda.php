<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Data penetapan PERDA/PERKADA (tahapan terakhir).
 */
class PenetapanPerda extends Model
{
    use HasFactory;

    protected $table = 'penetapan_perda';

    protected $fillable = [
        'permohonan_id',
        'nomor_perda',
        'tanggal_penetapan',
        'file_perda',
        'keterangan',
        'dibuat_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_penetapan' => 'date',
        ];
    }

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
