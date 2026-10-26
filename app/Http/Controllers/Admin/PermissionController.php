<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Policies\RolePolicy;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

/**
 * Katalog hak akses (read-only). Nama permission hanya didefinisikan di config/permissions.php
 * dan disinkronkan dengan `php artisan permission:sync`; pemetaan ke role diatur di RoleController.
 *
 * Akses: role.manage.
 * View: resources/views/pages/admin/permissions
 */
class PermissionController extends Controller
{
    /**
     * Daftar hak akses per modul beserta role yang saat ini memilikinya.
     * Bagi Admin, role superadmin dan permission tingkat pengelola tidak ditampilkan.
     */
    public function index(): View
    {
        $user = auth()->user();
        $hiddenRoles = RolePolicy::hiddenRolesFor($user);

        $rolesByPermission = Permission::with('roles:id,name')->get()
            ->mapWithKeys(fn (Permission $permission) => [
                $permission->name => $permission->roles->pluck('name')->diff($hiddenRoles)->values()->all(),
            ]);

        $catalog = collect(config('permissions.permissions'))
            ->except(RolePolicy::lockedPermissionsFor($user))
            ->groupBy('modul', preserveKeys: true);

        return view('pages.admin.permissions.index', compact('catalog', 'rolesByPermission'));
    }
}
