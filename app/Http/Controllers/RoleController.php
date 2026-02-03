<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['role:superadmin']);
    // }

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('pages.admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('pages.admin.roles.create', compact('permissions'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|unique:roles,name',
    //         'permissions' => 'array',
    //         'permissions.*' => 'exists:permissions,name',
    //     ]);

    //     $role = Role::create(['name' => $request->name]);
        
    //     if ($request->permissions) {
    //         $role->syncPermissions($request->permissions);
    //     }

    //     return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    // }
    

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    // public function update(Request $request, Role $role)
    // {
    //     $request->validate([
    //         'name' => 'required|unique:roles,name,' . $role->id,
    //         'permissions' => 'array',
    //         'permissions.*' => 'exists:permissions,name',
    //     ]);

    //     $role->update(['name' => $request->name]);
    //     $role->syncPermissions($request->permissions);

    //     return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    // }

    

    // public function destroy(Role $role)
    // {
    //     if ($role->name === 'superadmin') {
    //         return redirect()->back()->with('error', 'Cannot delete superadmin role.');
    //     }

    //     $role->delete();
    //     return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    // }

    // ... existing code ...
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

public function update(Request $request, Role $role)
{
    $request->validate([
        'name' => 'required|unique:roles,name,' . $role->id,
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
            'new_permissions' => $request->permissions
        ])
        ->log('Updated role');

    return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
}

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
// ... existing code ...
}