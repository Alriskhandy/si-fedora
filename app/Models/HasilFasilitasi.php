<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilFasilitasi extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi';

    protected $fillable = [
        'permohonan_id',
        'kesimpulan',
        'rekomendasi',
        'catatan_khusus',
        'lampiran_file',
        'dibuat_oleh',
    ];

    // Relasi
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
