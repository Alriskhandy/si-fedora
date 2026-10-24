<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penetapan jadwal pelaksanaan fasilitasi oleh Kaban untuk suatu permohonan.
 */
class PenetapanJadwalFasilitasi extends Model
{
    use HasFactory;

    protected $table = 'penetapan_jadwal_fasilitasi';

    protected $fillable = [
        'permohonan_id',
        'jadwal_fasilitasi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'latitude',
        'longitude',
        'catatan',
        'ditetapkan_oleh',
        'tanggal_penetapan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_penetapan' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // Relasi
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function jadwalFasilitasi(): BelongsTo
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    public function penetap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditetapkan_oleh');
    }

    // Helper methods
    public function getDurasiHariAttribute()
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function isAktif()
    {
        return now()->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    public function isBelumMulai()
    {
        return now()->lt($this->tanggal_mulai);
    }

    public function isSudahSelesai()
    {
        return now()->gt($this->tanggal_selesai);
    }
}
