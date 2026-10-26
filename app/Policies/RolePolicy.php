<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Batas wewenang pengaturan role & hak akses (PRD #6, keputusan stakeholder no. 7).
 *
 * Pemegang role.manage (Admin PERAN) mengatur hak akses semua role selain superadmin, tetapi
 * tidak boleh menambah/menghapus role atau memberi/mencabut permission tingkat pengelola
 * (PROTECTED_PERMISSIONS), agar tidak bisa menaikkan wewenangnya sendiri setara superadmin.
 * Pemegang role.manage-protected (Superadmin) tidak dibatasi, kecuali role katalog tidak bisa dihapus.
 *
 * Didaftarkan manual di AppServiceProvider karena model Role berasal dari paket Spatie.
 */
class RolePolicy
{
    /**
     * Role yang hanya boleh diubah pemegang role.manage-protected.
     *
     * @var list<string>
     */
    public const PROTECTED_ROLES = ['superadmin'];

    /**
     * Permission yang hanya boleh diberikan/dicabut pemegang role.manage-protected.
     *
     * @var list<string>
     */
    public const PROTECTED_PERMISSIONS = ['role.manage', 'role.manage-protected', 'user.manage-superadmin'];

    /**
     * Tambah role baru.
     */
    public function create(User $user): bool
    {
        return $user->can('role.manage-protected');
    }

    /**
     * Ubah hak akses role.
     */
    public function update(User $user, Role $role): bool
    {
        if ($user->can('role.manage-protected')) {
            return true;
        }

        return $user->can('role.manage') && ! in_array($role->name, self::PROTECTED_ROLES, true);
    }

    /**
     * Hapus role. Role katalog (config/permissions.php) dipakai kode aplikasi sehingga tidak bisa dihapus.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->can('role.manage-protected') && ! self::isCatalogRole($role);
    }

    /**
     * Permission yang tidak boleh diubah pengguna ini pada role mana pun.
     *
     * @return list<string>
     */
    public static function lockedPermissionsFor(User $user): array
    {
        return $user->can('role.manage-protected') ? [] : self::PROTECTED_PERMISSIONS;
    }

    /**
     * Role yang tidak ditampilkan kepada pengguna ini (Admin tidak melihat role superadmin sama sekali).
     *
     * @return list<string>
     */
    public static function hiddenRolesFor(User $user): array
    {
        return $user->can('role.manage-protected') ? [] : self::PROTECTED_ROLES;
    }

    /**
     * Apakah role terdaftar di katalog (punya pemetaan default).
     */
    public static function isCatalogRole(Role $role): bool
    {
        return in_array($role->name, config('permissions.roles'), true);
    }
}
