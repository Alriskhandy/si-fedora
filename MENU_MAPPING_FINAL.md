# 🎯 MAPPING MENU FINAL - SIFEDORA

## ✅ ARSITEKTUR SUDAH BENAR
Struktur menu sudah disederhanakan dan semua proses granular dipindahkan ke **TAB** dalam halaman detail fasilitasi.

---

## 📋 MENU → HALAMAN → TAB (FINAL MAPPING)

### 🌍 GLOBAL (SEMUA ROLE)

#### 🏠 Dashboard ✅
**Route**: `dashboard`  
**File**: `resources/views/dashboard.blade.php`

**Konten**:
- Ringkasan jumlah fasilitasi (Diajukan, Proses, Revisi, Selesai)
- To-do list sesuai role
- Notifikasi singkat (badge)
- ⚠️ **Tidak ada proses detail di sini**

Hasil Implementasi:
- Tampilan dashboard di file resources/views/pages/dashboard/index
- Memanggil partial sesuai role di ../pages/dashboard/partials

---

### � PERSIAPAN FASILITASI (Menu Admin)

#### 📅 Jadwal Fasilitasi (Menu Terpisah)
**Route**: `jadwal.index`  
**File**: `resources/views/jadwal/index.blade.php`

**Konten**:
- Buat jadwal fasilitasi global (RKPD 2026, RPJMD, dll)
- Tentukan periode waktu penerimaan permohonan
- Status aktif/tidak aktif
- Publikasi jadwal untuk semua Pemda

**Alur**: Admin membuat jadwal → Pemohon melihat jadwal → Pemohon ajukan permohonan

---

#### 👥 Tim FEDORA (Menu Terpisah)
**Route**: `tim-assignment.index`  
**File**: `resources/views/tim-assignment/index.blade.php`

**Konten**:
- Bentuk tim fasilitator untuk periode tertentu
- Assign koordinator tim
- Assign fasilitator per urusan pemerintahan
- Generate SK Tim
- Status tim: Aktif/Tidak Aktif

**Alur**: Admin bentuk tim → Tim siap → Admin assign tim ke permohonan yang masuk

---

### 📂 FASILITASI / EVALUASI (Menu Utama)

#### 📄 Halaman Index
**Route**: `permohonan.index`  
**File**: `resources/views/permohonan/index.blade.php`

**Konten**:
- Tabel daftar permohonan yang masuk
- Filter: Status, Tahun, Pemda
- Kolom: Pemda, Tahun, Jenis, Status, Tahapan Aktif

**Menggantikan Menu Lama**:
- ❌ Permohonan (menu terpisah)
- ❌ ~~Jadwal (menu terpisah)~~ → **TETAP TERPISAH** ✅
- ❌ Surat Pemberitahuan (menu terpisah)
- ❌ ~~Tim FEDORA (menu terpisah)~~ → **TETAP TERPISAH** ✅
- ❌ Undangan Pelaksanaan (menu terpisah)
- ❌ Validasi Hasil (menu terpisah)
- ❌ Perpanjangan Waktu (menu terpisah)

---

#### ⭐ Halaman Detail (INTI SISTEM)
**Route**: `permohonan.show`  
**File**: `resources/views/permohonan/show-with-tabs.blade.php`

**Header**:
- Nama Pemda
- Jenis Dokumen
- Tahun
- Status (badge besar & jelas)
- Timeline Aktivitas (sidebar kanan)

---

## 🔖 TAB DALAM DETAIL FASILITASI

### 1️⃣ Tab Overview (Permohonan)
**File Tab**: `resources/views/permohonan/tabs/overview.blade.php`  
**ID**: `#permohonan`

**Konten**:
- Informasi permohonan lengkap
- Status saat ini
- Timeline aktivitas
- Alert/Notifikasi status

**Menggantikan**: 
- Menu "Permohonan" yang terpisah

---

### 2️⃣ Tab Verifikasi & Dokumen
**File Tab**: `resources/views/permohonan/tabs/dokumen.blade.php`  
**ID**: `#verifikasi`

