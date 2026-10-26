<?php

namespace App\Models;

use App\Enums\StatusPermohonan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Status, batas waktu, dan tanggal selesai setiap tahapan untuk satu permohonan.
 *
 * tanggal_selesai terisi otomatis saat status menjadi "selesai" dan dikosongkan saat status
 * keluar dari "selesai". Admin dapat menyesuaikannya (PRD #5); perubahannya tercatat di riwayat.
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

            if ($tahapan->status === StatusPermohonan::Selesai) {
                $tahapan->tanggal_selesai ??= now();
            } else {
                $tahapan->tanggal_selesai = null;
            }
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
            'status' => StatusPermohonan::class,
            'deadline' => 'datetime',
            'tanggal_selesai' => 'datetime',
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
     * Tanggal selesai untuk tampilan: tanggal_selesai, atau updated_at untuk data lama yang belum terisi.
     * Null bila tahapan belum selesai.
     */
    public function getTanggalSelesaiTampilAttribute(): ?Carbon
    {
        if ($this->status !== StatusPermohonan::Selesai) {
            return null;
        }

        return $this->tanggal_selesai ?? $this->updated_at;
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
