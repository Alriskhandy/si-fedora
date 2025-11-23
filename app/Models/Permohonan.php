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
        'nomor_permohonan',
        'kabupaten_kota_id',
        'nomor_permohonan',
        'jenis_dokumen_id',
        'tahun_anggaran_id',
        'jadwal_fasilitasi_id',
        'nama_dokumen',
        'keterangan',
        'tanggal_permohonan',
        'status',
        'submitted_at',
        'verified_at',
        'assigned_at',
        'evaluated_at',
        'approved_at',
        'completed_at',
        'verifikator_id',
        'pokja_id',
        'created_by',
        'updated_by',
    ];

    // Status labels dan badge class
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'revision_required' => 'Perlu Revisi',
            'assigned' => 'Ditugaskan',
            'in_evaluation' => 'Sedang Dievaluasi',
            'draft_recommendation' => 'Draft Rekomendasi',
            'approved_by_kaban' => 'Disetujui Kaban',
            'letter_issued' => 'Surat Diterbitkan',
            'sent' => 'Terkirim',
            'follow_up' => 'Tindak Lanjut',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
        ];

        return $labels[$this->status] ?? 'Status Tidak Dikenal';
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'draft' => 'primary',
            'submitted' => 'warning',
            'verified' => 'success',
            'revision_required' => 'danger',
            'assigned' => 'info',
            'in_evaluation' => 'warning',
            'draft_recommendation' => 'info',
            'approved_by_kaban' => 'success',
            'letter_issued' => 'primary',
            'sent' => 'success',
            'follow_up' => 'warning',
            'completed' => 'success',
            'rejected' => 'danger',
        ];

        return $classes[$this->status] ?? 'secondary';
    }

    // Relasi
    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    public function tahunAnggaran()
    {
        return $this->belongsTo(TahunAnggaran::class, 'tahun_anggaran_id');
    }

    public function jadwalFasilitasi()
    {
        return $this->belongsTo(JadwalFasilitasi::class, 'jadwal_fasilitasi_id');
    }

    public function evaluasi()
    {
        return $this->hasMany(Evaluasi::class);
    }
// app/Models/Permohonan.php
protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (!$model->nomor_permohonan) {
            $tahun = now()->year;
            $bulan = now()->format('m');
            $counter = self::whereYear('created_at', $tahun)->count() + 1;
            $model->nomor_permohonan = sprintf("%03d/%s/%s", $counter, $bulan, $tahun);
        }
    });
}
public function getTanggalPermohonanFormattedAttribute()
{
    return $this->tanggal_permohonan ? \Carbon\Carbon::parse($this->tanggal_permohonan)->format('d M Y') : '-';
}

// app/Models/Permohonan.php
public function permohonanDokumen()
{
    return $this->hasMany(PermohonanDokumen::class, 'permohonan_id');
}

    
}
// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

// class Permohonan extends Model
// {
//     use HasFactory, SoftDeletes;

//     protected $table = 'permohonan';

//     protected $fillable = [
//         'nomor_permohonan',
//         'kabupaten_kota_id',
//         'jenis_dokumen_id',
//         'tahun_anggaran_id',
//         'jadwal_fasilitasi_id',
//         'nama_dokumen',
//         'keterangan',
//         'tanggal_permohonan',
//         'status',
//         'submitted_at',
//         'verified_at',
//         'assigned_at',
//         'evaluated_at',
//         'approved_at',
//         'completed_at',
//         'verifikator_id',
//         'pokja_id',
//         'created_by',
//         'updated_by',
//     ];

//     protected $casts = [
//         'tanggal_permohonan' => 'date',
//         'submitted_at' => 'datetime',
//         'verified_at' => 'datetime',
//         'assigned_at' => 'datetime',
//         'evaluated_at' => 'datetime',
//         'approved_at' => 'datetime',
//         'completed_at' => 'datetime',
//     ];

//     // Status constants
//     const STATUS_DRAFT = 'draft';
//     const STATUS_SUBMITTED = 'submitted';
//     const STATUS_VERIFIED = 'verified';
//     const STATUS_REVISION_REQUIRED = 'revision_required';
//     const STATUS_ASSIGNED = 'assigned';
//     const STATUS_IN_EVALUATION = 'in_evaluation';
//     const STATUS_DRAFT_RECOMMENDATION = 'draft_recommendation';
//     const STATUS_APPROVED_BY_KABAN = 'approved_by_kaban';
//     const STATUS_LETTER_ISSUED = 'letter_issued';
//     const STATUS_SENT = 'sent';
//     const STATUS_FOLLOW_UP = 'follow_up';
//     const STATUS_COMPLETED = 'completed';
//     const STATUS_REJECTED = 'rejected';

