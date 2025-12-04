# Ringkasan Update Model

Dokumen ini merangkum semua perubahan model yang telah dilakukan untuk menyesuaikan dengan struktur database baru (migrasi 2025_12_04_000001 - 000007).

## 1. Model yang Diupdate

### 1.1 Permohonan.php ✅

**Perubahan:**

-   Removed fillable: `nomor_permohonan`, `nama_dokumen`, `keterangan`, `tanggal_permohonan`, `status`, semua timestamp tracking (`submitted_at`, `verified_at`, dst), `jadwal_fasilitasi_id`, `verifikator_id`, `pokja_id`, `jenis_dokumen_id`, `tahun_anggaran_id`
-   Added fillable: `kab_kota_id` (rename dari kabupaten_kota_id), `tahun`, `jenis_dokumen`, `status_akhir`
-   Updated casts: Hanya `tahun` sebagai integer
-   Updated status labels: Dari 13 status menjadi 4 status (belum, proses, revisi, selesai)
-   Removed relasi: `jenisDokumen()`, `tahunAnggaran()`, `jadwalFasilitasi()` (yang lama)
-   Added relasi baru: `tahapan()`, `koordinator()`, `timFasilitasi()`, `timVerifikasi()`, `dokumenTahapan()`, `jadwalFasilitasi()` (yang baru), `pelaksanaanCatatan()`, `hasilFasilitasi()`, `penetapanPerda()`, `fasilitasiBab()`, `fasilitasiUrusan()`
-   Added helper methods: `getCurrentTahapan()`, `isTahapanSelesai()`
-   Added scopes: `byJenisDokumen()`, `byTahun()`, `byKabKota()`, `byStatus()`

### 1.2 JadwalFasilitasi.php ✅

**Perubahan:**

-   Complete rewrite dari global scheduling ke per-permohonan scheduling
-   Removed fillable: `tahun_anggaran_id`, `jenis_dokumen_id`, `nama_kegiatan`, `tanggal_mulai`, `tanggal_selesai`, `batas_permohonan`, `status`, `created_by`, `updated_by`
-   Added fillable: `permohonan_id`, `tanggal_pelaksanaan`, `tempat`, `undangan_file`, `keterangan`, `dibuat_oleh`
-   Removed SoftDeletes trait
-   Updated casts: Hanya `tanggal_pelaksanaan` sebagai date
-   Updated relasi: `tahunAnggaran()` → removed, `jenisDokumen()` → removed, `createdBy()` → `dibuatOleh()`, added `permohonan()`

### 1.3 MasterKelengkapanVerifikasi.php ✅

**Perubahan:**

-   Added fillable: `kategori`, `tahapan_id`, `urutan`
-   Added casts: `urutan` sebagai integer
-   Added relasi: `tahapan()`, `dokumenVerifikasiDetail()`
-   Removed relasi: `permohonanDokumen()` (deprecated)
-   Added scopes: `suratPermohonan()`, `kelengkapanVerifikasi()`, `byTahapan()`, `ordered()`
-   Existing scopes tetap: `wajib()`, `opsional()`

### 1.4 MasterTahapan.php ✅

**Perubahan:**

-   Updated fillable: Dari `nama_tahapan` menjadi `kode`, `nama`, `deskripsi`, `urutan`
-   Updated relasi: Dari `permohonan()` menjadi `permohonanTahapan()`, added `masterKelengkapan()`
-   Added scope: `ordered()`

### 1.5 MasterUrusan.php ✅

**Perubahan:**

-   Updated fillable: Dari `nama_urusan`, `kategori`, `urutan` menjadi `kode`, `nama`, `deskripsi`
-   Removed: kategori constants dan casts
-   Updated relasi: Dari `permohonan()` menjadi `fasilitasiUrusan()`
-   Removed scopes: `wajibDasar()`, `wajibNonDasar()`, `pilihan()`

## 2. Model Baru yang Dibuat

### 2.1 PermohonanTahapan.php ✅

**Purpose:** Tracking status permohonan per tahapan
**Fillable:** `permohonan_id`, `tahapan_id`, `status`, `tgl_mulai`, `tgl_selesai`, `catatan`, `koordinator_id`
**Relasi:** `permohonan()`, `masterTahapan()`, `koordinator()`, `logs()`
**Scopes:** `byStatus()`, `belum()`, `proses()`, `revisi()`, `selesai()`

