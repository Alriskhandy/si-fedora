<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KabupatenKota extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kabupaten_kota';

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'provinsi',
        'kepala_daerah',
        'email',
        'telepon',
        'alamat',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permohonan()
    {
        return $this->hasMany(Permohonan::class);
    }

    public function suratPemberitahuan()
    {
        return $this->hasMany(SuratPemberitahuan::class);
    }

    public function getFullNameAttribute(): string
    {
        return ucfirst($this->jenis) . ' ' . $this->nama;
    }
}