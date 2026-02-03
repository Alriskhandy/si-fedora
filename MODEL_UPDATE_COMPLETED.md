# Model Update Summary

Semua model telah berhasil diperbaiki agar sesuai dengan migrations yang sudah disederhanakan.

## ✅ Model Yang Dihapus (11 files)

Model-model berikut telah dihapus karena tabel sudah digabung/dihapus:

1. **PersyaratanDokumen.php** - Tabel dihapus (redundan dengan master_kelengkapan_verifikasi)
2. **FasilitasiBab.php** - Tabel dihapus (digabung ke hasil_fasilitasi_detail)
3. **FasilitasiUrusan.php** - Tabel dihapus (digabung ke hasil_fasilitasi_detail)
4. **DokumenTahapan.php** - Digabung ke Dokumen.php
5. **DokumenVerifikasiDetail.php** - Tidak dipakai lagi
6. **PermohonanDokumen.php** - Digabung ke Dokumen.php
7. **KoordinatorAssignment.php** - Digabung ke PermohonanAssignments.php
8. **TimFasilitasiAssignment.php** - Digabung ke PermohonanAssignments.php
9. **TimVerifikasiAssignment.php** - Digabung ke PermohonanAssignments.php
10. **HasilFasilitasiSistematika.php** - Digabung ke HasilFasilitasiDetail.php
11. **HasilFasilitasiUrusan.php** - Digabung ke HasilFasilitasiDetail.php

## ✅ Model Baru Yang Dibuat (3 files)

### 1. Dokumen.php
Menggabungkan PermohonanDokumen dan DokumenTahapan dengan field:
- **Kategori enum**: permohonan, verifikasi, pelaksanaan, hasil
- **Status enum**: pending, verified, rejected, revision
- **Relasi**: permohonan, tahapan, kelengkapan, uploader, verifier, revisi
- **Methods**: verify(), reject(), requestRevision(), getFileUrl()
- **Scopes**: byKategori, byStatus, byTahapan, pending, verified, rejected, revision

### 2. PermohonanAssignments.php
Menggabungkan 3 assignment tables dengan field:
- **Role enum**: koordinator, fasilitasi, verifikasi
- **is_pic**: boolean untuk menandai PIC
- **Relasi**: permohonan, user, assignedBy
- **Methods**: setPIC(), removePIC(), isPIC()
- **Static Methods**: assignKoordinator(), assignFasilitasi(), assignVerifikasi(), getPIC()
- **Scopes**: byRole, koordinator, fasilitasi, verifikasi, pic

### 3. HasilFasilitasiDetail.php
Menggabungkan HasilFasilitasiSistematika dan HasilFasilitasiUrusan dengan field:
- **Tipe enum**: sistematika, urusan
- **master_bab_id**: untuk tipe sistematika
- **master_urusan_id**: untuk tipe urusan
- **sub_bab**: nullable untuk sub bab
- **Relasi**: hasilFasilitasi, masterBab, masterUrusan, creator
- **Static Methods**: createSistematika(), createUrusan(), getSistematika(), getUrusan()
- **Scopes**: byTipe, sistematika, urusan

## ✅ Model Yang Diupdate (8 files)

### 1. DokumenRevisi.php
- ❌ `dokumen_tahapan_id` → ✅ `dokumen_id`
- ❌ `diunggah_oleh` → ✅ `created_by`
- ❌ `dokumenTahapan()` → ✅ `dokumen()`
- ❌ `diunggahOleh()` → ✅ `creator()`
- ➕ Tambah field: file_name, file_size, file_type

### 2. JadwalFasilitasi.php
- ❌ `dibuat_oleh` → ✅ `created_by`
- ❌ `dibuatOleh()` → ✅ `creator()`
- ✅ `updater()` tetap

### 3. HasilFasilitasi.php
- ❌ `dibuat_oleh` → ✅ `created_by`
- ❌ `pembuat()` → ✅ `creator()`
- ❌ `hasilUrusan()` → ✅ `hasilDetail()`
- ❌ `hasilSistematika()` → ✅ Scope ke hasilDetail dengan where tipe
- ➕ Relasi baru ke HasilFasilitasiDetail

