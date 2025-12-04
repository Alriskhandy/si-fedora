# MIGRATION ORDER & NOTES

## ⚠️ IMPORTANT: Fresh Migration Only

Migrasi refactoring ini dirancang untuk **fresh installation** atau **fresh migration**.
**JANGAN** jalankan di database production yang sudah ada data!

## 🗂️ Urutan Migration (Auto by Laravel)

Laravel akan menjalankan migration berdasarkan timestamp filename:

```
2025_11_21_* → Migrations lama (sudah berjalan)
2025_12_04_000001_refactor_permohonan_table.php
2025_12_04_000002_create_permohonan_tahapan_tables.php
2025_12_04_000003_create_assignment_tables.php
2025_12_04_000004_create_dokumen_tahapan_tables.php
2025_12_04_000005_create_jadwal_pelaksanaan_tables.php
2025_12_04_000006_create_fasilitasi_tables.php
2025_12_04_000007_update_master_kelengkapan_verifikasi.php
```

## ✅ Dependencies Fixed

### Migration 000001 - Refactor Permohonan

-   ✅ Check column existence before drop
-   ✅ Drop foreign keys safely
-   ✅ Rename kabupaten_kota_id → kab_kota_id

### Migration 000005 - Jadwal Pelaksanaan

-   ✅ Drop foreign key dari `surat_pemberitahuan` sebelum drop table
-   ✅ Drop `jadwal_fasilitasi_id` column dari `surat_pemberitahuan`
-   ✅ Recreate `jadwal_fasilitasi` dengan struktur baru (per permohonan)

## 🔄 Cara Menjalankan

### Option 1: Fresh Migration (Database Baru)

```bash
php artisan migrate:fresh --seed
```

### Option 2: Fresh Migration (Development)

```bash
# Drop semua table dan migrate ulang
php artisan migrate:fresh

# Seed master data
php artisan db:seed --class=MasterDataSeeder
php artisan db:seed --class=MasterTahapanSeeder
php artisan db:seed --class=MasterUrusanSeeder
php artisan db:seed --class=MasterKelengkapanSeeder
```

### Option 3: Reset Migrations

```bash
# Rollback semua
php artisan migrate:reset

# Migrate lagi dari awal
php artisan migrate

# Seed
php artisan db:seed
```

## ⚠️ UNTUK PRODUCTION (Dengan Data Existing)

**JANGAN** gunakan migration ini di production yang sudah ada data!

Jika harus migrate production:

1. ✅ Backup database lengkap
2. ✅ Buat script data migration terpisah
3. ✅ Test di staging environment dulu
4. ✅ Siapkan rollback plan
5. ✅ Schedule maintenance window

Atau lebih baik: **Bangun sistem baru paralel** dan migrate data secara bertahap.

## 📝 Breaking Changes Summary

### Tabel yang Berubah Total:

1. ✅ `permohonan` - Kolom berkurang drastis, workflow dipindah
2. ✅ `jadwal_fasilitasi` - Dari global → per permohonan
3. ⚠️ `surat_pemberitahuan` - Kehilangan relasi ke jadwal_fasilitasi

### Tabel yang Deprecated:

-   `permohonan_dokumen` - Diganti `dokumen_tahapan`
-   `evaluasi` - Diganti `fasilitasi_bab` + `fasilitasi_urusan`

### Tabel Baru:

-   `permohonan_tahapan` + `permohonan_tahapan_log`
-   `koordinator_assignment`, `tim_fasilitasi_assignment`, `tim_verifikasi_assignment`
-   `dokumen_tahapan`, `dokumen_verifikasi_detail`, `dokumen_revisi`
-   `pelaksanaan_catatan`, `hasil_fasilitasi`, `penetapan_perda`
-   `fasilitasi_bab`, `fasilitasi_urusan`

## 🐛 Troubleshooting

### Error: "cannot drop table jadwal_fasilitasi because other objects depend on it"

✅ **Fixed!** Migration 000005 sekarang drop foreign key dulu dari surat_pemberitahuan

### Error: "column does not exist"

✅ **Fixed!** Migration 000001 check column existence sebelum drop/add

### Error: "relation already exists"

-   Kemungkinan migration sudah pernah dijalankan sebagian
-   Solusi: `php artisan migrate:fresh` atau rollback manual

## 📊 Seeder Order

```php
DatabaseSeeder.php akan memanggil:
1. RolePermissionSeeder
2. MasterDataSeeder (Kabupaten, Jenis Dokumen, Tahun, Persyaratan, Tim Pokja)
3. MasterTahapanSeeder (6 tahapan)
4. MasterUrusanSeeder (32 urusan)
5. MasterKelengkapanSeeder (15 dokumen)
6. UserSeeder
```

---

**Last Updated**: 4 Desember 2025
**Status**: ✅ Dependencies Fixed - Ready for Fresh Migration
