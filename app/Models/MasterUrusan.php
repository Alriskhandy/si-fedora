<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterUrusan extends Model
{
    use HasFactory;

    protected $table = 'master_urusan';

    // Konstanta kategori urusan
    const KATEGORI_WAJIB_DASAR = 'wajib_dasar';
    const KATEGORI_WAJIB_NON_DASAR = 'wajib_non_dasar';
    const KATEGORI_PILIHAN = 'pilihan';

    protected $fillable = [
        'nama_urusan',
        'kategori',
        'urutan',
    ];

    // Relasi
    public function fasilitasiUrusan()
    {
        return $this->hasMany(FasilitasiUrusan::class, 'urusan_id');
    }

    // Accessor untuk nama kategori yang readable
    public function getKategoriLabelAttribute()
    {
        return match ($this->kategori) {
            self::KATEGORI_WAJIB_DASAR => 'Urusan Wajib Dasar',
            self::KATEGORI_WAJIB_NON_DASAR => 'Urusan Wajib Non Dasar',
            self::KATEGORI_PILIHAN => 'Urusan Pilihan',
            default => $this->kategori,
        };
    }
}
