<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalFasilitasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_fasilitasi';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tahun_anggaran',
        'jenis_dokumen',
        'tanggal_mulai',
        'tanggal_selesai',
        'batas_permohonan',
        'undangan_file',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tahun_anggaran' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'batas_permohonan' => 'date',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->hasMany(Permohonan::class);
    }

    public function suratPemberitahuan()
    {
        return $this->hasMany(SuratPemberitahuan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen');
    }

    // Accessor
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_CLOSED => 'Closed',
            default => $this->status,
        };
    }

    public function getJenisDokumenLabelAttribute()
    {
        return match($this->jenis_dokumen) {
            'rkpd' => 'RKPD',
            'rpd' => 'RPD',
            'rpjmd' => 'RPJMD',
            default => strtoupper($this->jenis_dokumen),
        };
    }

    // Scope
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->where('batas_permohonan', '>=', now());
    }
}