//     public static function getStatusLabels(): array
//     {
//         return [
//             self::STATUS_DRAFT => 'Draft',
//             self::STATUS_SUBMITTED => 'Diajukan',
//             self::STATUS_VERIFIED => 'Terverifikasi',
//             self::STATUS_REVISION_REQUIRED => 'Perlu Revisi',
//             self::STATUS_ASSIGNED => 'Ditugaskan',
//             self::STATUS_IN_EVALUATION => 'Dalam Evaluasi',
//             self::STATUS_DRAFT_RECOMMENDATION => 'Draft Rekomendasi',
//             self::STATUS_APPROVED_BY_KABAN => 'Disetujui Kaban',
//             self::STATUS_LETTER_ISSUED => 'Surat Diterbitkan',
//             self::STATUS_SENT => 'Terkirim',
//             self::STATUS_FOLLOW_UP => 'Tindak Lanjut',
//             self::STATUS_COMPLETED => 'Selesai',
//             self::STATUS_REJECTED => 'Ditolak',
//         ];
//     }

//     public function getStatusLabelAttribute(): string
//     {
//         return self::getStatusLabels()[$this->status] ?? $this->status;
//     }

//     // Relationships
//     public function kabupatenKota()
//     {
//         return $this->belongsTo(KabupatenKota::class);
//     }

//     public function jenisDokumen()
//     {
//         return $this->belongsTo(JenisDokumen::class);
//     }

//     public function tahunAnggaran()
//     {
//         return $this->belongsTo(TahunAnggaran::class);
//     }

//     public function jadwalFasilitasi()
//     {
//         return $this->belongsTo(JadwalFasilitasi::class);
//     }

//     public function verifikator()
//     {
//         return $this->belongsTo(User::class, 'verifikator_id');
//     }

//     public function pokja()
//     {
//         return $this->belongsTo(TimPokja::class, 'pokja_id');
//     }

//     public function creator()
//     {
//         return $this->belongsTo(User::class, 'created_by');
//     }

//     public function updater()
//     {
//         return $this->belongsTo(User::class, 'updated_by');
//     }

//     public function dokumen()
//     {
//         return $this->hasMany(PermohonanDokumen::class);
//     }

//     public function evaluasi()
//     {
//         return $this->hasOne(Evaluasi::class);
//     }

//     public function suratRekomendasi()
//     {
//         return $this->hasOne(SuratRekomendasi::class);
//     }

//     public function activityLog()
//     {
//         return $this->morphMany(ActivityLog::class, 'model');
//     }

//     // Scopes
//     public function scopeByKabupatenKota($query, $kabkotaId)
//     {
//         return $query->where('kabupaten_kota_id', $kabkotaId);
//     }

//     public function scopeByStatus($query, $status)
//     {
//         return $query->where('status', $status);
//     }

//     public function scopePending($query)
//     {
//         return $query->whereIn('status', [
//             self::STATUS_SUBMITTED,
//             self::STATUS_VERIFIED,
//             self::STATUS_ASSIGNED,
//             self::STATUS_IN_EVALUATION
//         ]);
//     }

//     // Helper Methods
//     public function canEdit(): bool
//     {
//         return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REVISION_REQUIRED]);
//     }

//     public function canSubmit(): bool
//     {
//         return $this->status === self::STATUS_DRAFT && $this->isDokumenLengkap();
//     }

//     public function canVerify(): bool
//     {
//         return $this->status === self::STATUS_SUBMITTED;
//     }

//     public function isDokumenLengkap(): bool
//     {
//         $required = $this->jenisDokumen->persyaratan()->where('is_wajib', true)->count();
//         $uploaded = $this->dokumen()->where('is_ada', true)->whereNotNull('file_path')->count();
        
//         return $required === $uploaded;
//     }

//     public function getProgressPercentage(): int
//     {
//         $stages = [
//             self::STATUS_DRAFT => 10,
//             self::STATUS_SUBMITTED => 20,
//             self::STATUS_VERIFIED => 35,
//             self::STATUS_ASSIGNED => 45,
//             self::STATUS_IN_EVALUATION => 60,
//             self::STATUS_DRAFT_RECOMMENDATION => 70,
//             self::STATUS_APPROVED_BY_KABAN => 80,
//             self::STATUS_LETTER_ISSUED => 90,
//             self::STATUS_SENT => 95,
//             self::STATUS_COMPLETED => 100,
//         ];

//         return $stages[$this->status] ?? 0;
//     }

//     public static function generateNomorPermohonan(): string
//     {
//         $tahun = date('Y');
//         $bulan = date('m');
        
//         $lastNumber = self::whereYear('created_at', $tahun)
//             ->whereMonth('created_at', $bulan)
//             ->count() + 1;
        
//         return sprintf('%03d/PERM-SIFEDORA/%s/%s', $lastNumber, $bulan, $tahun);
//     }
// }