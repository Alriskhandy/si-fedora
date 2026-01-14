<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UndanganPenerima extends Model
{
    use HasFactory;

    protected $table = 'undangan_penerima';

    protected $fillable = [
        'undangan_id',
        'user_id',
        'jenis_penerima',
        'dibaca',
        'tanggal_dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'tanggal_dibaca' => 'datetime',
    ];

    // Relasi
    public function undangan()
    {
        return $this->belongsTo(UndanganPelaksanaan::class, 'undangan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update([
            'dibaca' => true,
            'tanggal_dibaca' => now(),
        ]);
    }
}
