<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\KabupatenKota;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Exclude superadmin users
        $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'superadmin');
        });

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%');
        }

        $users = $query->with('roles')->latest()->paginate(10);
        $roles = Role::whereNotIn('name', ['superadmin'])->orderBy('name')->get();

        return view('pages.users.index', compact('users', 'roles'));
    }

    /**
     * Export users to .csv (Whatsapp, Name, Variable).
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Exclude superadmin users
        $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'superadmin');
        });

        // Only users with a phone number
        $query->whereNotNull('no_hp')->where('no_hp', '!=', '');

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%');
        }

        $users = $query->with('roles')->latest()->get();

        $fileName = 'Data User SI-FEDORA '.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Whatsapp', 'Name', 'Variable']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->no_hp."'",
                    $user->name,
                    $user->roles->first()->name ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::whereNotIn('name', User::NON_CREATABLE_ROLES)->get();
        $kab_kota = KabupatenKota::all();

        // dd($roles);
        return view('pages.users.create', compact('roles', 'kab_kota'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kabupaten_kota_id' => $request->kabupaten_kota_id,
        ]);

        $user->assignRole($request->role);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties(['role_baru' => $request->role])
            ->log('Menambahkan pengguna');

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('manage', $user);

        return view('pages.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('manage', $user);

        $roles = Role::whereNotIn('name', User::nonEditableRolesFor(Auth::user()))->get();
        $currentRole = $user->roles->first()?->name;

        return view('pages.users.edit', compact('user', 'roles', 'currentRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $previousRoles = $user->getRoleNames()->all();
        $user->syncRoles([$request->role]);

        if ($previousRoles !== [$request->role]) {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->withProperties(['role_lama' => $previousRoles, 'role_baru' => $request->role])
                ->log('Mengubah role pengguna');
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('manage', $user);

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('manage', $user);

        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password berhasil direset.');
    }
}
