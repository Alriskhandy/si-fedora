<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratRekomendasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_rekomendasi';

    protected $fillable = [
        'permohonan_id',
        'evaluasi_id',
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'isi_surat',
        'file_surat',
        'file_ttd',
        'file_lampiran',
        'status',
        'signed_by',
        'signed_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'signed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_SIGNED = 'signed';
    const STATUS_SENT = 'sent';

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function evaluasi()
    {
        return $this->belongsTo(Evaluasi::class);
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canSign(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canSend(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public static function generateNomorSurat(): string
    {
        $tahun = date('Y');
        $lastNumber = self::whereYear('created_at', $tahun)->count() + 1;
        return sprintf('%03d/PERAN/REKOMENDASI/%s', $lastNumber, $tahun);
    }
}
