<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Permohonan fasilitasi/evaluasi dari kabupaten/kota. Inti alur bisnis; relasi ke seluruh tahapan.
 */
class Permohonan extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    // ============================================================
    // PROPERTIES
    // ============================================================

    protected $table = 'permohonan';

    protected $fillable = [
        'user_id',
        'kab_kota_id',
        'jadwal_fasilitasi_id',
        'tahun',
        'jenis_dokumen_id',
        'status_akhir',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    // ============================================================
    // ACCESSORS & MUTATORS
    // ============================================================

    public function getKabupatenKotaIdAttribute()
    {
        return $this->attributes['kab_kota_id'] ?? null;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'belum' => 'Belum Dimulai',
            'proses' => 'Dalam Proses',
            'revisi' => 'Perlu Revisi',
            'selesai' => 'Selesai',
        ];

        return $labels[$this->status_akhir] ?? 'Status Tidak Dikenal';
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'belum' => 'secondary',
            'proses' => 'warning',
            'revisi' => 'danger',
            'selesai' => 'success',
        ];

        return $classes[$this->status_akhir] ?? 'secondary';
    }

    // ============================================================
    // ACTIVITY LOG
    // ============================================================

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status_akhir', 'submitted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')
            ->orderBy('created_at', 'desc');
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    // User & Admin Relations
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Master Data Relations
    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, 'kab_kota_id');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    public function jadwalFasilitasi(): BelongsTo
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    // Tahapan Relations
    public function tahapan(): HasMany
    {
        return $this->hasMany(PermohonanTahapan::class);
    }

    public function tahapanAktif(): HasOne
    {
        return $this->hasOne(PermohonanTahapan::class)
            ->where('status', 'proses')
            ->with('masterTahapan');
    }

    // Tim Relations
    public function timFasilitasi(): HasMany
    {
        return $this->hasMany(TimFasilitasiAssignment::class);
    }

    // Dokumen Relations
    public function permohonanDokumen(): HasMany
    {
        return $this->hasMany(PermohonanDokumen::class, 'permohonan_id');
    }

    public function dokumenTahapan(): HasMany
    {
        return $this->hasMany(DokumenTahapan::class);
    }

    // Proses Relations
    public function laporanVerifikasi(): HasOne
    {
        return $this->hasOne(LaporanVerifikasi::class, 'permohonan_id');
    }

    public function penetapanJadwal(): HasOne
    {
        return $this->hasOne(PenetapanJadwalFasilitasi::class, 'permohonan_id');
    }

    public function undanganPelaksanaan(): HasOne
    {
        return $this->hasOne(UndanganPelaksanaan::class, 'permohonan_id');
    }

    public function pelaksanaanCatatan(): HasOne
    {
        return $this->hasOne(PelaksanaanCatatan::class);
    }

    public function hasilFasilitasi(): HasOne
    {
        return $this->hasOne(HasilFasilitasi::class);
    }

    public function penetapanPerda(): HasOne
    {
        return $this->hasOne(PenetapanPerda::class);
    }

    // Tindak Lanjut Relations
    public function tindakLanjut(): HasOne
    {
        return $this->hasOne(TindakLanjut::class);
    }

    // Other Relations
    public function perpanjanganWaktu(): HasMany
    {
        return $this->hasMany(PerpanjanganWaktu::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeByJenisDokumen(Builder $query, $jenis)
    {
        if (is_numeric($jenis)) {
            return $query->where('jenis_dokumen_id', $jenis);
        }

        $jenisDokumen = MasterJenisDokumen::whereRaw('UPPER(nama) = ?', [strtoupper($jenis)])->first();

        return $query->where('jenis_dokumen_id', $jenisDokumen ? $jenisDokumen->id : null);
    }

    public function scopeByTahun(Builder $query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByKabKota(Builder $query, $kabKotaId)
    {
        return $query->where('kab_kota_id', $kabKotaId);
    }

    public function scopeByStatus(Builder $query, $status)
    {
        return $query->where('status_akhir', $status);
    }

    /**
     * Batasi ke permohonan yang boleh dilihat pengguna di daftar.
     *
     * Aturan detail per record ada di PermohonanPolicy::view().
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('permohonan.view-all')) {
            return $query;
        }

        if ($user->can('permohonan.view-own')) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id);

                if ($user->kabupaten_kota_id) {
                    $q->orWhere('kab_kota_id', $user->kabupaten_kota_id);
                }
            });
        }

        // Verifikator (pemegang permohonan.verify): assignment + permohonan selesai yang pernah diverifikasi
        if ($user->can('permohonan.view-assigned') && $user->can('permohonan.verify')) {
            $assignments = UserKabkotaAssignment::where('user_id', $user->id)->where('is_active', true)->get();

            return $query->where(function (Builder $q) use ($assignments, $user) {
                if ($assignments->isNotEmpty()) {
                    $q->orWhere(function (Builder $qq) use ($assignments) {
                        $qq->where(function (Builder $sub) use ($assignments) {
                            foreach ($assignments as $assignment) {
                                $sub->orWhere(function (Builder $s) use ($assignment) {
                                    $s->where('kab_kota_id', $assignment->kabupaten_kota_id);

                                    if ($assignment->tahun) {
                                        $s->where('tahun', $assignment->tahun);
                                    }

                                    if ($assignment->jenis_dokumen_id) {
                                        $s->where('jenis_dokumen_id', $assignment->jenis_dokumen_id);
                                    }
                                });
                            }
                        })->whereIn('status_akhir', ['proses', 'revisi', 'selesai']);
                    });
                }

                // Permohonan selesai yang pernah diverifikasi langsung (pergantian PIC)
                $q->orWhere(function (Builder $qq) use ($user) {
                    $qq->where('status_akhir', 'selesai')
                        ->whereHas('permohonanDokumen', function (Builder $sub) use ($user) {
                            $sub->where('verified_by', $user->id)->whereNotNull('verified_at');
                        });
                });
            });
        }

        // Fasilitator: assignment aktif per kab/kota dan tahun
        if ($user->can('permohonan.view-assigned')) {
            $assignments = UserKabkotaAssignment::where('user_id', $user->id)->where('is_active', true)->get();

            if ($assignments->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function (Builder $q) use ($assignments) {
                foreach ($assignments as $assignment) {
                    $q->orWhere(function (Builder $qq) use ($assignment) {
                        $qq->where('kab_kota_id', $assignment->kabupaten_kota_id)
                            ->where('tahun', $assignment->tahun);

                        if ($assignment->jenis_dokumen_id) {
                            $qq->where('jenis_dokumen_id', $assignment->jenis_dokumen_id);
                        }
                    });
                }
            })->whereIn('status_akhir', ['proses', 'revisi', 'selesai']);
        }

        return $query->whereRaw('1 = 0');
    }

    // ============================================================
    // PROGRESS TRACKING METHODS
    // ============================================================

    public function getProgressSteps()
    {
        $masterTahapan = MasterTahapan::orderBy('urutan')->get();

        $iconMap = [
            'Permohonan' => 'bx-send',
            'Verifikasi' => 'bx-check-circle',
            'Penetapan Jadwal' => 'bx-calendar',
            'Pelaksanaan' => 'bx-briefcase',
            'Hasil Fasilitasi' => 'bx-file',
            'Tindak Lanjut Hasil' => 'bx-task',
            'Penetapan PERDA/PERKADA' => 'bx-check-shield',
        ];

        $steps = [];
        $lastCompletedIndex = -1; // Track index tahapan terakhir yang selesai

        foreach ($masterTahapan as $index => $tahapan) {
            $permohonanTahapan = $this->tahapan()
                ->where('tahapan_id', $tahapan->id)
                ->first();

            // Determine status: pending, active, or completed
            $status = 'pending';
            $completed = false;
            $isActive = false;
            $date = null;

            // Tahap 1 (Permohonan) - Otomatis active saat permohonan dibuat
            if ($index === 0 && $this->exists) {
                if ($permohonanTahapan) {
                    if ($permohonanTahapan->status === 'selesai') {
                        $status = 'completed';
                        $completed = true;
                        $lastCompletedIndex = $index;
                        $date = $permohonanTahapan->tanggal_selesai ?? $permohonanTahapan->updated_at;
                    } else {
                        // Masih proses atau revisi
                        $status = 'active';
                        $isActive = true;
                        $date = $permohonanTahapan->created_at;
                    }
                } else {
                    // Permohonan baru dibuat, belum ada record di permohonan_tahapan
                    // Set sebagai active (on proses)
                    $status = 'active';
                    $isActive = true;
                    $date = $this->created_at;
                }
            } else {
                // Tahap lainnya (Verifikasi dst)
                if ($permohonanTahapan) {
                    if ($permohonanTahapan->status === 'selesai') {
                        $status = 'completed';
                        $completed = true;
                        $lastCompletedIndex = $index;
                        $date = $permohonanTahapan->tanggal_selesai ?? $permohonanTahapan->updated_at;
                    } elseif (in_array($permohonanTahapan->status, ['proses', 'revisi'])) {
                        $status = 'active';
                        $isActive = true;
                        $date = $permohonanTahapan->created_at;
                    }
                } else {
                    // Belum ada record, cek apakah tahap sebelumnya sudah selesai
                    // Jika index === lastCompletedIndex + 1, maka bisa active
                    // Jika tidak, tetap pending
                    $status = 'pending';
                }
            }

            $steps[] = [
                'name' => $tahapan->nama_tahapan,
                'description' => $this->getStepDescription($tahapan->nama_tahapan),
                'icon' => $iconMap[$tahapan->nama_tahapan] ?? 'bx-file',
                'date' => $date,
                'completed' => $completed,
                'isActive' => $isActive,
                'status' => $status, // pending | active | completed
                'tahapan_id' => $tahapan->id,
                'db_status' => $permohonanTahapan->status ?? null,
            ];
        }

        return $steps;
    }

    public function getCurrentStepIndex()
    {
        $currentTahapan = $this->tahapan()
            ->with('masterTahapan')
            ->whereIn('status', ['proses', 'revisi'])
            ->orderBy('id', 'desc')
            ->first();

        if ($currentTahapan) {
            return $currentTahapan->masterTahapan->urutan - 1;
        }

        $lastCompletedTahapan = $this->tahapan()
            ->with('masterTahapan')
            ->where('status', 'selesai')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCompletedTahapan) {
            return $lastCompletedTahapan->masterTahapan->urutan;
        }

        return 0;
    }

    private function getStepDescription($tahapanName)
    {
        $descriptions = [
            'Permohonan' => 'Permohonan dibuat dan diajukan',
            'Verifikasi' => 'Dokumen diverifikasi oleh tim',
            'Penetapan Jadwal' => 'Penetapan jadwal fasilitasi',
            'Pelaksanaan' => 'Pelaksanaan fasilitasi',
            'Hasil Fasilitasi' => 'Penyusunan hasil fasilitasi',
            'Tindak Lanjut Hasil' => 'Tindak lanjut hasil fasilitasi',
            'Penetapan PERDA/PERKADA' => 'Penetapan peraturan daerah',
        ];

        return $descriptions[$tahapanName] ?? '';
    }

    // ============================================================
    // TAHAPAN HELPER METHODS
    // ============================================================

    public function getCurrentTahapan()
    {
        return $this->tahapan()
            ->with('masterTahapan')
            ->whereIn('status', ['proses', 'revisi'])
            ->orderBy('id', 'desc')
            ->first();
    }

    public function isTahapanSelesai($tahapanId)
    {
        return $this->tahapan()
            ->where('tahapan_id', $tahapanId)
            ->where('status', 'selesai')
            ->exists();
    }

    // ============================================================
    // UPLOAD DOCUMENT HELPER METHODS
    // ============================================================

    /**
     * Mendapatkan batas waktu efektif untuk permohonan ini.
     * Prioritas: perpanjangan waktu yang sudah diproses > jadwal fasilitasi.
     */
    public function getEffectiveDeadline()
    {
        $perpanjangan = $this->perpanjanganWaktu()
            ->whereNotNull('diproses_at')
            ->whereNotNull('batas_waktu')
            ->latest('diproses_at')
            ->first();

        if ($perpanjangan) {
            return $perpanjangan->batas_waktu;
        }

        // Jika permohonan ini dibuat berkat perpanjangan waktu berbasis jadwal
        // (pemohon melewati batas_permohonan lalu mengajukan perpanjangan
        // sebelum sempat membuat permohonan), batas waktu yang sudah disetujui
        // itu turut berlaku sebagai batas upload dokumen di tahapan ini, agar
        // pemohon tidak perlu mengajukan perpanjangan kedua untuk upload.
        $perpanjanganJadwal = $this->jadwal_fasilitasi_id
            ? PerpanjanganWaktu::where('jadwal_fasilitasi_id', $this->jadwal_fasilitasi_id)
                ->where('user_id', $this->user_id)
                ->whereNotNull('diproses_at')
                ->whereNotNull('batas_waktu')
                ->latest('diproses_at')
                ->first()
            : null;

        if ($perpanjanganJadwal) {
            return $perpanjanganJadwal->batas_waktu;
        }

        return $this->jadwalFasilitasi?->batas_permohonan;
    }

    public function isUploadDeadlinePassed()
    {
        $deadline = $this->getEffectiveDeadline();

        if (! $deadline) {
            return false;
        }

        return now()->isAfter($deadline);
    }

    public function canUploadDocuments()
    {
        if (! in_array($this->status_akhir, ['belum', 'revisi'])) {
            return false;
        }

        if ($this->isUploadDeadlinePassed()) {
            return false;
        }

        return true;
    }

    public function getUploadDeadlineMessage()
    {
        $deadline = $this->getEffectiveDeadline();

        if (! $deadline) {
            return null;
        }

        if ($this->isUploadDeadlinePassed()) {
            return 'Batas waktu upload dokumen telah berakhir pada '.$deadline->format('d M Y, H:i');
        }

        return 'Batas waktu upload dokumen: '.$deadline->format('d M Y, H:i');
    }
}
