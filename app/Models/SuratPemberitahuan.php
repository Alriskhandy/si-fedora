<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Surat pemberitahuan jadwal fasilitasi kepada kabupaten/kota.
 */
class SuratPemberitahuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_pemberitahuan';

    protected $fillable = [
        'jadwal_fasilitasi_id',
        'kabupaten_kota_id',
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'isi_surat',
        'file_path',
        'status',
        'sent_at',
        'received_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function jadwalFasilitasi(): BelongsTo
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
