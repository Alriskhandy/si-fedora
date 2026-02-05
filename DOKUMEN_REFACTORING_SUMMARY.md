# Summary Refactoring Penyederhanaan Dokumen

## 📋 Overview
Telah dilakukan refactoring besar-besaran untuk menyederhanakan struktur dokumen dari 3 tabel menjadi 2 tabel.

## 🔄 Perubahan Struktur Database

### SEBELUM (3 Tabel)
```
permohonan → permohonan_dokumen → dokumen_revisi
```
- Tabel `permohonan_dokumen` sebagai pivot dengan field `is_ada` (boolean)
- Field `is_ada` untuk menandakan dokumen tersedia atau tidak

### SESUDAH (2 Tabel)
```
permohonan → dokumen → dokumen_revisi
```
- Langsung ke tabel `dokumen`
- Field `file_path` (nullable) - null = belum upload, ada value = sudah upload
- Tidak ada field `is_ada` lagi

## 📁 File yang Diubah

### 1. Routes
**File:** `routes/web.php`
- ✅ Dihapus import `PermohonanDokumenController` (line 12)
- ✅ Dihapus route resource `permohonan-dokumen` (line 200-201)
- ✅ Route sudah bersih dari referensi permohonan-dokumen

### 2. Models
**Status:** ✅ Sudah sesuai
- Model `Permohonan` sudah memiliki relasi `dokumen()` (line 112)
- Model `Dokumen` lengkap dengan relasi ke `kelengkapan()` (bukan masterKelengkapan)
- Model `PermohonanDokumen` sudah dihapus

### 3. Controllers

#### PermohonanController
**File:** `app/Http/Controllers/PermohonanController.php`
- ✅ Line 175: `nama_kelengkapan` → `nama_dokumen`
- ✅ Line 215: `permohonanDokumen` → `dokumen` di eager loading
- ✅ Line 264: `is_ada` → `file_path` null check

#### ApprovalController
**File:** `app/Http/Controllers/ApprovalController.php`
- ✅ Line 52: `permohonanDokumen.masterKelengkapan` → `dokumen.kelengkapan`

#### RunWorkflowTest Command
**File:** `app/Console/Commands/RunWorkflowTest.php`
- ✅ Import `PermohonanDokumen` → `Dokumen`
- ✅ Import `JenisDokumen` → `MasterJenisDokumen`
- ✅ Dihapus `TahunAnggaran` (tidak digunakan)
- ✅ Line 86-92: Update create dokumen dengan struktur baru
- ✅ Line 103: `is_ada` → `status` dalam test data

### 4. Views

#### Deleted
- ✅ **Dihapus folder:** `resources/views/pages/permohonan_dokumen/`
  - index.blade.php
  - show.blade.php
  - edit.blade.php
  - create.blade.php

#### Updated - Main View
**File:** `resources/views/pages/permohonan/show.blade.php`
- ✅ Line 274: `is_ada` → `file_path` null check
- ✅ Line 603-606: `permohonanDokumen` → `dokumen`, `masterKelengkapan` → `kelengkapan`
- ✅ Line 656, 813: `is_ada` → `file_path` check
- ✅ Line 762-765: `permohonanDokumen` → `dokumen`, `masterKelengkapan` → `kelengkapan`
- ✅ Semua referensi `masterKelengkapan` → `kelengkapan` (via sed)

#### Updated - Tabs
**File:** `resources/views/pages/permohonan/show-with-tabs.blade.php`
- ✅ Line 241-242: `permohonanDokumen` → `dokumen`, `is_ada` → `file_path`
- ✅ Progress tracking menggunakan `whereNull('file_path')`

**File:** `resources/views/pages/permohonan/tabs/dokumen.blade.php`
- ✅ Line 33-34: Progress calculation dengan `file_path`
- ✅ Line 72-83: Filter collection menggunakan `kelengkapan`
- ✅ Semua `masterKelengkapan` → `kelengkapan`

**File:** `resources/views/pages/permohonan/tabs/overview.blade.php`
- ✅ Line 76-78: `permohonanDokumen` → `dokumen`, `is_ada` → `whereNull('file_path')`

