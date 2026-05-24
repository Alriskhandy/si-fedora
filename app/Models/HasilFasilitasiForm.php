<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilFasilitasiForm extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi_form';

    protected $fillable = [
        'hasil_fasilitasi_id',
        'catatan',
        'user_id',
    ];

    public function hasilFasilitasi()
    {
        return $this->belongsTo(HasilFasilitasi::class, 'hasil_fasilitasi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
