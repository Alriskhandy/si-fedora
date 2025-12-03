<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUrusan extends Model
{
    use HasFactory;

    protected $table = 'master_urusan';

    protected $fillable = [
        'nama_urusan',
        'kategori',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // Konstanta untuk kategori
    const KATEGORI_WAJIB_DASAR = 'wajib_dasar';
    const KATEGORI_WAJIB_NON_DASAR = 'wajib_non_dasar';
    const KATEGORI_PILIHAN = 'pilihan';

    // Relasi ke permohonan atau evaluasi jika diperlukan
    public function permohonan()
    {
        return $this->hasMany(Permohonan::class, 'urusan_id');
    }

    // Scope filter berdasarkan kategori
    public function scopeWajibDasar($query)
    {
        return $query->where('kategori', self::KATEGORI_WAJIB_DASAR);
    }

    public function scopeWajibNonDasar($query)
    {
        return $query->where('kategori', self::KATEGORI_WAJIB_NON_DASAR);
    }

    public function scopePilihan($query)
    {
        return $query->where('kategori', self::KATEGORI_PILIHAN);
    }

    // Accessor untuk nama kategori yang readable
    public function getKategoriLabelAttribute()
    {
        return match($this->kategori) {
            self::KATEGORI_WAJIB_DASAR => 'Urusan Wajib Dasar',
            self::KATEGORI_WAJIB_NON_DASAR => 'Urusan Wajib Non Dasar',
            self::KATEGORI_PILIHAN => 'Urusan Pilihan',
            default => $this->kategori,
        };
    }
}
