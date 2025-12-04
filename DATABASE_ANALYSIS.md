# 📊 ANALISIS & REFACTORING DATABASE

## Executive Summary

Telah dilakukan analisis menyeluruh terhadap struktur database existing dan dibuat refactoring komprehensif untuk mengubah dari **workflow monolitik** menjadi **workflow berbasis tahapan dinamis**.

---

## 🎯 Permasalahan Utama (Existing Structure)

### 1. **Tabel `permohonan`** - Terlalu Bloated
```sql
-- 60+ kolom dengan 13 enum status, 7 timestamp tracking
status: enum(draft, submitted, verified, revision_required, assigned, 
             in_evaluation, draft_recommendation, approved_by_kaban, 
             letter_issued, sent, follow_up, completed, rejected)
```
**Masalah:**
- ❌ Workflow hardcoded, tidak fleksibel
- ❌ Sulit menambah/modifikasi tahapan
- ❌ Status tracking tidak terstruktur
- ❌ Banyak kolom timestamp yang membingungkan

### 2. **Dokumen Tidak Terikat Tahapan**
```sql
permohonan_dokumen
├── permohonan_id
├── persyaratan_dokumen_id (deprecated)
└── (tidak ada tahapan_id)
```
**Masalah:**
- ❌ Dokumen flat, tidak jelas untuk tahap apa
- ❌ Tidak bisa track dokumen per tahapan
- ❌ Sulit validasi kelengkapan per tahap

### 3. **Assignment Tidak Terpisah**
```sql
permohonan
├── verifikator_id (hanya 1 orang)
├── pokja_id (hanya 1 tim)
└── (tidak ada koordinator, tidak bisa multiple tim)
```
**Masalah:**
- ❌ Tidak scalable untuk multiple assignee
- ❌ Tidak ada history assignment
- ❌ Tidak ada role koordinator

---

## ✅ Solusi: Struktur Database Baru

### **A. Simplified `permohonan` Table**
```sql
permohonan
├── id
├── kab_kota_id           → FK kabupaten_kota
├── tahun                 → integer (2025, 2026, ...)
├── jenis_dokumen         → enum(rkpd, rpd, rpjmd)
├── status_akhir          → enum(belum, proses, revisi, selesai)
├── created_by, updated_by
└── timestamps
```
**Keuntungan:**
- ✅ Simpel, hanya data esensial
- ✅ Workflow dipindah ke tabel terpisah
- ✅ Easy to understand

---

### **B. Dynamic Workflow System**

#### 1. `permohonan_tahapan` - Track Status Per Tahapan
```sql
permohonan_tahapan
├── permohonan_id         → FK permohonan
├── tahapan_id            → FK master_tahapan (1-10)
├── status                → enum(belum, proses, revisi, selesai)
├── catatan               → text
└── updated_by, timestamps
```

#### 2. `permohonan_tahapan_log` - Audit Trail
```sql
permohonan_tahapan_log
├── permohonan_tahapan_id
├── status_lama → status_baru
├── keterangan
├── user_id
└── created_at
```

**Master Tahapan:**
1. Pengajuan Permohonan
2. Verifikasi Kelengkapan
3. Penugasan Tim
4. Penjadwalan Fasilitasi
5. Pelaksanaan Fasilitasi
6. Evaluasi dan Rekomendasi
7. Review dan Approval
8. Penerbitan Surat Rekomendasi
9. Pengiriman ke Daerah
10. Follow Up dan Penetapan

---

### **C. Document Management Per Stage**

#### 1. `dokumen_tahapan` - Dokumen Per Tahapan
```sql
dokumen_tahapan
├── permohonan_id
├── tahapan_id            → Dokumen untuk tahap berapa?
├── user_id               → Pengunggah
├── nama_dokumen, file_path
├── status                → enum(menunggu, diterima, ditolak)
└── catatan_verifikator, verified_by, verified_at
```

#### 2. `dokumen_verifikasi_detail` - Checklist Kelengkapan
```sql
dokumen_verifikasi_detail
├── dokumen_tahapan_id
├── master_kelengkapan_id → FK master_kelengkapan_verifikasi
├── status                → enum(lengkap, tidak_lengkap, revisi)
└── catatan, updated_by
```

#### 3. `dokumen_revisi` - History Revisi
```sql
dokumen_revisi
├── dokumen_tahapan_id
├── file_path
├── alasan_revisi
└── created_by, created_at
```

---

### **D. Assignment Tables (Separated)**

```sql
koordinator_assignment       → 1 permohonan : 1 koordinator
├── permohonan_id (UNIQUE)
└── koordinator_id

tim_fasilitasi_assignment    → 1 permohonan : N fasilitator
├── permohonan_id
└── user_id (role=tim_fasilitasi)

tim_verifikasi_assignment    → 1 permohonan : N verifikator
├── permohonan_id
└── user_id (role=verifikator)
```

---

### **E. Specific Stage Tables**

