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
        $pokjaId = DB::table('tim_pokja')->first()->id; // Pastiin tim_pokja udah ada

        // 1. Superadmin
        $superadmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superadmin->assignRole('superadmin');

        // 2. Kepala Badan
        $kaban = User::create([
            'name' => 'Kepala Badan PERAN',
            'email' => 'kaban@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $kaban->assignRole('kaban');

        // 3. Admin PERAN
        $adminPeran = User::create([
            'name' => 'Admin PERAN',
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
            'name' => 'Fasilitator Pokja 1',
            'email' => 'fasilitator1@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pokja1->assignRole('fasilitator');

        // Link ke pokja_anggota
        DB::table('pokja_anggota')->insert([
            'pokja_id' => $pokjaId,
            'user_id' => $pokja1->id,
            'jabatan' => 'Ketua',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pokja2 = User::create([
            'name' => 'Fasilitator Pokja 2',
            'email' => 'fasilitator2@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pokja2->assignRole('fasilitator');

        DB::table('pokja_anggota')->insert([
            'pokja_id' => $pokjaId,
            'user_id' => $pokja2->id,
            'jabatan' => 'Anggota',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. User Kota Ternate (Pemohon)
        $ternateUser = User::create([
            'name' => 'Pemohon Kota Ternate',
            'email' => 'ternate@sifedora.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $ternateUser->assignRole('pemohon');

        // Link user ke kabupaten_kota
        DB::table('users')->where('id', $ternateUser->id)->update([
            'kabupaten_kota_id' => $ternateId
        ]);

        // 7. User Auditor (Monitoring)
        $auditor = User::create([
            'name' => 'Auditor Monitoring',
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
        echo "Admin PERAN: admin@sifedora.go.id / password\n";
        echo "Verifikator: verifikator1@sifedora.go.id / password\n";
        echo "Fasilitator: fasilitator1@sifedora.go.id / password\n";
        echo "Pemohon (Ternate): ternate@sifedora.go.id / password\n";
        echo "Auditor: auditor@sifedora.go.id / password\n";
    }
}
