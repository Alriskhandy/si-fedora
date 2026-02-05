<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MasterDataSeeder::class,
            MasterTahapanSeeder::class,
            MasterUrusanSeeder::class,
            MasterJenisDokumenSeeder::class,
            MasterKelengkapanSeeder::class,
            MasterBabSeeder::class,
            UserSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('🔐 Default Login Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Superadmin', 'superadmin@sifedora.go.id', 'password'],
                ['Kaban', 'kaban@sifedora.go.id', 'password'],
                ['Admin PERAN', 'admin@sifedora.go.id', 'password'],
                ['Verifikator', 'verifikator1@sifedora.go.id', 'password'],
                ['Fasilitator', 'fasilitator1@sifedora.go.id', 'password'],
                ['Pemohon (Ternate)', 'ternate@sifedora.go.id', 'password'],
                ['Auditor', 'auditor@sifedora.go.id', 'password'],
            ]
        );
    }
}
