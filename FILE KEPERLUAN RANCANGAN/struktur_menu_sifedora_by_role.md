
# Struktur Menu & Halaman Sistem SIFEDORA (Berdasarkan Role)

Dokumen ini merangkum **menu, halaman, dan konten yang ditampilkan** untuk setiap role pengguna pada Sistem Informasi Fasilitasi Dokumen Perencanaan Daerah (SIFEDORA).

---

## 1. Super Admin

### Menu & Halaman
- **Dashboard**
  - Ringkasan seluruh fasilitasi (semua status & pemda)
- **Manajemen User**
  - Data user, role, pemda, status
- **Master Data**
  - Pemda
  - Kategori Urusan
  - Urusan
- **Fasilitasi / Evaluasi**
  - Daftar & Detail seluruh fasilitasi
- **Laporan**
  - Rekap per tahun / pemda / status
- **Audit Log**
  - Riwayat aktivitas sistem
- **Pengaturan Sistem**
  - Tahun aktif, template, notifikasi

---

## 2. Kaban

### Menu & Halaman
- **Dashboard**
  - Fasilitasi yang menunggu penetapan / persetujuan
- **Fasilitasi / Evaluasi**
  - Detail fasilitasi (read + aksi keputusan)
- **Dokumen Final**
  - Surat hasil fasilitasi
  - PERDA / PERKADA
- **Laporan**
  - Rekap hasil fasilitasi

### Fokus Tampilan
- Status besar
- Tombol: Setujui, Tetapkan Jadwal

---

## 3. Admin Peran

### Menu & Halaman
- **Dashboard**
  - Semua proses aktif & notifikasi
- **Fasilitasi / Evaluasi**
  - Daftar semua fasilitasi
  - Detail fasilitasi (koordinasi proses)
- **Dokumen**
  - Upload & distribusi dokumen
- **Notifikasi**
- **Laporan**

### Fokus Tampilan
- Filter cepat (status, pemda, tahun)
- Timeline proses

---

## 4. Verifikator

### Menu & Halaman
- **Dashboard**
  - Antrian verifikasi
- **Verifikasi Dokumen**
  - Checklist dokumen
  - Catatan verifikasi
- **Fasilitasi / Evaluasi**
  - Detail (tab verifikasi saja)
- **Notifikasi**

### Fokus Tampilan
- Dokumen
- Tombol: Terima / Revisi

---

## 5. Fasilitator

### Menu & Halaman
- **Dashboard**
  - Jadwal fasilitasi berjalan
- **Fasilitasi Berjalan**
  - Detail fasilitasi
- **Hasil Fasilitasi**
  - Input masukan sistematika
  - Input masukan urusan
- **Notifikasi**

### Fokus Tampilan
- Form masukan
- Referensi urusan & bab

---

## 6. Pemohon (Kab/Kota)

### Menu & Halaman
- **Dashboard**
  - Status pengajuan
- **Pengajuan Saya**
  - Daftar & detail fasilitasi
- **Dokumen**
  - Upload dokumen & revisi
- **Tindak Lanjut**
  - Upload laporan tindak lanjut
- **Notifikasi**

### Fokus Tampilan
- Status proses
- Tombol upload

---

## 7. Auditor

### Menu & Halaman
- **Dashboard**
  - Ringkasan data
- **Data Fasilitasi**
  - Akses baca semua fasilitasi
- **Dokumen**
  - Preview & download
- **Audit Log**

### Fokus Tampilan
- Read-only
- Filter & histori

---

## Catatan Prinsip Umum UI/UX

- Satu proses utama: **Fasilitasi / Evaluasi**
- Satu halaman inti: **Detail Fasilitasi**
- Aksi & tab muncul sesuai **role + status**
- Tidak ada menu teknis berlebihan untuk user non-teknis

---
