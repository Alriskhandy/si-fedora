<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * Daftar permission. Nama permission seharusnya hanya berasal dari config/permissions.php; pembuatan/penghapusan lewat UI akan diganti di PRD #6.
 *
 * Akses: role.manage.
 * View: resources/views/pages/admin/permissions
 */
class PermissionController extends Controller
{
    /**
     * Daftar permission.
     */
    public function index()
    {
        $permissions = Permission::all();

        return view('pages.admin.permissions.index', compact('permissions'));
    }

    /**
     * Form tambah permission.
     */
    public function create()
    {
        return view('pages.admin.permissions.create');
    }

    /**
     * Tambah permission (dicatat di activity log).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['permission' => $permission->name])
            ->log('Created new permission');

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Hapus permission (dicatat di activity log).
     */
    public function destroy(Permission $permission)
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['permission' => $permission->name])
            ->log('Deleted permission');

        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