### 2.2 PermohonanTahapanLog.php ✅

**Purpose:** Audit trail perubahan status tahapan
**Fillable:** `permohonan_tahapan_id`, `status_sebelumnya`, `status_baru`, `catatan`, `diubah_oleh`
**Relasi:** `permohonanTahapan()`, `diubahOleh()`
**Scopes:** `latest()`

### 2.3 KoordinatorAssignment.php ✅

**Purpose:** Assignment koordinator per permohonan
**Fillable:** `permohonan_id`, `koordinator_id`, `ditugaskan_oleh`
**Relasi:** `permohonan()`, `koordinator()`, `ditugaskanOleh()`

### 2.4 TimFasilitasiAssignment.php ✅

**Purpose:** Assignment anggota tim fasilitasi
**Fillable:** `permohonan_id`, `user_id`, `peran`, `ditugaskan_oleh`
**Relasi:** `permohonan()`, `user()`, `ditugaskanOleh()`
**Scopes:** `byPeran()`, `ketuaTim()`, `anggotaTim()`

### 2.5 TimVerifikasiAssignment.php ✅

**Purpose:** Assignment anggota tim verifikasi
**Fillable:** `permohonan_id`, `user_id`, `ditugaskan_oleh`
**Relasi:** `permohonan()`, `user()`, `ditugaskanOleh()`

### 2.6 DokumenTahapan.php ✅

**Purpose:** Dokumen yang diupload per tahapan
**Fillable:** `permohonan_id`, `tahapan_id`, `jenis_dokumen`, `nama_file`, `file_path`, `keterangan`, `diunggah_oleh`
**Relasi:** `permohonan()`, `masterTahapan()`, `diunggahOleh()`, `verifikasiDetail()`, `revisi()`
**Scopes:** `byJenis()`, `suratPermohonan()`, `kelengkapanVerifikasi()`, `dokumenPendukung()`

### 2.7 DokumenVerifikasiDetail.php ✅

**Purpose:** Detail verifikasi per item kelengkapan
**Fillable:** `dokumen_tahapan_id`, `master_kelengkapan_id`, `status_verifikasi`, `catatan_verifikasi`, `diverifikasi_oleh`
**Relasi:** `dokumenTahapan()`, `masterKelengkapan()`, `diverifikasiOleh()`
**Scopes:** `lengkap()`, `tidakLengkap()`, `tidakSesuai()`

### 2.8 DokumenRevisi.php ✅

**Purpose:** Tracking revisi dokumen (versioning)
**Fillable:** `dokumen_tahapan_id`, `versi`, `catatan_revisi`, `file_path`, `diunggah_oleh`
**Relasi:** `dokumenTahapan()`, `diunggahOleh()`
**Scopes:** `latestVersion()`

### 2.9 PelaksanaanCatatan.php ✅

**Purpose:** Catatan pelaksanaan fasilitasi
**Fillable:** `permohonan_id`, `catatan`, `peserta`, `hasil_pembahasan`, `dokumentasi`, `dibuat_oleh`
**Casts:** `peserta` as array
**Relasi:** `permohonan()`, `dibuatOleh()`

### 2.10 HasilFasilitasi.php ✅

**Purpose:** Kesimpulan dan rekomendasi hasil fasilitasi
**Fillable:** `permohonan_id`, `kesimpulan`, `rekomendasi`, `catatan_khusus`, `lampiran_file`, `dibuat_oleh`
**Relasi:** `permohonan()`, `dibuatOleh()`

### 2.11 PenetapanPerda.php ✅

**Purpose:** Data penetapan perda/perkada
**Fillable:** `permohonan_id`, `nomor_perda`, `tanggal_penetapan`, `judul`, `file_perda`, `keterangan`, `dibuat_oleh`
**Relasi:** `permohonan()`, `dibuatOleh()`

### 2.12 FasilitasiBab.php ✅

**Purpose:** Tracking pembahasan per bab dokumen
**Fillable:** `permohonan_id`, `nomor_bab`, `judul_bab`, `catatan_fasilitasi`, `status_pembahasan`
**Relasi:** `permohonan()`
**Scopes:** `ordered()`, `belumDibahas()`, `sedangDibahas()`, `selesaiDibahas()`, `perluRevisi()`

