<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID dari tabel master
        $ternateId = DB::table('kabupaten_kota')->where('kode', '8271')->value('id');

        // 1. Superadmin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superadmin->assignRole('superadmin');

        // 2. Kepala Badan
        $kaban = User::create([
            'name' => 'Kepala Bappeda Malut',
            'email' => 'kaban@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $kaban->assignRole('kaban');

        // 3. Admin PERAN
        $adminPeran = User::create([
            'name' => 'Admin',
            'email' => 'admin@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminPeran->assignRole('admin_peran');

        // 4. Tim Verifikasi
        $verifikator1 = User::create([
            'name' => 'Verifikator 1',
            'email' => 'verifikator1@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $verifikator1->assignRole('verifikator');

        $verifikator2 = User::create([
            'name' => 'Verifikator 2',
            'email' => 'verifikator2@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $verifikator2->assignRole('verifikator');

        // 5. Tim Pokja (Fasilitator)
        $pokja1 = User::create([
            'name' => 'Fasilitator 1',
            'email' => 'fasilitator1@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pokja1->assignRole('fasilitator');

        $pokja2 = User::create([
            'name' => 'Fasilitator 2',
            'email' => 'fasilitator2@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pokja2->assignRole('fasilitator');

        // 6. User Kota Ternate (Pemohon)
        $ternateUser = User::create([
            'name' => 'Kota Ternate',
            'email' => 'ternate@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'kabupaten_kota_id' => $ternateId,
        ]);
        $ternateUser->assignRole('pemohon');

        // 7. User Auditor (Monitoring)
        $auditor = User::create([
            'name' => 'Auditor',
            'email' => 'auditor@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $auditor->assignRole('auditor');

        echo "Users with roles created successfully!\n";
        echo "\nDefault Login Credentials:\n";
        echo "==========================\n";
        echo "Superadmin: superadmin@sifedora.go.id / password\n";
        echo "Kaban: kaban@sifedora.go.id / password\n";
        echo "Admin: admin@sifedora.go.id / password\n";
        echo "Verifikator: verifikator1@sifedora.go.id / password\n";
        echo "Fasilitator: fasilitator1@sifedora.go.id / password\n";
        echo "Kota Ternate (Pemohon): ternate@sifedora.go.id / password\n";
        echo "Auditor: auditor@sifedora.go.id / password\n";
    }
}