### 4. Permohonan.php
- ➕ `assignments()` - hasMany PermohonanAssignments
- ✅ `koordinator()` - update query ke PermohonanAssignments
- ✅ `timFasilitasi()` - update query ke PermohonanAssignments
- ✅ `timVerifikasi()` - update query ke PermohonanAssignments
- ❌ `dokumenTahapan()` → ✅ `dokumen()`
- ❌ `permohonanDokumen()` → ✅ `dokumen()` (sama)
- ❌ `fasilitasiBab()` → ✅ `fasilitasiDetail()` (hasManyThrough)
- ❌ `fasilitasiUrusan()` → dihapus

### 5. UndanganPelaksanaan.php
- ❌ `dibuat_oleh` → ✅ `created_by`
- ❌ Relasi dibuatOleh → ✅ creator()

### 6. PenetapanJadwalFasilitasi.php
- ❌ `ditetapkan_oleh` → ✅ `created_by`
- ❌ Relasi ditetapkanOleh → ✅ creator()

### 7. LaporanVerifikasi.php
- ❌ `dibuat_oleh` → ✅ `created_by`
- ❌ Relasi dibuatOleh → ✅ creator()

### 8. TindakLanjut.php
- ❌ `diupload_oleh` → ✅ `created_by`
- ❌ Relasi diuploadOleh → ✅ creator()

### 9. PelaksanaanCatatan.php
- ❌ `dibuat_oleh` → ✅ `created_by`
- ❌ Relasi dibuatOleh → ✅ creator()

### 10. PenetapanPerda.php
- ❌ `dibuat_oleh` → ✅ `created_by`
- ❌ Relasi dibuatOleh → ✅ creator()

## 📊 Ringkasan Perubahan

### Field Naming Standardization
Semua field creator sekarang konsisten menggunakan:
- ✅ `created_by` (bukan dibuat_oleh, diunggah_oleh, diupload_oleh, ditetapkan_oleh)
- ✅ `updated_by` (konsisten)
- ✅ Relasi method: `creator()` dan `updater()`

### Table Consolidation
- **3 assignment tables** → **1 table** (permohonan_assignments) dengan enum `role`
- **2 dokumen tables** → **1 table** (dokumen) dengan enum `kategori`
- **2 hasil detail tables** → **1 table** (hasil_fasilitasi_detail) dengan enum `tipe`
- **Fasilitasi tables** → dihapus (redundan)
- **Persyaratan dokumen** → dihapus (redundan)

### Relasi Yang Berubah
- DokumenRevisi → dokumen (bukan dokumenTahapan)
- Permohonan → assignments, dokumen (bukan koordinator/timFasilitasi/timVerifikasi terpisah)
- HasilFasilitasi → hasilDetail (bukan hasilSistematika/hasilUrusan terpisah)

## ⚠️ Breaking Changes

Controller/Service yang perlu diupdate:
1. **Assignment logic** - Ganti KoordinatorAssignment/TimFasilitasi/TimVerifikasi dengan PermohonanAssignments
2. **Document upload** - Ganti PermohonanDokumen/DokumenTahapan dengan Dokumen
3. **Hasil fasilitasi** - Ganti HasilFasilitasiSistematika/Urusan dengan HasilFasilitasiDetail
4. **All queries** using old field names (dibuat_oleh, etc) → created_by

## ✅ Keunggulan Setelah Update

1. **Konsistensi**: Semua field creator menggunakan created_by
2. **Simplifikasi**: 50+ tables → ~40 tables
3. **Flexibility**: Enum fields memudahkan query filtering
4. **Maintainability**: Lebih mudah dipahami dan dipelihara
5. **Performance**: Fewer joins, better indexing dengan enum
6. **Scalability**: Mudah ditambah role/kategori/tipe baru

---

**Status**: ✅ SELESAI - Semua model sudah sesuai dengan migrations yang disederhanakan
