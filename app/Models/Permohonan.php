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
        'kab_kota_id',
        'jadwal_fasilitasi_id',
        'tahun',
        'jenis_dokumen',
        'status_akhir',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

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
        return $query->where('jenis_dokumen', $jenis);
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

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    public function tahunAnggaran()
    {
        return $this->belongsTo(TahunAnggaran::class, 'tahun_anggaran_id');
    }

    // app/Models/Permohonan.php
    public function suratRekomendasi()
    {
        return $this->hasOne(SuratRekomendasi::class, 'permohonan_id');
    }

    public function evaluasi()
    {
        return $this->hasMany(Evaluasi::class);
    }
    // public function pokja()
    // {
    //     return $this->belongsTo(User::class, 'pokja_id');
    // }
    public function timPokja()
    {
        return $this->belongsTo(TimPokja::class, 'pokja_id');
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
            'Penetapan PERDA/PERKADA' => 'Penetapan peraturan daerah',
        ];

        return $descriptions[$tahapanName] ?? '';
    }
}