**File:** `resources/views/pages/permohonan/tabs/verifikasi.blade.php`
- ✅ Line 9, 20: `permohonanDokumen` → `dokumen`, `is_ada` → `whereNull('file_path')`
- ✅ Line 112: groupBy menggunakan `kelengkapan->kategori`
- ✅ Line 139: `is_ada` → `file_path` check
- ✅ Line 184: count check `permohonanDokumen` → `dokumen`
- ✅ Semua `masterKelengkapan` → `kelengkapan` (via sed)

#### Updated - Partials
**File:** `resources/views/pages/permohonan/partials/dokumen-table.blade.php`
- ✅ Line 56: `is_ada` → `file_path` check (badge: Ada → Tersedia)
- ✅ Line 121: `is_ada` → `file_path` check

#### Updated - Verifikasi
**File:** `resources/views/pages/verifikasi/show.blade.php`
- ✅ Line 86-90: `permohonanDokumen` → `dokumen`, `masterKelengkapan` → `kelengkapan`
- ✅ Line 137, 230: `is_ada` → `file_path` check
- ✅ Semua `masterKelengkapan` → `kelengkapan` (via sed)

## 🔍 Perubahan Field Logic

### is_ada (Boolean) → file_path (Nullable String)

#### SEBELUM:
```php
// Check jika dokumen ada
if ($dokumen->is_ada) { ... }

// Count dokumen belum ada
->where('is_ada', false)->count()
```

#### SESUDAH:
```php
// Check jika dokumen sudah diupload
if ($dokumen->file_path) { ... }

// Count dokumen belum upload
->whereNull('file_path')->count()
```

### Relasi Model

#### SEBELUM:
```php
$permohonan->permohonanDokumen // Collection PermohonanDokumen
$dokumen->masterKelengkapan // MasterKelengkapanVerifikasi
```

#### SESUDAH:
```php
$permohonan->dokumen // Collection Dokumen
$dokumen->kelengkapan // MasterKelengkapanVerifikasi
```

## ✅ Checklist Validasi

### Routes
- [x] Hapus import PermohonanDokumenController
- [x] Hapus route resource permohonan-dokumen
- [x] Hapus route upload permohonan-dokumen

### Models
- [x] Model Dokumen sudah ada dan lengkap
- [x] Relasi permohonan->dokumen() sudah ada
- [x] Relasi dokumen->kelengkapan() sudah ada

### Controllers
- [x] PermohonanController updated
- [x] ApprovalController updated
- [x] Command test updated
- [x] Tidak ada controller lain yang reference PermohonanDokumen

### Views
- [x] Hapus folder permohonan_dokumen
- [x] Update semua is_ada → file_path
- [x] Update semua permohonanDokumen → dokumen
- [x] Update semua masterKelengkapan → kelengkapan
- [x] show.blade.php
- [x] show-with-tabs.blade.php
- [x] tabs/dokumen.blade.php
- [x] tabs/overview.blade.php
- [x] tabs/verifikasi.blade.php
- [x] partials/dokumen-table.blade.php
- [x] verifikasi/show.blade.php

## 🎯 Next Steps

1. **Testing:**
   - Test upload dokumen
   - Test progress tracking
   - Test submit permohonan dengan validasi dokumen
   - Test verifikasi dokumen

2. **Database Migration:**
   - Pastikan migration sudah sesuai
   - Drop kolom `is_ada` jika masih ada di database
   - Pastikan kolom `file_path` nullable

3. **Dokumentasi:**
   - Update API documentation jika ada
   - Update user guide untuk upload dokumen

## 📝 Notes

- Field `file_path` nullable adalah indikator utama dokumen sudah diupload atau belum
- Relasi sudah langsung dari `permohonan` ke `dokumen` (tidak ada pivot)
- Semua referensi ke `masterKelengkapan` sudah diganti ke `kelengkapan`
- Badge text berubah: "Ada/Tidak" → "Tersedia/Belum"
- Command test sudah disesuaikan dengan struktur baru

## ⚠️ Breaking Changes

1. Route `/permohonan-dokumen` sudah dihapus
2. Model `PermohonanDokumen` tidak ada lagi
3. Field `is_ada` tidak digunakan lagi
4. Relasi `permohonanDokumen()` diganti `dokumen()`
5. Relasi `masterKelengkapan()` diganti `kelengkapan()`

---

**Tanggal Refactoring:** {{ date('Y-m-d') }}
**Status:** ✅ SELESAI
