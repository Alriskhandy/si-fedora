<?php

namespace App\Models;

use App\Enums\StatusJadwal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Jadwal fasilitasi per tahun anggaran dan jenis dokumen, termasuk batas pengajuan permohonan.
 */
class JadwalFasilitasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_fasilitasi';

    protected $fillable = [
        'tahun_anggaran',
        'jenis_dokumen',
        'tanggal_mulai',
        'tanggal_selesai',
        'batas_permohonan',
        'undangan_file',
        'status',
        'dibuat_oleh',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_anggaran' => 'integer',
            'status' => StatusJadwal::class,
            'tanggal_mulai' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'batas_permohonan' => 'datetime',
        ];
    }

    // Relasi
    public function permohonan(): HasMany
    {
        return $this->hasMany(Permohonan::class);
    }

    public function suratPemberitahuan(): HasMany
    {
        return $this->hasMany(SuratPemberitahuan::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function diupdateOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen');
    }

    public function perpanjanganWaktu(): HasMany
    {
        return $this->hasMany(PerpanjanganWaktu::class);
    }

    // Accessor
    public function getStatusLabelAttribute()
    {
        return $this->status?->label() ?? '-';
    }

    public function getJenisDokumenLabelAttribute()
    {
        return match ($this->jenis_dokumen) {
            'rkpd' => 'RKPD',
            'rpd' => 'RPD',
            'rpjmd' => 'RPJMD',
            default => strtoupper($this->jenis_dokumen),
        };
    }

    // Scope
    public function scopePublished(Builder $query)
    {
        return $query->where('status', StatusJadwal::Published);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', StatusJadwal::Published)
            ->where('batas_permohonan', '>=', now());
    }

    /**
     * Jadwal yang batas permohonannya sudah lewat, belum punya Permohonan milik
     * user ini, dan belum pernah diajukan perpanjangan waktu oleh user ini
     * (hanya boleh mengajukan 1 kali per jadwal untuk menghindari request ganda).
     */
    public function scopeAvailableForExtensionRequest(Builder $query, int $userId)
    {
        return $query->where('status', StatusJadwal::Published)
            ->where('batas_permohonan', '<', now())
            ->whereDoesntHave('permohonan', fn ($q) => $q->where('user_id', $userId))
            ->whereDoesntHave('perpanjanganWaktu', fn ($q) => $q->where('user_id', $userId));
    }

    /**
     * Request perpanjangan waktu milik user yang sudah disetujui admin dan
     * batas waktu barunya masih berlaku (dipakai untuk membolehkan pembuatan
     * Permohonan meski batas_permohonan asli sudah lewat).
     */
    public function validExtensionFor(?int $userId = null)
    {
        return $this->perpanjanganWaktu()
            ->where('user_id', $userId ?? auth()->id())
            ->whereNotNull('diproses_at')
            ->whereNotNull('batas_waktu')
            ->where('batas_waktu', '>=', now())
            ->latest('diproses_at')
            ->first();
    }

    public function hasValidExtensionFor(?int $userId = null): bool
    {
        return $this->validExtensionFor($userId) !== null;
    }
}
