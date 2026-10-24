<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Butir rekomendasi pada hasil fasilitasi (rich text, disanitasi saat disimpan).
 */
class HasilFasilitasiRekomendasi extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi_rekomendasi';

    protected $fillable = [
        'hasil_fasilitasi_id',
        'catatan',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'catatan' => SanitizedHtml::class,
        ];
    }

    public function hasilFasilitasi(): BelongsTo
    {
        return $this->belongsTo(HasilFasilitasi::class, 'hasil_fasilitasi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
