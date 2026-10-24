<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modul/panduan pengguna yang dapat diunduh, dapat ditujukan ke role tertentu.
 */
class Modul extends Model
{
    use HasFactory;

    protected $table = 'modul';

    protected $fillable = [
        'judul',
        'deskripsi',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'role',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scope: tampilkan modul untuk role tertentu (termasuk modul 'all')
    public function scopeForRole(Builder $query, string $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('role', 'all')->orWhere('role', $role);
        });
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }
        $kb = $this->file_size / 1024;
        if ($kb < 1024) {
            return number_format($kb, 1).' KB';
        }

        return number_format($kb / 1024, 1).' MB';
    }
}
