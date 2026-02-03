<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PermohonanAssignments extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'permohonan_assignments';

    protected $fillable = [
        'permohonan_id',
        'user_id',
        'role',
        'is_pic',
        'assigned_by',
    ];

    protected $casts = [
        'is_pic' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['permohonan_id', 'user_id', 'role', 'is_pic'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeKoordinator($query)
    {
        return $query->where('role', 'koordinator');
    }

    public function scopeFasilitasi($query)
    {
        return $query->where('role', 'fasilitasi');
    }

    public function scopeVerifikasi($query)
    {
        return $query->where('role', 'verifikasi');
    }

    public function scopePic($query)
    {
        return $query->where('is_pic', true);
    }

    public function scopeByPermohonan($query, $permohonanId)
    {
        return $query->where('permohonan_id', $permohonanId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getRoleBadgeAttribute()
    {
        $badges = [
            'koordinator' => '<span class="badge bg-primary">Koordinator</span>',
            'fasilitasi' => '<span class="badge bg-info">Tim Fasilitasi</span>',
            'verifikasi' => '<span class="badge bg-success">Tim Verifikasi</span>',
        ];

        return $badges[$this->role] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getPicBadgeAttribute()
    {
        return $this->is_pic 
            ? '<span class="badge bg-warning">PIC</span>' 
            : '';
    }

    // Helper methods
    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    public function isFasilitasi(): bool
    {
        return $this->role === 'fasilitasi';
    }

    public function isVerifikasi(): bool
    {
        return $this->role === 'verifikasi';
    }

    public function isPIC(): bool
    {
        return $this->is_pic;
    }

    public function setPIC(): bool
    {
        // Set semua assignment untuk permohonan dan role yang sama menjadi bukan PIC
        self::where('permohonan_id', $this->permohonan_id)
            ->where('role', $this->role)
            ->update(['is_pic' => false]);

        // Set assignment ini sebagai PIC
        $this->is_pic = true;
        return $this->save();
    }

    public function removePIC(): bool
    {
        $this->is_pic = false;
        return $this->save();
    }

    // Static methods
    public static function assignUser(
        int $permohonanId, 
        int $userId, 
        string $role, 
        bool $isPic = false, 
        ?int $assignedBy = null
    ): self {
        return self::create([
            'permohonan_id' => $permohonanId,
            'user_id' => $userId,
            'role' => $role,
            'is_pic' => $isPic,
            'assigned_by' => $assignedBy ?? auth()->id,
        ]);
    }

    public static function assignKoordinator(int $permohonanId, int $userId, ?int $assignedBy = null): self
    {
        return self::assignUser($permohonanId, $userId, 'koordinator', true, $assignedBy);
    }

    public static function assignFasilitasi(int $permohonanId, int $userId, bool $isPic = false, ?int $assignedBy = null): self
    {
        return self::assignUser($permohonanId, $userId, 'fasilitasi', $isPic, $assignedBy);
    }

    public static function assignVerifikasi(int $permohonanId, int $userId, bool $isPic = false, ?int $assignedBy = null): self
    {
        return self::assignUser($permohonanId, $userId, 'verifikasi', $isPic, $assignedBy);
    }

    public static function getAssignmentsByRole(int $permohonanId, string $role)
    {
        return self::where('permohonan_id', $permohonanId)
            ->where('role', $role)
            ->with('user')
            ->get();
    }

    public static function getPIC(int $permohonanId, string $role): ?self
    {
        return self::where('permohonan_id', $permohonanId)
            ->where('role', $role)
            ->where('is_pic', true)
            ->first();
    }
}