**Konten**:
- Upload dokumen persyaratan
- Checklist kelengkapan dokumen
- Riwayat upload
- Status verifikasi per dokumen
- Catatan verifikator
- Tombol: Terima / Minta Revisi
- **Sub-bagian Perpanjangan Waktu** (jika diperlukan)

**Menggantikan**:
- ❌ Menu "Laporan Verifikasi"
- ❌ Menu "Perpanjangan Waktu"

**File Terkait**:
- `resources/views/permohonan/tabs/verifikasi.blade.php`
- `resources/views/permohonan/tabs/perpanjangan.blade.php`

---

### 3️⃣ Tab Jadwal & Tim
**File Tab**: `resources/views/permohonan/tabs/jadwal.blade.php`  
**ID**: `#jadwal`

**Konten**:
- **Pilih jadwal fasilitasi** yang sudah dibuat (dropdown)
- **Assign tim** yang sudah dibentuk ke permohonan ini
- Lihat detail tim yang bertugas (koordinator & fasilitator)
- Lihat jadwal pelaksanaan
- Generate undangan khusus untuk permohonan ini
- Kirim notifikasi ke tim & pemohon

**CATATAN PENTING**:
- ⚠️ **Jadwal Fasilitasi** tetap sebagai **MENU TERPISAH** (buat jadwal global)
- ⚠️ **Tim FEDORA** tetap sebagai **MENU TERPISAH** (bentuk tim)
- ✅ Tab ini hanya untuk **ASSIGN** jadwal & tim yang sudah ada ke permohonan ini

**Menggantikan**:
- ❌ Menu "Surat Pemberitahuan" (undangan)
- ❌ Menu "Undangan Pelaksanaan"

---

### 4️⃣ Tab Pelaksanaan
**File Tab**: `resources/views/permohonan/tabs/pelaksanaan.blade.php`  
**ID**: `#pelaksanaan`

**Konten**:
- Upload dokumen pelaksanaan (luring)
- Notulensi
- Dokumentasi foto
- Daftar hadir / absensi
- Berita acara

**Menggantikan**:
- ❌ Menu "Undangan Pelaksanaan"

---

### 5️⃣ Tab Hasil Fasilitasi
**File Tab**: `resources/views/permohonan/tabs/hasil.blade.php`  
**ID**: `#hasil`

**Konten**:
- Input masukan per BAB (sistematika)
- Input masukan per Urusan
- Draft hasil fasilitasi
- Preview hasil fasilitasi
- Validasi hasil oleh koordinator

**Menggantikan**:
- ❌ Menu "Validasi Hasil"
- ❌ Menu "Hasil Fasilitasi" (terpisah)

---

### 6️⃣ Tab Penetapan & Surat
**File Tab**: `resources/views/permohonan/tabs/penetapan.blade.php`  
**ID**: `#penetapan`

**Konten**:
- Generate surat penyampaian hasil
- Upload surat penyampaian hasil
- Status penetapan PERDA/PERKADA
- Nomor penetapan
- Tanggal penetapan
- Download dokumen final

**Menggantikan**:
- ❌ Menu "Surat Penyampaian Hasil" (sebagian, untuk input)

**Catatan**: Menu "Surat Penyampaian Hasil" dan "PERDA/PERKADA" tetap ada sebagai **menu terpisah di bagian Dokumen** untuk akses cepat read-only semua fasilitasi.

---

### 7️⃣ Tab Tindak Lanjut
**File Tab**: `resources/views/permohonan/tabs/tindak-lanjut.blade.php`  
**ID**: `#tindak-lanjut`

**Konten**:
- Upload laporan tindak lanjut
- Status penyelesaian tindak lanjut
- Catatan tindak lanjut
- Riwayat upload

**Menggantikan**:
- ❌ Menu "Tindak Lanjut" (terpisah untuk pemohon)

---

### 8️⃣ Tab Riwayat (Opsional - belum ada)
**File Tab**: *Belum dibuat*  
**ID**: `#riwayat`

