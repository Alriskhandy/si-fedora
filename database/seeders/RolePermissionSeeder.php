<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RolePermissionSeeder extends Seeder
{
    /**
     * Role & permission diambil dari katalog config/permissions.php.
     * Untuk server yang sudah berjalan, gunakan `php artisan permission:sync` (tanpa --reset).
     */
    public function run(): void
    {
        Artisan::call('permission:sync', ['--reset' => true]);

        $this->command?->info(trim(Artisan::output()));
    }
}