### 2.13 FasilitasiUrusan.php ✅

**Purpose:** Tracking pembahasan per urusan
**Fillable:** `permohonan_id`, `master_urusan_id`, `catatan_fasilitasi`, `status_pembahasan`
**Relasi:** `permohonan()`, `masterUrusan()`
**Scopes:** `belumDibahas()`, `sedangDibahas()`, `selesaiDibahas()`, `perluRevisi()`

## 3. Status Model Deprecated

Lihat file `DEPRECATED_MODELS.md` untuk detail lengkap. Ringkasan:

**Model yang harus dihapus:**

-   ❌ Evaluasi.php
-   ❌ PermohonanDokumen.php
-   ❌ PersyaratanDokumen.php
-   ❌ JenisDokumen.php
-   ❌ TahunAnggaran.php
-   ❌ SuratRekomendasi.php
-   ❌ TimPokja.php
-   ❌ PokjaAnggota.php

**Model yang perlu review:**

-   ⚠️ SuratPemberitahuan.php
-   ⚠️ MasterBab.php

**Model yang tidak berubah:**

-   ✅ KabupatenKota.php
-   ✅ User.php
-   ✅ TemporaryRoleAssignment.php

## 4. Checklist Verifikasi

### Model Update ✅

-   [x] Permohonan.php - Updated
-   [x] JadwalFasilitasi.php - Rewritten
-   [x] MasterKelengkapanVerifikasi.php - Updated
-   [x] MasterTahapan.php - Updated
-   [x] MasterUrusan.php - Updated

### New Models ✅

-   [x] PermohonanTahapan.php
-   [x] PermohonanTahapanLog.php
-   [x] KoordinatorAssignment.php
-   [x] TimFasilitasiAssignment.php
-   [x] TimVerifikasiAssignment.php
-   [x] DokumenTahapan.php
-   [x] DokumenVerifikasiDetail.php
-   [x] DokumenRevisi.php
-   [x] PelaksanaanCatatan.php
-   [x] HasilFasilitasi.php
-   [x] PenetapanPerda.php
-   [x] FasilitasiBab.php
-   [x] FasilitasiUrusan.php

### Documentation ✅

-   [x] DEPRECATED_MODELS.md - Created
-   [x] MODEL_UPDATE_SUMMARY.md - This file

## 5. Next Steps

1. ⏳ **Update Controllers** - Controllers yang menggunakan model deprecated perlu diupdate
2. ⏳ **Update Views** - Views yang menggunakan model deprecated perlu diupdate
3. ⏳ **Update Seeders** - Seeders yang menggunakan model deprecated perlu diupdate
4. ⏳ **Update Tests** - Tests yang menggunakan model deprecated perlu diupdate
5. ⏳ **Run Migration** - Jalankan migrasi untuk menerapkan perubahan database
6. ⏳ **Test Application** - Test semua fungsi aplikasi dengan struktur baru
7. ⏳ **Delete Deprecated Models** - Hapus model deprecated setelah semua update selesai

## 6. Catatan Penting

⚠️ **Breaking Changes:**

-   Model `Permohonan` memiliki perubahan signifikan pada fillable dan relasi
-   Model `JadwalFasilitasi` benar-benar berbeda (global → per-permohonan)
-   Semua kode yang menggunakan `jenis_dokumen_id` perlu diubah ke enum `jenis_dokumen`
-   Semua kode yang menggunakan `tahun_anggaran_id` perlu diubah ke integer `tahun`
-   Status permohonan dari 13 status menjadi 4 status di `status_akhir`
-   Workflow tracking sekarang di tabel `permohonan_tahapan`, bukan di kolom `status` permohonan

⚠️ **Database Migration:**

-   Migrasi bersifat destructive (drop columns & tables)
-   Tidak untuk production dengan data existing
-   Hanya untuk fresh migration atau development

⚠️ **Relasi Changes:**

-   Banyak relasi `belongsTo` yang berubah atau dihapus
-   Controller perlu update untuk eager loading relasi baru
-   View perlu update untuk mengakses data dari relasi baru
