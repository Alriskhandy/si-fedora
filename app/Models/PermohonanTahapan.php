<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Status, batas waktu, dan tanggal selesai setiap tahapan untuk satu permohonan.
 *
 * tanggal_selesai terisi otomatis saat status menjadi "selesai" dan dikosongkan saat status
 * keluar dari "selesai". Admin dapat menyesuaikannya (PRD #5); perubahannya tercatat di riwayat.
 * tanggal_selesai_perkiraan = true untuk data lama yang diisi dari updated_at saat migrasi.
 */
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
        'deadline',
        'tanggal_selesai',
        'tanggal_selesai_perkiraan',
    ];

    /**
     * Isi/kosongkan tanggal selesai mengikuti perubahan status, di semua titik penyelesaian tahapan.
     */
    protected static function booted(): void
    {
        static::saving(function (PermohonanTahapan $tahapan): void {
            if (! $tahapan->isDirty('status')) {
                return;
            }

            if ($tahapan->status === 'selesai') {
                $tahapan->tanggal_selesai ??= now();
            } else {
                $tahapan->tanggal_selesai = null;
            }

            $tahapan->tanggal_selesai_perkiraan = false;
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deadline' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'tanggal_selesai_perkiraan' => 'boolean',
        ];
    }

    // Relasi
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function masterTahapan(): BelongsTo
    {
        return $this->belongsTo(MasterTahapan::class, 'tahapan_id');
    }

    /**
     * Riwayat perubahan tanggal selesai & batas waktu, terbaru dulu.
     */
    public function riwayat(): HasMany
    {
        return $this->hasMany(PermohonanTahapanRiwayat::class)->latest('created_at')->latest('id');
    }

    // Scope
    public function scopeByStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBelum(Builder $query)
    {
        return $query->where('status', 'belum');
    }

    public function scopeProses(Builder $query)
    {
        return $query->where('status', 'proses');
    }

    public function scopeRevisi(Builder $query)
    {
        return $query->where('status', 'revisi');
    }

    public function scopeSelesai(Builder $query)
    {
        return $query->where('status', 'selesai');
    }
}
