<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync
        {--reset : Samakan pemetaan semua role persis dengan katalog (mencabut perubahan dari UI)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan role & permission dengan katalog config/permissions.php';

    /**
     * Execute the console command.
     */
    public function handle(PermissionRegistrar $registrar): int
    {
        $catalog = config('permissions.permissions');
        $isReset = (bool) $this->option('reset');

        foreach (config('permissions.roles') as $roleName) {
            Role::findOrCreate($roleName, 'web');
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

            if ($isReset) {
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
