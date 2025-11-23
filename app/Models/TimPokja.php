<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimPokja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tim_pokja';

    protected $fillable = [
        'nama',
        'deskripsi',
        'ketua_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    public function anggota()
    {
        return $this->hasMany(PokjaAnggota::class, 'pokja_id');
    }

    public function permohonan()
    {
        return $this->hasMany(Permohonan::class, 'pokja_id');
    }

    public function evaluasi()
    {
        return $this->hasMany(Evaluasi::class, 'pokja_id');
    }
}