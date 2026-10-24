<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Master persyaratan dokumen versi lama. Sudah digantikan MasterKelengkapanVerifikasi; dipertahankan karena tabelnya masih ada.
 */
class PersyaratanDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'persyaratan_dokumen';

    protected $fillable = [
        'jenis_dokumen_id',
        'kode',
        'nama',
        'deskripsi',
        'urutan',
        'is_required',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }
}
