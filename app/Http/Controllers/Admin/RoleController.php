<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Policies\RolePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Halaman "Role & Hak Akses": checklist permission per role dari katalog config/permissions.php (PRD #6).
 *
 * Akses: role.manage. Batas wewenang per aksi diatur RolePolicy.
 * Setiap perubahan pemetaan dicatat di activity log (permission ditambah/dicabut).
 * View: resources/views/pages/admin/roles
 */
class RoleController extends Controller
{
    /**
     * Daftar role beserta jumlah hak aksesnya.
     */
    public function index(): View
    {
        $roles = Role::withCount('permissions')
            ->whereNotIn('name', RolePolicy::hiddenRolesFor(auth()->user()))
            ->orderBy('id')
            ->get();

        return view('pages.admin.roles.index', compact('roles'));
    }

    /**
     * Form tambah role.
     */
    public function create(): View
    {
        Gate::authorize('create', Role::class);

        return view('pages.admin.roles.create', ['catalog' => $this->catalogByModule()]);
    }

    /**
     * Simpan role baru beserta hak aksesnya.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);

        $this->syncAndLog($role, $request->validated('permissions', []), 'Menambah role');

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} berhasil ditambahkan.");
    }

    /**
     * Form checklist hak akses role.
     */
    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        return view('pages.admin.roles.edit', [
            'role' => $role,
            'catalog' => $this->catalogByModule(),
            'rolePermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    /**
     * Simpan checklist hak akses. Permission yang terkunci bagi pengguna ini dipertahankan apa adanya.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $permissions = $this->withLockedPermissionsKept($role, $request->validated('permissions', []));

        $this->syncAndLog($role, $permissions, 'Mengubah hak akses role');

        return redirect()->route('admin.roles.index')->with('success', "Hak akses role {$role->name} berhasil disimpan.");
    }

    /**
     * Kembalikan hak akses role katalog ke pemetaan default di config/permissions.php.
     */
    public function reset(Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);
        abort_unless(RolePolicy::isCatalogRole($role), 404);

        $defaults = collect(config('permissions.permissions'))
            ->filter(fn (array $item) => in_array($role->name, $item['roles'], true))
            ->keys()
            ->diff(RolePolicy::lockedPermissionsFor(auth()->user()))
            ->all();

        $this->syncAndLog($role, $this->withLockedPermissionsKept($role, $defaults), 'Mengembalikan hak akses role ke default');

        return redirect()->route('admin.roles.index')->with('success', "Hak akses role {$role->name} dikembalikan ke default.");
    }

    /**
     * Hapus role buatan sendiri (role katalog tidak bisa dihapus).
     */
    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['role' => $role->name, 'dicabut' => $role->permissions->pluck('name')->all()])
            ->log('Menghapus role');

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', "Role {$role->name} berhasil dihapus.");
    }

    /**
     * Katalog permission yang boleh diubah pengguna ini, dikelompokkan per modul (key = nama permission).
     * Permission terkunci tidak ditampilkan; nilainya dipertahankan saat menyimpan.
     *
     * @return Collection<string, Collection<string, array{modul: string, label: string, roles: list<string>}>>
     */
    private function catalogByModule(): Collection
    {
        return collect(config('permissions.permissions'))
            ->except(RolePolicy::lockedPermissionsFor(auth()->user()))
            ->groupBy('modul', preserveKeys: true);
    }

    /**
     * Tambahkan permission terkunci yang sudah dimiliki role, agar tidak ikut tercabut.
     *
     * @param  array<int, string>  $permissions
     * @return list<string>
     */
    private function withLockedPermissionsKept(Role $role, array $permissions): array
    {
        $kept = $role->permissions->pluck('name')->intersect(RolePolicy::lockedPermissionsFor(auth()->user()));

        return collect($permissions)->merge($kept)->unique()->values()->all();
    }

    /**
     * Samakan hak akses role lalu catat permission yang ditambah/dicabut. Cache permission Spatie
     * dibersihkan otomatis oleh syncPermissions, sehingga perubahan langsung berlaku.
     *
     * @param  array<int, string>  $permissions
     */
    private function syncAndLog(Role $role, array $permissions, string $description): void
    {
        $before = $role->permissions->pluck('name');

        $role->syncPermissions($permissions);

        $added = collect($permissions)->diff($before)->values()->all();
        $revoked = $before->diff($permissions)->values()->all();

        if ($added === [] && $revoked === [] && ! $role->wasRecentlyCreated) {
            return;
        }

        activity()
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties(['role' => $role->name, 'ditambah' => $added, 'dicabut' => $revoked])
            ->log($description);
    }
}
