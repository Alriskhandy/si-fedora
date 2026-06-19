<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenRevisi extends Model
{
    use HasFactory;

    protected $table = 'dokumen_revisi';

    public $timestamps = false;

    protected $fillable = [
        'dokumen_tahapan_id',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'alasan_revisi',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function dokumenTahapan()
    {
        return $this->belongsTo(DokumenTahapan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
