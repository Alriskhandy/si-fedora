<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan penyempurnaan per bab/sub-bab dokumen (rich text, disanitasi saat disimpan).
 */
class HasilFasilitasiSistematika extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi_sistematika';

    protected $fillable = [
        'hasil_fasilitasi_id',
        'master_bab_id',
        'sub_bab',
        'bab_sub_bab',
        'catatan_penyempurnaan',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'catatan_penyempurnaan' => SanitizedHtml::class,
        ];
    }

    // Relasi
    public function hasilFasilitasi(): BelongsTo
    {
        return $this->belongsTo(HasilFasilitasi::class, 'hasil_fasilitasi_id');
    }

    public function masterBab(): BelongsTo
    {
        return $this->belongsTo(MasterBab::class, 'master_bab_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
