<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method bool hasRole($roles, $guard = null)
 * @method bool hasAnyRole($roles, $guard = null)
 * @method bool hasAllRoles($roles, $guard = null)
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany roles()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'kabupaten_kota_id',
        'phone',
        'nip',
        'jabatan',
        'alamat',
        'foto',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }


    public function permohonanCreated()
    {
        return $this->hasMany(Permohonan::class, 'created_by');
    }

    public function permohonanAsVerifikator()
    {
        return $this->hasMany(Permohonan::class, 'verifikator_id');
    }

    public function evaluasi()
    {
        return $this->hasMany(Evaluasi::class, 'evaluator_id');
    }

    public function pokjaAnggota()
    {
        return $this->hasMany(PokjaAnggota::class);
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifikasi()
    {
        return $this->hasMany(Notifikasi::class)->where('is_read', false);
    }

    // Helper Methods
    public function isKabKota(): bool
    {
        return $this->hasRole('kabkota');
    }

    public function isVerifikator(): bool
    {
        return $this->hasRole('verifikator');
    }

    public function isPokja(): bool
    {
        return $this->hasRole('pokja');
    }

    public function isAdminPeran(): bool
    {
        return $this->hasRole('admin_peran');
    }

    public function isKaban(): bool
    {
        return $this->hasRole('kaban');
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
    public function temporaryRoles()
    {
        return $this->hasMany(TemporaryRoleAssignment::class);
    }

    public function activeTemporaryRoles()
    {
        return $this->temporaryRoles()->where('end_date', '>', now());
    }
}
