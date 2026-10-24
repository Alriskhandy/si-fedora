<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Kelola role dan pemetaan permission-nya.
 *
 * Akses: role.manage.
 * View: resources/views/pages/admin/roles
 */
class RoleController extends Controller
{
    /**
     * Daftar role beserta permission-nya.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return view('pages.admin.roles.index', compact('roles'));
    }

    /**
     * Form tambah role.
     */
    public function create()
    {
        $permissions = Permission::all();

        return view('pages.admin.roles.create', compact('permissions'));
    }

    /**
     * Form ubah role dan pemetaan permission.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('pages.admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Simpan role baru beserta permission-nya (dicatat di activity log).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['role' => $role->name, 'permissions' => $request->permissions])
            ->log('Created new role');

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Simpan perubahan role dan permission-nya (dicatat di activity log).
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'old_name' => $role->getOriginal('name'),
                'new_name' => $request->name,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $request->permissions,
            ])
            ->log('Updated role');

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Hapus role (role superadmin tidak dapat dihapus).
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'superadmin') {
            return redirect()->back()->with('error', 'Cannot delete superadmin role.');
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['role' => $role->name])
            ->log('Deleted role');

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
