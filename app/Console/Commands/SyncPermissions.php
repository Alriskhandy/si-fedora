<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menyinkronkan role & permission dengan katalog config/permissions.php.
 *
 * Tanpa --reset: menambah permission baru ke role default tanpa mencabut perubahan dari UI;
 * role yang baru dibuat langsung mendapat seluruh pemetaan default-nya.
 * Dengan --reset: menyamakan seluruh pemetaan role persis dengan katalog.
 * Permission yang tidak ada di katalog selalu dihapus.
 */
class SyncPermissions extends Command
{
    /**
     * Nama dan argumen command.
     *
     * @var string
     */
    protected $signature = 'permission:sync
        {--reset : Samakan pemetaan semua role persis dengan katalog (mencabut perubahan dari UI)}';

    /**
     * Deskripsi yang tampil di `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Sinkronkan role & permission dengan katalog config/permissions.php';

    /**
     * Buat role & permission yang belum ada, hapus yang tidak ada di katalog, lalu petakan ke role.
     */
    public function handle(PermissionRegistrar $registrar): int
    {
        $catalog = config('permissions.permissions');
        $isReset = (bool) $this->option('reset');

        $createdRoles = [];
        foreach (config('permissions.roles') as $roleName) {
            if (Role::findOrCreate($roleName, 'web')->wasRecentlyCreated) {
                $createdRoles[] = $roleName;
            }
        }

        $created = [];
        foreach (array_keys($catalog) as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            if ($permission->wasRecentlyCreated) {
                $created[] = $name;
            }
        }

        $removed = Permission::whereNotIn('name', array_keys($catalog))->pluck('name')->all();
        Permission::whereIn('name', $removed)->delete();

        foreach (config('permissions.roles') as $roleName) {
            $role = Role::findByName($roleName, 'web');
            $defaults = collect($catalog)->filter(fn (array $item) => in_array($roleName, $item['roles'], true))->keys();

            if ($isReset || in_array($roleName, $createdRoles, true)) {
                $role->syncPermissions($defaults->all());
            } else {
                // Server berjalan: hanya permission baru yang diberikan ke role default-nya
                $role->givePermissionTo($defaults->intersect($created)->all());
            }
        }

        $registrar->forgetCachedPermissions();

        $this->info(sprintf(
            'Permission disinkronkan%s: %d baru, %d dihapus.',
            $isReset ? ' (reset ke default katalog)' : '',
            count($created),
            count($removed),
        ));

        return self::SUCCESS;
    }
}
