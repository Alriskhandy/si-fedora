<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserKabkotaAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'kabupaten_kota_id',
        'jenis_dokumen_id',
        'role_type',
        'is_pic',
        'tahun',
        'nomor_surat',
        'file_sk',
        'assigned_from',
        'assigned_until',
        'is_active',
    ];

    protected $casts = [
        'is_pic' => 'boolean',
        'is_active' => 'boolean',
        'assigned_from' => 'date',
        'assigned_until' => 'date',
        'tahun' => 'integer',
    ];

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Kabupaten/Kota
     */
    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class);
    }

    /**
     * Relasi ke Jenis Dokumen
     */
    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(MasterJenisDokumen::class, 'jenis_dokumen_id');
    }

    /**
     * Scope: assignment yang masih aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('assigned_until')
                    ->orWhere('assigned_until', '>=', now());
            });
    }

    /**
     * Scope: filter berdasarkan role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role_type', $role);
    }

    /**
     * Scope: hanya PIC
     */
    public function scopePicOnly($query)
    {
        return $query->where('is_pic', true);
    }

    /**
     * Scope: untuk kabupaten/kota tertentu
     */
    public function scopeForKabkota($query, int $kabkotaId)
    {
        return $query->where('kabupaten_kota_id', $kabkotaId);
    }

    /**
     * Scope: untuk user tertentu
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check apakah assignment masih berlaku
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->assigned_from && $this->assigned_from->isFuture()) {
            return false;
        }

        if ($this->assigned_until && $this->assigned_until->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Deactivate assignment
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Activate assignment
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }
}
