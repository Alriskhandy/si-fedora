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
        'master_bab_id',
        'sub_bab',
        'bab_sub_bab',
        'catatan_penyempurnaan',
        'user_id',
    ];

    // Relasi
    public function hasilFasilitasi()
    {
        return $this->belongsTo(HasilFasilitasi::class, 'hasil_fasilitasi_id');
    }

    public function masterBab()
    {
        return $this->belongsTo(MasterBab::class, 'master_bab_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
