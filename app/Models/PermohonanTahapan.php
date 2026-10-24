<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Status dan batas waktu setiap tahapan untuk satu permohonan.
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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deadline' => 'datetime',
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
