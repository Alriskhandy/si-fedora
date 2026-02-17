<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Permohonan extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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

    protected $casts = [
        'tahun' => 'integer',
        'submitted_at' => 'datetime',
    ];

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

    public function activityLogs()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')
            ->orderBy('created_at', 'desc');
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    // User & Admin Relations
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    // Master Data Relations
    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'kab_kota_id');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    public function jadwalFasilitasi()
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    // Tahapan Relations
    public function tahapan()
    {
        return $this->hasMany(PermohonanTahapan::class);
    }

    public function tahapanAktif()
    {
        return $this->hasOne(PermohonanTahapan::class)
            ->where('status', 'proses')
            ->with('masterTahapan');
    }

    // Tim Relations
    public function koordinator()
    {
        return $this->hasOne(KoordinatorAssignment::class);
    }

    public function timFasilitasi()
    {
        return $this->hasMany(TimFasilitasiAssignment::class);
    }

    public function timVerifikasi()
    {
        return $this->hasMany(TimVerifikasiAssignment::class);
    }

    // Dokumen Relations
    public function permohonanDokumen()
    {
        return $this->hasMany(PermohonanDokumen::class, 'permohonan_id');
    }

    public function dokumenTahapan()
    {
        return $this->hasMany(DokumenTahapan::class);
    }

    // Proses Relations
    public function laporanVerifikasi()
    {
        return $this->hasOne(LaporanVerifikasi::class, 'permohonan_id');
    }

    public function penetapanJadwal()
    {
        return $this->hasOne(PenetapanJadwalFasilitasi::class, 'permohonan_id');
    }

    public function undanganPelaksanaan()
    {
        return $this->hasOne(UndanganPelaksanaan::class, 'permohonan_id');
    }

    public function pelaksanaanCatatan()
    {
        return $this->hasOne(PelaksanaanCatatan::class);
    }

    public function hasilFasilitasi()
    {
        return $this->hasOne(HasilFasilitasi::class);
    }

    public function fasilitasiBab()
    {
        return $this->hasMany(FasilitasiBab::class);
    }

    public function fasilitasiUrusan()
    {
        return $this->hasMany(FasilitasiUrusan::class);
    }

    // Tindak Lanjut Relations
    public function tindakLanjut()
    {
        return $this->hasOne(TindakLanjut::class);
    }

    public function penetapanPerda()
    {
        return $this->hasOne(PenetapanPerda::class);
    }

    public function suratRekomendasi()
    {
        return $this->hasOne(SuratRekomendasi::class, 'permohonan_id');
    }

    // Other Relations
    public function perpanjanganWaktu()
    {
        return $this->hasMany(PerpanjanganWaktu::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeByJenisDokumen($query, $jenis)
    {
        if (is_numeric($jenis)) {
            return $query->where('jenis_dokumen_id', $jenis);
        }

        $jenisDokumen = \App\Models\MasterJenisDokumen::whereRaw('UPPER(nama) = ?', [strtoupper($jenis)])->first();
        return $query->where('jenis_dokumen_id', $jenisDokumen ? $jenisDokumen->id : null);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByKabKota($query, $kabKotaId)
    {
        return $query->where('kab_kota_id', $kabKotaId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status_akhir', $status);
    }

    // ============================================================
    // PROGRESS TRACKING METHODS
    // ============================================================

    public function getProgressSteps()
    {
        $masterTahapan = \App\Models\MasterTahapan::orderBy('urutan')->get();

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
                        $date = $permohonanTahapan->updated_at;
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
                        $date = $permohonanTahapan->updated_at;
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

    public function isUploadDeadlinePassed()
    {
        if (!$this->jadwalFasilitasi || !$this->jadwalFasilitasi->batas_permohonan) {
            return false;
        }

        return now()->isAfter($this->jadwalFasilitasi->batas_permohonan);
    }

    public function canUploadDocuments()
    {
        if (!in_array($this->status_akhir, ['belum', 'revisi'])) {
            return false;
        }

        if ($this->isUploadDeadlinePassed()) {
            return false;
        }

        return true;
    }

    public function getUploadDeadlineMessage()
    {
        if (!$this->jadwalFasilitasi || !$this->jadwalFasilitasi->batas_permohonan) {
            return null;
        }

        $deadline = $this->jadwalFasilitasi->batas_permohonan;

        if ($this->isUploadDeadlinePassed()) {
            return 'Batas waktu upload dokumen telah berakhir pada ' . $deadline->format('d M Y');
        }

        return 'Batas waktu upload dokumen: ' . $deadline->format('d M Y');
    }
}
