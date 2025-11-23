<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User Management
            'user.view', 'user.create', 'user.edit', 'user.delete',
            
            // Master Data
            'master.kabkota.view', 'master.kabkota.manage',
            'master.jenis-dokumen.view', 'master.jenis-dokumen.manage',
            'master.persyaratan.view', 'master.persyaratan.manage',
            'master.tahun.view', 'master.tahun.manage',
            'master.pokja.view', 'master.pokja.manage',
            
            // Jadwal
            'jadwal.view', 'jadwal.create', 'jadwal.edit', 'jadwal.publish',
            
            // Surat Pemberitahuan
            'surat-pemberitahuan.view', 'surat-pemberitahuan.create', 'surat-pemberitahuan.send',
            
            // Permohonan
            'permohonan.view', 'permohonan.create', 'permohonan.edit', 'permohonan.submit',
            'permohonan.verify', 'permohonan.assign',
            
            // Evaluasi
            'evaluasi.view', 'evaluasi.input', 'evaluasi.submit',
            
            // Approval
            'approval.view', 'approval.approve', 'approval.reject',
            
            // Surat Rekomendasi
            'surat-rekomendasi.view', 'surat-rekomendasi.create', 'surat-rekomendasi.sign', 'surat-rekomendasi.send',
            
            // Dashboard & Laporan
            'dashboard.executive', 'dashboard.admin', 'dashboard.kabkota',
            'laporan.view', 'laporan.export',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles & Assign Permissions
        
        // 1. Superadmin - Full Access
        $superadmin = Role::create(['name' => 'superadmin']);
        $superadmin->givePermissionTo(Permission::all());

        // 2. Kaban - Executive & Approval
        $kaban = Role::create(['name' => 'kaban']);
        $kaban->givePermissionTo([
            'dashboard.executive',
            'permohonan.view',
            'evaluasi.view',
            'approval.view', 'approval.approve', 'approval.reject',
            'surat-rekomendasi.view', 'surat-rekomendasi.sign',
            'laporan.view', 'laporan.export',
        ]);

        // 3. Admin PERAN - Koordinasi & Workflow
        $adminPeran = Role::create(['name' => 'admin_peran']);
        $adminPeran->givePermissionTo([
            'dashboard.admin',
            'master.kabkota.view', 'master.kabkota.manage',
            'master.jenis-dokumen.view', 'master.jenis-dokumen.manage',
            'master.persyaratan.view', 'master.persyaratan.manage',
            'master.tahun.view', 'master.tahun.manage',
            'master.pokja.view', 'master.pokja.manage',
            'jadwal.view', 'jadwal.create', 'jadwal.edit', 'jadwal.publish',
            'surat-pemberitahuan.view', 'surat-pemberitahuan.create', 'surat-pemberitahuan.send',
            'permohonan.view', 'permohonan.assign',
            'evaluasi.view',
            'surat-rekomendasi.view', 'surat-rekomendasi.create', 'surat-rekomendasi.send',
            'laporan.view', 'laporan.export',
        ]);

        // 4. Tim Verifikasi
        $verifikator = Role::create(['name' => 'verifikator']);
        $verifikator->givePermissionTo([
            'dashboard.admin',
            'permohonan.view', 'permohonan.verify',
        ]);

        // 5. Tim Pokja/Evaluasi
        $pokja = Role::create(['name' => 'pokja']);
        $pokja->givePermissionTo([
            'dashboard.admin',
            'permohonan.view',
            'evaluasi.view', 'evaluasi.input', 'evaluasi.submit',
        ]);

        // 6. Kabupaten/Kota
        $kabkota = Role::create(['name' => 'kabkota']);
        $kabkota->givePermissionTo([
            'dashboard.kabkota',
            'permohonan.view', 'permohonan.create', 'permohonan.edit', 'permohonan.submit',
            'surat-rekomendasi.view',
        ]);

        echo "Roles & Permissions created successfully!\n";
    }
}