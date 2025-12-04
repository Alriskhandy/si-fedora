# REFACTORING DATABASE STRUKTUR
## Tanggal: 4 Desember 2025

## 🎯 TUJUAN REFACTORING

Mengubah struktur database dari model workflow **monolitik berbasis status enum** menjadi model **tahapan dinamis** yang lebih fleksibel, scalable, dan mudah di-maintain.

---

## 📊 PERBANDINGAN STRUKTUR

### **SEBELUM (Old Structure)**
```
permohonan
├── status: enum 13 values (draft, submitted, verified, ...)
├── submitted_at, verified_at, assigned_at, ... (7 timestamp columns)
├── verifikator_id, pokja_id (langsung di tabel permohonan)
└── permohonan_dokumen (flat, tidak terikat tahapan)
```

**Masalah:**
- ❌ Status workflow hardcoded di enum
- ❌ Sulit menambah tahapan baru
- ❌ Tracking perubahan status tidak lengkap
- ❌ Dokumen tidak terikat ke tahapan tertentu
- ❌ Assignment tim terpusat di tabel permohonan

---

### **SESUDAH (New Structure)**
```
permohonan (simplified)
├── kab_kota_id
├── tahun
├── jenis_dokumen: enum (rkpd, rpd, rpjmd)
└── status_akhir: enum (belum, proses, revisi, selesai)

permohonan_tahapan (dynamic workflow)
├── permohonan_id
├── tahapan_id → master_tahapan
├── status: enum (belum, proses, revisi, selesai)
└── catatan

permohonan_tahapan_log (audit trail)
├── permohonan_tahapan_id
├── status_lama → status_baru
├── keterangan
└── user_id

dokumen_tahapan (per-stage documents)
├── permohonan_id
├── tahapan_id → master_tahapan
├── file_path
└── status: enum (menunggu, diterima, ditolak)

Assignment Tables (separated)
├── koordinator_assignment
├── tim_fasilitasi_assignment
└── tim_verifikasi_assignment
```

**Keuntungan:**
- ✅ Workflow dinamis berbasis `master_tahapan`
- ✅ Audit trail lengkap setiap perubahan status
- ✅ Dokumen terikat ke tahapan spesifik
- ✅ Assignment tim terpisah dan scalable
- ✅ Mudah menambah/modifikasi tahapan

---

## 🗂️ STRUKTUR TABEL BARU

### **A. MASTER DATA**

#### 1. `master_tahapan` (sudah ada)
```sql
id | nama_tahapan | urutan
```
Contoh tahapan:
1. Pengajuan Permohonan
2. Verifikasi Kelengkapan
3. Penugasan Tim
4. Fasilitasi
5. Review & Approval
6. Penetapan

#### 2. `master_kelengkapan_verifikasi` (updated)
```sql
id | nama_dokumen | kategori | tahapan_id | wajib | urutan | deskripsi
```
- **kategori**: `surat_permohonan` | `kelengkapan_verifikasi`
- **tahapan_id**: NULL (global) atau spesifik tahapan

#### 3. `master_bab` & `master_urusan` (sudah ada)

---

### **B. TABEL PERMOHONAN + WORKFLOW**

#### 1. `permohonan` (refactored)
```sql
id | kab_kota_id | tahun | jenis_dokumen | status_akhir | created_by | updated_by
```
- **jenis_dokumen**: enum (`rkpd`, `rpd`, `rpjmd`)
- **status_akhir**: enum (`belum`, `proses`, `revisi`, `selesai`)

#### 2. `permohonan_tahapan` ⭐ NEW
```sql
id | permohonan_id | tahapan_id | status | catatan | updated_by | updated_at
```
- Track status per tahapan
- Unique constraint: `(permohonan_id, tahapan_id)`

#### 3. `permohonan_tahapan_log` ⭐ NEW
```sql
id | permohonan_tahapan_id | status_lama | status_baru | keterangan | user_id | created_at
```
- Audit trail setiap perubahan status

---

### **C. ASSIGNMENT TABLES** ⭐ NEW

#### 1. `koordinator_assignment`
```sql
id | permohonan_id | koordinator_id | created_at
```
- Unique: satu permohonan = 1 koordinator

#### 2. `tim_fasilitasi_assignment`
```sql
id | permohonan_id | user_id | created_at
```
- Many-to-many: permohonan ↔ anggota tim fasilitasi

#### 3. `tim_verifikasi_assignment`
```sql
id | permohonan_id | user_id | created_at
```
- Many-to-many: permohonan ↔ verifikator

---

### **D. DOKUMEN PER TAHAPAN** ⭐ NEW

#### 1. `dokumen_tahapan`
```sql
id | permohonan_id | tahapan_id | user_id | nama_dokumen | file_path | 
   | status (menunggu|diterima|ditolak) | catatan_verifikator | verified_by | verified_at
```
- Dokumen yang diunggah per tahapan
- Status verifikasi per dokumen

#### 2. `dokumen_verifikasi_detail`
```sql
id | dokumen_tahapan_id | master_kelengkapan_id | 
   | status (lengkap|tidak_lengkap|revisi) | catatan | updated_by
```
- Checklist kelengkapan berdasarkan master
- Unique: `(dokumen_tahapan_id, master_kelengkapan_id)`

#### 3. `dokumen_revisi`
```sql
id | dokumen_tahapan_id | file_path | alasan_revisi | created_by | created_at
```
- History revisi dokumen
- Multiple records per dokumen

---

### **E. TAHAPAN KHUSUS**

#### 1. `jadwal_fasilitasi` (refactored)
```sql
id | permohonan_id | tanggal_pelaksanaan | tempat | undangan_file | dibuat_oleh
```
- **SEBELUM**: Global per tahun/jenis dokumen
- **SEKARANG**: Per permohonan (bisa multiple jadwal)

