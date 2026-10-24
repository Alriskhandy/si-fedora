<?php

namespace App\Models;

use App\Casts\SanitizedHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilFasilitasiUrusan extends Model
{
    use HasFactory;

    protected $table = 'hasil_fasilitasi_urusan';

    protected $fillable = [
        'hasil_fasilitasi_id',
        'master_urusan_id',
        'user_id',
        'catatan_masukan', // renamed from pembahasan
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'catatan_masukan' => SanitizedHtml::class,
        ];
    }

    // Relasi
    public function hasilFasilitasi()
    {
        return $this->belongsTo(HasilFasilitasi::class, 'hasil_fasilitasi_id');
    }

    public function masterUrusan()
    {
        return $this->belongsTo(MasterUrusan::class, 'master_urusan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
