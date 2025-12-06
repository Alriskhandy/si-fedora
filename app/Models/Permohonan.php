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

    // Method untuk mendapatkan progress tahapan dari master_tahapan
    public function getProgressSteps()
    {
        $masterTahapan = \App\Models\MasterTahapan::orderBy('urutan')->get();
        $steps = [];

        foreach ($masterTahapan as $tahapan) {
            $step = [
                'name' => $tahapan->nama_tahapan,
                'urutan' => $tahapan->urutan,
                'description' => $this->getStepDescription($tahapan->urutan),
                'icon' => $this->getStepIcon($tahapan->urutan),
                'date' => $this->getStepDate($tahapan->urutan),
                'completed' => $this->isStepCompleted($tahapan->urutan)
            ];
            $steps[] = $step;
        }

        return $steps;
    }

    private function getStepDescription($urutan)
    {
        $descriptions = [
            1 => 'Permohonan dibuat dan diajukan',
            2 => 'Dokumen diverifikasi oleh tim',
            3 => 'Jadwal fasilitasi ditetapkan',
            4 => 'Pelaksanaan fasilitasi/evaluasi',
            5 => 'Draft hasil fasilitasi',
            6 => 'Penetapan Perda/Perkada'
        ];
        return $descriptions[$urutan] ?? '';
    }

    private function getStepIcon($urutan)
    {
        $icons = [
            1 => 'bx-send',
            2 => 'bx-check-circle',
            3 => 'bx-calendar-event',
            4 => 'bx-briefcase',
            5 => 'bx-file-blank',
            6 => 'bx-check-shield'
        ];
        return $icons[$urutan] ?? 'bx-circle';
    }

    private function getStepDate($urutan)
    {
        $dates = [
            1 => $this->submitted_at ?? $this->created_at,
            2 => $this->verified_at,
            3 => $this->assigned_at,
            4 => $this->evaluated_at,
            5 => $this->approved_at,
            6 => $this->completed_at
        ];
        return $dates[$urutan] ?? null;
    }

    private function isStepCompleted($urutan)
    {
        $statusMapping = [
            1 => ['submitted', 'revision_required', 'verified', 'assigned', 'in_evaluation', 'draft_recommendation', 'approved_by_kaban', 'letter_issued', 'sent', 'follow_up', 'completed'],
            2 => ['verified', 'assigned', 'in_evaluation', 'draft_recommendation', 'approved_by_kaban', 'letter_issued', 'sent', 'follow_up', 'completed'],
            3 => ['assigned', 'in_evaluation', 'draft_recommendation', 'approved_by_kaban', 'letter_issued', 'sent', 'follow_up', 'completed'],
            4 => ['in_evaluation', 'draft_recommendation', 'approved_by_kaban', 'letter_issued', 'sent', 'follow_up', 'completed'],
            5 => ['draft_recommendation', 'approved_by_kaban', 'letter_issued', 'sent', 'follow_up', 'completed'],
            6 => ['completed']
        ];

        return in_array($this->status, $statusMapping[$urutan] ?? []);
    }

    public function getCurrentStepIndex()
    {
        if ($this->status === 'completed') return 5;
        if (in_array($this->status, ['approved_by_kaban', 'letter_issued', 'sent', 'follow_up'])) return 5;
        if (in_array($this->status, ['draft_recommendation'])) return 4;
        if (in_array($this->status, ['in_evaluation'])) return 3;
        if (in_array($this->status, ['assigned'])) return 2;
        if (in_array($this->status, ['verified', 'revision_required'])) return 1;
        if (in_array($this->status, ['submitted'])) return 1;
        return 0; // draft
    }
}
