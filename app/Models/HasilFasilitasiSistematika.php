<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilFasilitasiSistematika extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi_sistematika';

    protected $fillable = [
        'hasil_fasilitasi_id',
        'bab_sub_bab',
        'catatan_penyempurnaan',
    ];

    // Relasi
    public function hasilFasilitasi()
    {
        return $this->belongsTo(HasilFasilitasi::class, 'hasil_fasilitasi_id');
    }
}
