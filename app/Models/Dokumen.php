<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Dokumen extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'dokumen';

    protected $fillable = [
        'permohonan_id',
        'tahapan_id',
        'kelengkapan_id',
        'kategori',
        'nama_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status',
        'catatan',
        'uploaded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama_dokumen', 'status', 'kategori'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function tahapan()
    {
        return $this->belongsTo(MasterTahapan::class, 'tahapan_id');
    }

    public function kelengkapan()
    {
        return $this->belongsTo(MasterKelengkapanVerifikasi::class, 'kelengkapan_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function revisi()
    {
        return $this->hasMany(DokumenRevisi::class);
    }

    // Scopes
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTahapan($query, $tahapanId)
    {
        return $query->where('tahapan_id', $tahapanId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRevision($query)
    {
        return $query->where('status', 'revision');
    }

    // Accessors
    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Menunggu</span>',
            'verified' => '<span class="badge bg-success">Terverifikasi</span>',
            'rejected' => '<span class="badge bg-danger">Ditolak</span>',
            'revision' => '<span class="badge bg-info">Revisi</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getKategoriBadgeAttribute()
    {
        $badges = [
            'permohonan' => '<span class="badge bg-primary">Permohonan</span>',
            'verifikasi' => '<span class="badge bg-info">Verifikasi</span>',
            'pelaksanaan' => '<span class="badge bg-warning">Pelaksanaan</span>',
            'hasil' => '<span class="badge bg-success">Hasil</span>',
        ];

        return $badges[$this->kategori] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Helper methods
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function needsRevision(): bool
    {
        return $this->status === 'revision';
    }

    public function getFileUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    public function verify(User $verifier, ?string $catatan = null): bool
    {
        $this->status = 'verified';
        $this->verified_by = $verifier->id;
        $this->verified_at = now();
        if ($catatan) {
            $this->catatan = $catatan;
        }

        return $this->save();
    }

    public function reject(User $verifier, string $catatan): bool
    {
        $this->status = 'rejected';
        $this->verified_by = $verifier->id;
        $this->verified_at = now();
        $this->catatan = $catatan;

        return $this->save();
    }

    public function requestRevision(User $verifier, string $catatan): bool
    {
        $this->status = 'revision';
        $this->verified_by = $verifier->id;
        $this->verified_at = now();
        $this->catatan = $catatan;

        return $this->save();
    }
}
