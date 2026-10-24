<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penerima undangan pelaksanaan beserta konfirmasi kehadirannya.
 */
class UndanganPenerima extends Model
{
    use HasFactory;

    protected $table = 'undangan_penerima';

    protected $fillable = [
        'undangan_id',
        'user_id',
        'jenis_penerima',
        'dibaca',
        'tanggal_dibaca',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dibaca' => 'boolean',
            'tanggal_dibaca' => 'datetime',
        ];
    }

    // Relasi
    public function undangan(): BelongsTo
    {
        return $this->belongsTo(UndanganPelaksanaan::class, 'undangan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update([
            'dibaca' => true,
            'tanggal_dibaca' => now(),
        ]);
    }
}