**Konten** (Usulan):
- Log aktivitas lengkap
- Timestamp setiap aksi
- User yang melakukan
- Perubahan status
- Audit trail

---

## 📁 MENU DOKUMEN (Read-Only Global)

### 📄 Surat Penyampaian Hasil
**Route**: `public.surat-penyampaian-hasil`  
**File**: `resources/views/surat-penyampaian-hasil/index.blade.php`

**Konten**:
- Daftar surat hasil fasilitasi dari semua Pemda
- Filter: Tahun, Pemda, Status
- Preview & Download PDF
- **Read-only** untuk semua role

---

### 📄 PERDA / PERKADA
**Route**: `public.penetapan-perda`  
**File**: `resources/views/penetapan-perda/index.blade.php`

**Konten**:
- Dokumen final yang sudah ditetapkan
- Filter: Tahun, Pemda
- Preview & Download
- **Read-only** untuk semua role

---

## 👥 MANAJEMEN USER (Admin Role)

### 📄 Akun Pengguna
**Route**: `users.index`  
**File**: `resources/views/users/index.blade.php`

**Konten**:
- CRUD akun pengguna
- Assign role
- Assign Pemda (untuk pemohon)
- Status aktif/nonaktif

---

### 📄 Role & Permission
**Route**: `admin.roles.index`  
**File**: `resources/views/admin/roles/index.blade.php`

**Konten**:
- Matrix role ↔ permission
- Kelola permission
- Tidak menyentuh data transaksi

---

## 🧱 MASTER DATA

### 📄 Kabupaten/Kota
**Route**: `kabupaten-kota.index`

### 📄 Urusan Pemerintahan
**Route**: `master-urusan.index`

### 📄 Master Lainnya (Dropdown)
- Jenis Dokumen (`master-jenis-dokumen.index`)
- Sistematika/BAB (`master-bab.index`)
- Tahapan (`master-tahapan.index`)
- Kelengkapan Verifikasi (`master-kelengkapan.index`)

**Fungsi**: Dipakai di seluruh tab fasilitasi

---

## 📊 LAPORAN & SISTEM

### 📄 Laporan Rekap
**Route**: *Belum dibuat*

**Konten**:
- Rekap fasilitasi per tahun
- Rekap per status
- Rekap per Pemda
- Export PDF/Excel

---

### 📄 Audit Log
**Route**: *Belum dibuat*

**Konten**:
- User yang melakukan aksi
- Objek fasilitasi
- Jenis aksi
- Timestamp

---

### ⚙️ Pengaturan Sistem
**Route**: *Belum dibuat*

**Konten**:
- Tahun aktif
- Template surat
- Pengaturan notifikasi
- Konfigurasi sistem

---

## 🔔 NOTIFIKASI

### 📄 Notifikasi
**Route**: `my-undangan.index`  
**File**: `resources/views/my-undangan/index.blade.php`

**Konten**:
- Undangan fasilitasi
- Notifikasi verifikasi
- Perubahan status
- Daftar tugas

**Note**: Untuk role Verifikator & Fasilitator
🔄 ALUR KERJA YANG BENAR

### Admin PERAN:
1. **Buat Jadwal Fasilitasi** (Menu: Jadwal Fasilitasi)
   - Contoh: Jadwal Fasilitasi RKPD 2026
   - Tentukan periode: 1 Jan - 31 Mar 2026
   - Publikasi jadwal

2. **Bentuk Tim FEDORA** (Menu: Tim FEDORA)
   - Bentuk tim untuk periode 2026
   - Assign koordinator: User A
   - Assign fasilitator per urusan
   - Generate SK Tim
   - Aktifkan tim

3. **Tunggu Permohonan Masuk** (Menu: Fasilitasi / Evaluasi)
   - Pemohon lihat jadwal yang sudah dipublikasi
   - Pemohon ajukan permohonan sesuai jadwal
   - Admin lihat permohonan masuk di tabel

4. **Proses Permohonan** (Klik Detail → Tab-tab)
   - Tab Verifikasi: Verifikasi dokumen
   - Tab Jadwal & Tim: **Assign** jadwal & tim yang sudah dibuat
   - Tab Pelaksanaan: Proses pelaksanaan
   - Tab Hasil: Input hasil fasilitasi
   - Tab Penetapan: Terbitkan surat hasil