```sql
jadwal_fasilitasi            → Jadwal per permohonan (bukan global)
├── permohonan_id
├── tanggal_pelaksanaan, tempat
└── undangan_file

pelaksanaan_catatan          → Dokumentasi pelaksanaan
├── permohonan_id
├── berita_acara_file, notulensi_file
├── dokumentasi_file, absensi_file

hasil_fasilitasi             → Draft & final dokumen
├── permohonan_id (UNIQUE)
├── draft_file, final_file
└── catatan

penetapan_perda              → Dokumen penetapan final
├── permohonan_id (UNIQUE)
├── nomor_perda, tanggal_penetapan
└── file_perda
```

---

### **F. Fasilitasi/Evaluasi Tables**

```sql
fasilitasi_bab               → Masukan per bab dokumen
├── permohonan_id
├── bab_id → FK master_bab
└── catatan

fasilitasi_urusan            → Evaluasi per urusan (32 urusan)
├── permohonan_id
├── urusan_id → FK master_urusan
├── kondisi_umum, permasalahan
├── analisis_kinerja, kesesuaian_dokumen
└── rekomendasi
```

---

## 📦 Files Created

### Migration Files (7 files)
```
2025_12_04_000001_refactor_permohonan_table.php
2025_12_04_000002_create_permohonan_tahapan_tables.php
2025_12_04_000003_create_assignment_tables.php
2025_12_04_000004_create_dokumen_tahapan_tables.php
2025_12_04_000005_create_jadwal_pelaksanaan_tables.php
2025_12_04_000006_create_fasilitasi_tables.php
2025_12_04_000007_update_master_kelengkapan_verifikasi.php
```

### Seeder Files (3 files)
```
MasterTahapanSeeder.php          → 10 tahapan workflow
MasterKelengkapanSeeder.php      → 12 dokumen kelengkapan
MasterUrusanSeeder.php           → 32 urusan pemerintahan
```

### Documentation
```
DATABASE_REFACTORING.md          → Dokumentasi lengkap 200+ baris
```

---

## ⚠️ Breaking Changes

### Tabel yang Berubah Total:
1. ✅ `permohonan` - Simplified (kab_kota_id, tahun, jenis_dokumen, status_akhir)
2. ✅ `jadwal_fasilitasi` - Dari global → per permohonan
3. ⚠️ `permohonan_dokumen` - Deprecated, diganti `dokumen_tahapan`
4. ⚠️ `evaluasi` - Dapat diganti dengan `fasilitasi_bab` + `fasilitasi_urusan`

### Kolom yang Dihapus dari `permohonan`:
- ❌ nomor_permohonan, nama_dokumen, tanggal_permohonan, keterangan
- ❌ status (13 values) → diganti status_akhir (4 values)
- ❌ 7 timestamp tracking (submitted_at, verified_at, ...)
- ❌ jenis_dokumen_id FK → diganti enum
- ❌ jadwal_fasilitasi_id, verifikator_id, pokja_id FK

---

## 🚀 Keuntungan Struktur Baru

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| **Fleksibilitas** | Status hardcoded | Tahapan dinamis dari master |
| **Audit Trail** | Tidak ada | Lengkap di permohonan_tahapan_log |
| **Scalability** | Limited assignment | Multiple assignee per role |
| **Document Tracking** | Flat | Per tahapan dengan checklist |
| **Maintenance** | Sulit modify workflow | Easy add/remove tahapan |
| **Reporting** | Complex query | Simple join by tahapan |

---

## 📋 Next Steps

### Phase 1: Migration Preparation ⏳
- [ ] Backup database production
- [ ] Test migrations di development
- [ ] Prepare rollback script
- [ ] Run seeders (tahapan, kelengkapan, urusan)

### Phase 2: Model Refactoring 🔨
- [ ] Update Permohonan model & relationships
- [ ] Create 10 new models (PermohonanTahapan, DokumenTahapan, dll)
- [ ] Create WorkflowService untuk handle tahapan

### Phase 3: Controller & Logic 🎛️
- [ ] Refactor PermohonanController
- [ ] Create DokumenTahapanController
- [ ] Update VerifikasiController
- [ ] Create/Update FasilitasiController

### Phase 4: Views & Frontend 🎨
- [ ] Update permohonan views
- [ ] Create tahapan tracking UI
- [ ] Update dokumen upload views
- [ ] Create fasilitasi forms

### Phase 5: Data Migration 🔄
- [ ] Script migrasi data existing → new structure
- [ ] Testing & validation
- [ ] Deploy to production

---

## 📞 Support

Jika ada pertanyaan tentang refactoring ini, silakan:
1. Baca dokumentasi lengkap di `DATABASE_REFACTORING.md`
2. Review migration files di `database/migrations/2025_12_04_*`
3. Check seeder files di `database/seeders/Master*.php`

---

**Status**: ✅ Database Design Complete - Ready for Implementation
**Last Updated**: 4 Desember 2025
