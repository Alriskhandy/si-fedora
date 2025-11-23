<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PokjaAnggota extends Model
{
    use HasFactory;

    protected $table = 'pokja_anggota';

    protected $fillable = [
        'pokja_id',
        'user_id',
        'jabatan',
    ];

    public function pokja()
    {
        return $this->belongsTo(TimPokja::class, 'pokja_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}