### Pemohon:
1. **Lihat Jadwal** (Menu: Jadwal - jika ada akses, atau info dari dashboard)
2.✅ **Jadwal Fasilitasi** → **TETAP MENU TERPISAH** (buat jadwal global)
- ✅ **Tim FEDORA** → **TETAP MENU TERPISAH** (bentuk tim)
- ❌ ~~Surat Pemberitahuan~~ → Tab Jadwal & Tim (assign & undangan)

---

## ✅ KESIMPULAN

### Prinsip Arsitektur Final:
1. **Menu Master** = Persiapan (Jadwal & Tim dibuat dulu)
2. **Menu Fasilitasi** = Entry point permohonan
3. **Detail Fasilitasi** (`permohonan.show`) = Workflow lengkap per permohonan
4. **Tab** = Tahapan proses (assign, bukan create)l:
1. **Menu** = Konteks kerja
2. **Detail Fasilitasi** (`permohonan.show`) = Workflow lengkap
3. **Tab** = Tahapan proses

### Status Implementasi:
- ✅ Struktur menu sudah BENAR dan disederhanakan
- ✅ Tab sudah ada dan lengkap di `permohonan/show-with-tabs.blade.php`
- ✅ File tab terpisah di `permohonan/tabs/`
- ⚠️ Tab "Riwayat" belum dibuat (opsional)
- ⚠️ Beberapa menu laporan & sistem belum dibuat (future)

### File Tab yang Sudah Ada:
1. ✅ `overview.blade.php` - Tab Permohonan
2. ✅ `dokumen.blade.php` - Tab Verifikasi & Dokumen
3. ✅ `verifikasi.blade.php` - Detail verifikasi
4. ✅ `perpanjangan.blade.php` - Sub-bagian perpanjangan waktu
5. ✅ `jadwal.blade.php` - Tab Jadwal & Tim
6. ✅ `pelaksanaan.blade.php` - Tab Pelaksanaan
7. ✅ `hasil.blade.php` - Tab Hasil Fasilitasi
8. ✅ `penetapan.blade.php` - Tab Penetapan & Surat
9. ✅ `tindak-lanjut.blade.php` - Tab Tindak Lanjut

### Menu Lama yang Sudah Dipindahkan ke Tab:
- ❌ ~~Jadwal Fasilitasi~~ → Tab Jadwal & Tim
- ❌ ~~Surat Pemberitahuan~~ → Tab Jadwal & Tim (undangan)
- ❌ ~~Tim FEDORA~~ → Tab Jadwal & Tim
- ❌ ~~Laporan Verifikasi~~ → Tab Verifikasi
- ❌ ~~Undangan Pelaksanaan~~ → Tab Pelaksanaan
- ❌ ~~Validasi Hasil~~ → Tab Hasil Fasilitasi
- ❌ ~~Perpanjangan Waktu~~ → Sub-bagian di Tab Verifikasi
- ❌ ~~Hasil Fasilitasi~~ → Tab Hasil Fasilitasi
- ❌ ~~Tindak Lanjut~~ → Tab Tindak Lanjut

---

## 🎯 NEXT STEPS (Jika diperlukan)

1. **Tab Riwayat** (Opsional)
   - Buat file: `resources/views/permohonan/tabs/riwayat.blade.php`
   - Tambahkan di `show-with-tabs.blade.php`
   - Tampilkan activity log lengkap

2. **Laporan & Sistem**
   - Implementasi halaman Laporan Rekap
   - Implementasi Audit Log
   - Implementasi Pengaturan Sistem

3. **Optimasi UX**
   - Badge counter di tab (jumlah dokumen belum lengkap, dll)
   - Progress indicator per tab
   - Auto-save draft

---

**Dokumentasi ini memastikan bahwa SEMUA menu lama sudah terakomodasi dalam struktur baru tanpa kehilangan fungsionalitas.**
