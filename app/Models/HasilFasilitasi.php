<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Hasil fasilitasi/evaluasi per permohonan: draft, draft final, persetujuan Kaban, dan surat penyampaian.
 */
class HasilFasilitasi extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi';

    protected $fillable = [
        'permohonan_id',
        'draft_file',
        'draft_final_file',
        'final_file',
        'status_draft',
        'tanggal_diajukan_kaban',
        'tanggal_disetujui_kaban',
        'keterangan_kaban',
        'surat_penyampaian',
        'surat_dibuat_oleh',
        'surat_tanggal',
        'catatan',
        'dibuat_oleh',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'surat_tanggal' => 'datetime',
            'tanggal_diajukan_kaban' => 'datetime',
            'tanggal_disetujui_kaban' => 'datetime',
        ];
    }

    // Relasi
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pembuatSurat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surat_dibuat_oleh');
    }

    public function hasilUrusan(): HasMany
    {
        return $this->hasMany(HasilFasilitasiUrusan::class, 'hasil_fasilitasi_id');
    }

    public function hasilSistematika(): HasMany
    {
        return $this->hasMany(HasilFasilitasiSistematika::class, 'hasil_fasilitasi_id');
    }

    public function hasilForm(): HasMany
    {
        return $this->hasMany(HasilFasilitasiForm::class, 'hasil_fasilitasi_id');
    }

    public function hasilRekomendasi(): HasMany
    {
        return $this->hasMany(HasilFasilitasiRekomendasi::class, 'hasil_fasilitasi_id');
    }

    /**
     * Status saat isi hasil fasilitasi tidak boleh diubah: sedang dinilai atau sudah disetujui Kaban.
     *
     * @var list<string>
     */
    public const STATUS_TERKUNCI = ['menunggu_persetujuan_kaban', 'disetujui_kaban'];

    /**
     * Apakah masukan (sistematika, urusan, form, rekomendasi) terkunci (keputusan K5).
     * Kunci terbuka lagi bila Kaban menolak (status ditolak_kaban).
     */
    public function isLocked(): bool
    {
        return in_array($this->status_draft, self::STATUS_TERKUNCI, true);
    }

    /**
     * Label dan kelas badge status_draft untuk tampilan.
     *
     * @return array{0: string, 1: string}
     */
    public function statusLabel(): array
    {
        return match ($this->status_draft) {
            'menunggu_persetujuan_kaban' => ['Menunggu Persetujuan Kaban', 'bg-label-warning'],
            'disetujui_kaban' => ['Disetujui Kaban', 'bg-label-success'],
            'ditolak_kaban' => ['Dikembalikan Kaban', 'bg-label-danger'],
            'diajukan' => ['Diajukan', 'bg-label-info'],
            default => ['Penyusunan Masukan', 'bg-label-primary'],
        };
    }

    /**
     * Kebalikan isLocked(); dipakai alur ajukan hasil oleh tim.
     */
    public function canEdit(): bool
    {
        return ! $this->isLocked();
    }
}
