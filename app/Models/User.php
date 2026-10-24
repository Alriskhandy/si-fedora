<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Pengguna aplikasi. Hak akses diatur lewat role & permission (spatie/laravel-permission, katalog di config/permissions.php).
 *
 * @method bool hasRole($roles, $guard = null)
 * @method bool hasAnyRole($roles, $guard = null)
 * @method bool hasAllRoles($roles, $guard = null)
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany roles()
 */
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Role yang tidak boleh diberikan lewat form tambah pengguna.
     *
     * @var list<string>
     */
    public const NON_CREATABLE_ROLES = ['superadmin', 'kaban', 'admin_peran'];

    /**
     * Role yang tidak boleh diberikan lewat form ubah pengguna.
     *
     * @var list<string>
     */
    public const NON_EDITABLE_ROLES = ['superadmin'];

    /**
     * Role yang tidak boleh diberikan oleh pengguna tertentu lewat form ubah pengguna.
     *
     * @return list<string>
     */
    public static function nonEditableRolesFor(User $actor): array
    {
        return $actor->can('user.manage-superadmin') ? [] : self::NON_EDITABLE_ROLES;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'kabupaten_kota_id',
        'no_hp',
        'foto_profile',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class)->where('is_read', false);
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    // Assignment Kabupaten/Kota Relations
    public function kabkotaAssignments(): HasMany
    {
        return $this->hasMany(UserKabkotaAssignment::class);
    }

    public function assignedKabkota(): BelongsToMany
    {
        return $this->belongsToMany(KabupatenKota::class, 'user_kabkota_assignments')
            ->withPivot(['role_type', 'is_pic', 'is_active', 'assigned_from', 'assigned_until', 'notes'])
            ->withTimestamps();
    }

    // Helper Methods untuk Assignment
    public function isAssignedToKabkota(int $kabkotaId, ?string $roleType = null): bool
    {
        $query = $this->kabkotaAssignments()
            ->active()
            ->where('kabupaten_kota_id', $kabkotaId);

        if ($roleType) {
            $query->where('role_type', $roleType);
        }

        return $query->exists();
    }

    public function isPicForKabkota(int $kabkotaId): bool
    {
        return $this->kabkotaAssignments()
            ->active()
            ->where('kabupaten_kota_id', $kabkotaId)
            ->where('is_pic', true)
            ->exists();
    }

    public function getAssignedKabkotaIds(?string $roleType = null): array
    {
        $query = $this->kabkotaAssignments()->active();

        if ($roleType) {
            $query->where('role_type', $roleType);
        }

        return $query->pluck('kabupaten_kota_id')->toArray();
    }

    public function getAssignmentForKabkota(int $kabkotaId, ?string $roleType = null)
    {
        $query = $this->kabkotaAssignments()
            ->active()
            ->where('kabupaten_kota_id', $kabkotaId);

        if ($roleType) {
            $query->where('role_type', $roleType);
        }

        return $query->first();
    }
}
