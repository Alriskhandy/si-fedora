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
            UserSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->newLine();
        $this->command->info('🔐 Default Login Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Superadmin', 'superadmin@sifedora.go.id', 'password'],
                ['Kaban', 'kaban@peran.malutprov.go.id', 'password'],
                ['Admin PERAN', 'admin@peran.malutprov.go.id', 'password'],
                ['Verifikator', 'verifikator1@peran.malutprov.go.id', 'password'],
                ['Pokja', 'pokja1@peran.malutprov.go.id', 'password'],
                ['Kota Ternate', 'admin@ternatekota.go.id', 'password'],
            ]
        );
    }
}
// namespace Database\Seeders;

// use App\Models\User;
// // use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// class DatabaseSeeder extends Seeder
// {
//     /**
//      * Seed the application's database.
//      */
//     public function run(): void
//     {
//         $this->call([
//             UserSeeder::class,
//         ]);
//     }
// }
