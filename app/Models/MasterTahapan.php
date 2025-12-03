<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterTahapan extends Model
{
    use HasFactory;

    protected $table = 'master_tahapan';

    protected $fillable = [
        'nama_tahapan',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // Relasi ke permohonan atau evaluasi jika diperlukan
    public function permohonan()
    {
        return $this->hasMany(Permohonan::class, 'tahapan_id');
    }
}
