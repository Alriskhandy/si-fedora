<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permohonan extends Model
{
    use HasFactory, SoftDeletes;

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

    // Accessor untuk backward compatibility dengan kode lama yang menggunakan 'jenis_dokumen'
    public function getJenisDokumenAttribute()
    {
        // Jika ada relasi jenisDokumen yang sudah di-load, gunakan nama dari sana
        if ($this->relationLoaded('jenisDokumen') && $this->jenisDokumen) {
            return $this->jenisDokumen->nama;
        }

        // Jika belum di-load, load relasi dulu
        if ($this->jenis_dokumen_id) {
            $jenisDokumen = \App\Models\MasterJenisDokumen::find($this->jenis_dokumen_id);
            return $jenisDokumen ? $jenisDokumen->nama : null;
        }

        return null;
    }

    // Mutator untuk backward compatibility
    public function setJenisDokumenAttribute($value)
    {
        // Jika yang di-set adalah nama jenis dokumen (string), cari ID-nya
        if (is_string($value)) {
            $jenisDokumen = \App\Models\MasterJenisDokumen::whereRaw('UPPER(nama) = ?', [strtoupper($value)])->first();
            $this->attributes['jenis_dokumen_id'] = $jenisDokumen ? $jenisDokumen->id : null;
        } else {
            // Jika sudah ID, langsung set
            $this->attributes['jenis_dokumen_id'] = $value;
        }
    }

    // Accessor untuk kabupaten_kota_id (backward compatibility)
    public function getKabupatenKotaIdAttribute()
    {
        return $this->attributes['kab_kota_id'] ?? null;
    }

    // Status labels dan badge class untuk status_akhir
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

    // Relasi
    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'kab_kota_id');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relasi ke tabel baru
    public function tahapan()
    {
        return $this->hasMany(PermohonanTahapan::class);
    }

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

    public function dokumenTahapan()
    {
        return $this->hasMany(DokumenTahapan::class);
    }

    public function jadwalFasilitasi()
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    public function pelaksanaanCatatan()
    {
        return $this->hasOne(PelaksanaanCatatan::class);
    }

    public function hasilFasilitasi()
    {
        return $this->hasOne(HasilFasilitasi::class);
    }

    public function tindakLanjut()
    {
        return $this->hasOne(TindakLanjut::class);
    }

    public function penetapanPerda()
    {
        return $this->hasOne(PenetapanPerda::class);
    }

    public function fasilitasiBab()
    {
        return $this->hasMany(FasilitasiBab::class);
    }

    public function fasilitasiUrusan()
    {
        return $this->hasMany(FasilitasiUrusan::class);
    }

    // Helper method untuk mendapatkan tahapan saat ini
    public function getCurrentTahapan()
    {
        return $this->tahapan()
            ->with('masterTahapan')
            ->whereIn('status', ['proses', 'revisi'])
            ->orderBy('id', 'desc')
            ->first();
    }

    // Helper method untuk cek apakah tahapan sudah selesai
    public function isTahapanSelesai($tahapanId)
    {
        return $this->tahapan()
            ->where('tahapan_id', $tahapanId)
            ->where('status', 'selesai')
            ->exists();
    }

    // Scope
    public function scopeByJenisDokumen($query, $jenis)
    {
        // Support both ID and name
        if (is_numeric($jenis)) {
            return $query->where('jenis_dokumen_id', $jenis);
        } else {
            // Search by name
            $jenisDokumen = \App\Models\MasterJenisDokumen::whereRaw('UPPER(nama) = ?', [strtoupper($jenis)])->first();
            return $query->where('jenis_dokumen_id', $jenisDokumen ? $jenisDokumen->id : null);
        }
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

    // app/Models/Permohonan.php
    public function suratRekomendasi()
    {
        return $this->hasOne(SuratRekomendasi::class, 'permohonan_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    // app/Models/Permohonan.php
    public function permohonanDokumen()
    {
        return $this->hasMany(PermohonanDokumen::class, 'permohonan_id');
    }

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

    // Method untuk mendapatkan progress tahapan dari master_tahapan
    public function getProgressSteps()
    {
        // Ambil semua tahapan dari master_tahapan
        $masterTahapan = \App\Models\MasterTahapan::orderBy('urutan')->get();

        // Icon mapping untuk setiap tahapan
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
        foreach ($masterTahapan as $index => $tahapan) {
            // Cek apakah tahapan ini sudah ada di permohonan_tahapan
            $permohonanTahapan = $this->tahapan()
                ->where('tahapan_id', $tahapan->id)
                ->first();

            // Tentukan status completed berdasarkan permohonan_tahapan
            $completed = false;
            $date = null;

            if ($permohonanTahapan) {
                // Jika ada record di permohonan_tahapan dan statusnya selesai
                $completed = $permohonanTahapan->status === 'selesai';
                $date = $permohonanTahapan->tgl_selesai ?? $permohonanTahapan->tgl_mulai;
            } else {
                // Untuk tahapan pertama (Permohonan), dianggap selesai jika permohonan sudah dibuat
                if ($index === 0) {
                    $completed = true;
                    $date = $this->created_at;
                }
            }

            $steps[] = [
                'name' => $tahapan->nama_tahapan,
                'description' => $this->getStepDescription($tahapan->nama_tahapan),
                'icon' => $iconMap[$tahapan->nama_tahapan] ?? 'bx-file',
                'date' => $date,
                'completed' => $completed,
                'tahapan_id' => $tahapan->id,
                'status' => $permohonanTahapan->status ?? null,
            ];
        }

        return $steps;
    }

    public function getCurrentStepIndex()
    {
        // Cari tahapan terakhir yang sedang berjalan
        $currentTahapan = $this->tahapan()
            ->with('masterTahapan')
            ->whereIn('status', ['proses', 'revisi'])
            ->orderBy('id', 'desc')
            ->first();

        if ($currentTahapan) {
            return $currentTahapan->masterTahapan->urutan - 1; // Array index dimulai dari 0
        }

        // Jika belum ada tahapan yang berjalan, cek apakah sudah ada tahapan yang selesai
        $lastCompletedTahapan = $this->tahapan()
            ->with('masterTahapan')
            ->where('status', 'selesai')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCompletedTahapan) {
            // Return index tahapan berikutnya setelah yang terakhir selesai
            return $lastCompletedTahapan->masterTahapan->urutan;
        }

        // Default: tahapan pertama (Permohonan)
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

    // Helper method untuk cek apakah batas waktu upload dokumen sudah lewat
    public function isUploadDeadlinePassed()
    {
        if (!$this->jadwalFasilitasi || !$this->jadwalFasilitasi->batas_permohonan) {
            return false;
        }

        return now()->isAfter($this->jadwalFasilitasi->batas_permohonan);
    }

    // Helper method untuk cek apakah masih bisa upload dokumen
    public function canUploadDocuments()
    {
        // Cek status permohonan - hanya bisa upload jika status belum atau revisi
        if (!in_array($this->status_akhir, ['belum', 'revisi'])) {
            return false;
        }

        // Cek deadline
        if ($this->isUploadDeadlinePassed()) {
            return false;
        }

        return true;
    }

    // Getter untuk pesan deadline
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