#### 2. `pelaksanaan_catatan` ⭐ NEW
```sql
id | permohonan_id | berita_acara_file | notulensi_file | 
   | dokumentasi_file | absensi_file | dibuat_oleh
```

#### 3. `hasil_fasilitasi` ⭐ NEW
```sql
id | permohonan_id | draft_file | final_file | catatan | dibuat_oleh | updated_by
```
- Unique: 1 permohonan = 1 hasil

#### 4. `penetapan_perda` ⭐ NEW
```sql
id | permohonan_id | nomor_perda | tanggal_penetapan | file_perda | dibuat_oleh
```
- Dokumen final dari Kab/Kota
- Unique: 1 permohonan = 1 penetapan

---

### **F. FASILITASI (EVALUASI)** ⭐ NEW

#### 1. `fasilitasi_bab`
```sql
id | permohonan_id | bab_id | catatan | dibuat_oleh | updated_by
```
- Masukan per bab dokumen
- Unique: `(permohonan_id, bab_id)`

#### 2. `fasilitasi_urusan`
```sql
id | permohonan_id | urusan_id | kondisi_umum | permasalahan | 
   | analisis_kinerja | kesesuaian_dokumen | rekomendasi | dibuat_oleh | updated_by
```
- Evaluasi per urusan pemerintahan (32 urusan)
- Unique: `(permohonan_id, urusan_id)`

---

## 🔄 MIGRATION SEQUENCE

```bash
2025_12_04_000001_refactor_permohonan_table.php
2025_12_04_000002_create_permohonan_tahapan_tables.php
2025_12_04_000003_create_assignment_tables.php
2025_12_04_000004_create_dokumen_tahapan_tables.php
2025_12_04_000005_create_jadwal_pelaksanaan_tables.php
2025_12_04_000006_create_fasilitasi_tables.php
2025_12_04_000007_update_master_kelengkapan_verifikasi.php
```

---

## ⚠️ BREAKING CHANGES

### 1. **Tabel `permohonan`**
- ❌ Removed: `nomor_permohonan`, `nama_dokumen`, `tanggal_permohonan`, `keterangan`
- ❌ Removed: `status` (13 values) → replaced by `status_akhir` (4 values)
- ❌ Removed: 7 timestamp columns (`submitted_at`, `verified_at`, ...)
- ❌ Removed: `jenis_dokumen_id` FK → replaced by enum
- ❌ Removed: `jadwal_fasilitasi_id`, `verifikator_id`, `pokja_id` FK
- ✅ Added: `tahun`, `jenis_dokumen` (enum), `status_akhir` (enum)
- ✅ Renamed: `kabupaten_kota_id` → `kab_kota_id`

### 2. **Tabel `permohonan_dokumen`**
- ⚠️ Akan di-refactor atau di-deprecate
- Diganti dengan `dokumen_tahapan`

### 3. **Tabel `jadwal_fasilitasi`**
- ❌ Dropped & recreated dengan struktur berbeda
- **SEBELUM**: Global schedule (tahun_anggaran_id, jenis_dokumen_id)
- **SEKARANG**: Per permohonan (permohonan_id)

### 4. **Tabel `evaluasi`**
- ⚠️ Perlu disesuaikan atau diganti dengan `fasilitasi_bab` + `fasilitasi_urusan`

---

## 📋 CHECKLIST IMPLEMENTASI

### Phase 1: Database Migration
- [x] Buat migration files
- [ ] Review & test migration rollback
- [ ] Backup database sebelum migrate
- [ ] Run migrations
- [ ] Seed master_tahapan data
- [ ] Seed master_kelengkapan_verifikasi dengan kategori

### Phase 2: Model Refactoring
- [ ] Update `Permohonan` model
- [ ] Create `PermohonanTahapan` model
- [ ] Create `PermohonanTahapanLog` model
- [ ] Create `DokumenTahapan` model
- [ ] Create assignment models (3x)
- [ ] Create fasilitasi models (2x)
- [ ] Update relationships

### Phase 3: Controller & Service
- [ ] Refactor `PermohonanController`
- [ ] Create `WorkflowService` untuk handle tahapan
- [ ] Create `DokumenTahapanController`
- [ ] Update `VerifikasiController`
- [ ] Update `EvaluasiController` → `FasilitasiController`
- [ ] Update assignment logic

### Phase 4: Views
- [ ] Update permohonan views (index, show, create, edit)
- [ ] Create tahapan tracking views
- [ ] Update dokumen upload views
- [ ] Create fasilitasi views (bab & urusan)
- [ ] Update dashboard widgets

### Phase 5: Testing & Migration
- [ ] Data migration script (old → new structure)
- [ ] Integration testing
- [ ] User acceptance testing
- [ ] Deploy ke production

---

## 🚀 KEUNTUNGAN JANGKA PANJANG

1. **Fleksibilitas**: Mudah menambah/modifikasi tahapan tanpa alter table
2. **Audit Trail**: Lengkap tracking setiap perubahan status
3. **Scalability**: Assignment terpisah, tidak bloat tabel permohonan
4. **Clarity**: Dokumen jelas terikat ke tahapan mana
5. **Maintainability**: Struktur lebih modular dan clean
6. **Reporting**: Mudah generate laporan per tahapan

---

## 📚 REFERENSI

- Master Tahapan: `master_tahapan` table
- Master Kelengkapan: `master_kelengkapan_verifikasi` table
- Master Bab: `master_bab` table
- Master Urusan: `master_urusan` table (32 urusan)

---

**Catatan**: Sebelum run migration di production, pastikan:
1. ✅ Backup database lengkap
2. ✅ Test di development environment
3. ✅ Buat rollback plan
4. ✅ Koordinasi dengan tim untuk downtime
