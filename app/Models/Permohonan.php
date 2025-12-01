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