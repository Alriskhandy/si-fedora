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
        $ternateId = DB::table('kabupaten_kota')->where('kode', 'KT-TRN')->value('id');
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
            'email' => 'kaban@peran.malutprov.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $kaban->assignRole('kaban');

        // 3. Admin PERAN
        $adminPeran = User::create([
            'name' => 'Admin PERAN',
            'email' => 'admin@peran.malutprov.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminPeran->assignRole('admin_peran');

        // 4. Tim Verifikasi
        $verifikator1 = User::create([
            'name' => 'Verifikator 1',
            'email' => 'verifikator1@peran.malutprov.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $verifikator1->assignRole('verifikator'); // Pastiin role verifikator, bukan kabkota

        $verifikator2 = User::create([
            'name' => 'Verifikator 2',
            'email' => 'verifikator2@peran.malutprov.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $verifikator2->assignRole('verifikator');

        // 5. Tim Pokja
        $pokja1 = User::create([
            'name' => 'Evaluator Pokja 1',
            'email' => 'pokja1@peran.malutprov.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pokja1->assignRole('pokja');
        
        // Link ke pokja_anggota
        DB::table('pokja_anggota')->insert([
            'pokja_id' => $pokjaId,
            'user_id' => $pokja1->id,
            'jabatan' => 'Ketua',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pokja2 = User::create([
            'name' => 'Evaluator Pokja 2',
            'email' => 'pokja2@peran.malutprov.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pokja2->assignRole('pokja');
        
        DB::table('pokja_anggota')->insert([
            'pokja_id' => $pokjaId,
            'user_id' => $pokja2->id,
            'jabatan' => 'Anggota',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. User Kota Ternate
        $ternateUser = User::create([
            'name' => 'Admin Kota Ternate',
            'email' => 'admin@ternatekota.go.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $ternateUser->assignRole('kabkota');
        
        // Link user ke kabupaten_kota
        DB::table('users')->where('id', $ternateUser->id)->update([
            'kabupaten_kota_id' => $ternateId
        ]);

        echo "Users with roles created successfully!\n";
        echo "\nDefault Login Credentials:\n";
        echo "==========================\n";
        echo "Superadmin: superadmin@sifedora.go.id / password\n";
        echo "Kaban: kaban@peran.malutprov.go.id / password\n";
        echo "Admin PERAN: admin@peran.malutprov.go.id / password\n";
        echo "Verifikator: verifikator1@peran.malutprov.go.id / password\n";
        echo "Pokja: pokja1@peran.malutprov.go.id / password\n";
        echo "Kota Ternate: admin@ternatekota.go.id / password\n";
    }
}