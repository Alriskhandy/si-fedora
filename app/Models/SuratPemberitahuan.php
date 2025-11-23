<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected $casts = [
        'tanggal_surat' => 'date',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function jadwalFasilitasi()
    {
        return $this->belongsTo(JadwalFasilitasi::class);
    }

    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}