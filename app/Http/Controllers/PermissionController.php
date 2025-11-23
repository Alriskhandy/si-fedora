<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:superadmin');
    // }

    public function index()
    {
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|unique:permissions,name',
    //     ]);

    //     Permission::create(['name' => $request->name]);

    //     return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    // }

    // public function destroy(Permission $permission)
    // {
    //     $permission->delete();
    //     return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    // }

    // ... existing code ...
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

public function destroy(Permission $permission)
{
    activity()
        ->causedBy(auth()->user())
        ->withProperties(['permission' => $permission->name])
        ->log('Deleted permission');

    $permission->delete();
    return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
}
// ... existing code ...
}