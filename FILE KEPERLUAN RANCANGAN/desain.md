# DAFTAR HALAMAN SI-FEDORA

## Sistem Informasi Fasilitasi Evaluasi Dokumen Rencana

---

## 🔐 A. MODUL AUTENTIKASI & PROFIL

### A1. Halaman Login

-   Form login (email & password)
-   Forgot password
-   Remember me

### A2. Halaman Dashboard

-   Dashboard sesuai role (berbeda untuk setiap role)
-   Statistik ringkasan
-   Grafik & chart monitoring
-   Notifikasi terbaru

### A3. Halaman Profil

-   Lihat & edit profil user
-   Ubah password
-   Pengaturan notifikasi

---

## 👥 B. MODUL MANAJEMEN USER & AKSES

### B1. Manajemen User

-   **List User** - Tabel daftar user dengan filter role
-   **Tambah User** - Form tambah user baru
-   **Edit User** - Form edit user
-   **Hapus User** - Soft delete
-   **Reset Password** - Reset password user

### B2. Manajemen Kab/Kota

-   **List Kabupaten/Kota** - Tabel daftar kabupaten/kota
-   **Tambah Kab/Kota** - Form tambah data baru
-   **Edit Kab/Kota** - Form edit data
-   **Detail Kab/Kota** - Info lengkap + history fasilitasi

---

## 📋 C. MODUL MASTER DATA

### C1. Manajemen Jenis Dokumen

-   **List Jenis Dokumen** (RKPD, RPJMD, RPD)
-   **Tambah/Edit Jenis Dokumen**
-   **Atur Persyaratan Dokumen** per jenis

### C2. Manajemen Persyaratan Dokumen

-   **List Persyaratan** per jenis dokumen
-   **Tambah/Edit Persyaratan**
-   **Upload Template Persyaratan**
-   **Urutan & Kode Persyaratan**

### C3. Manajemen Tahun Anggaran

-   **List Tahun Anggaran**
-   **Tambah/Edit Tahun Anggaran**
-   **Set Tahun Aktif**

### C4. Manajemen Tim Pokja

-   **List Tim Pokja**
-   **Tambah/Edit Tim Pokja**
-   **Manajemen Anggota Pokja** (assign user ke pokja)

---

## 📅 D. MODUL PENJADWALAN (ADMIN PERAN)

### D1. Manajemen Jadwal Fasilitasi

-   **List Jadwal Fasilitasi** - Tabel jadwal per tahun
-   **Buat Jadwal Baru** - Form jadwal (tahun, jenis dokumen, periode)
-   **Edit Jadwal**
-   **Publikasi Jadwal** (status: draft → published)

### D2. Surat Pemberitahuan

-   **Generate Surat Pemberitahuan** ke semua kab/kota
-   **List Surat Pemberitahuan** - Status pengiriman
-   **Cetak/Download Surat**
-   **Tracking Status Penerimaan**

---

## 📝 E. MODUL PERMOHONAN (KAB/KOTA)

### E1. Pengajuan Permohonan

-   **Formulir Permohonan** - Input data permohonan
-   **Upload Dokumen Persyaratan** - Multiple file upload dengan checklist
-   **Preview Permohonan** - Review sebelum submit
-   **Submit Permohonan** - Kirim ke sistem

### E2. Monitoring Permohonan

-   **List Permohonan Saya** - Status tracking
-   **Detail Permohonan** - Info lengkap + timeline
-   **Edit Permohonan** (jika masih draft)
-   **Download Hasil Rekomendasi**

### E3. Upload Dokumen Persyaratan

-   **Checklist Persyaratan** - Tanda centang ADA/TIDAK ADA
-   **Upload File** per persyaratan
-   **Ganti File** - Re-upload jika ditolak
-   **Preview Dokumen**

---

## ✅ F. MODUL VERIFIKASI (TIM VERIFIKASI)

### F1. Verifikasi Permohonan

-   **List Permohonan Masuk** - Filter status
-   **Detail Permohonan** - Lihat semua dokumen
-   **Form Verifikasi Dokumen** - Verifikasi per dokumen
-   **Catatan Verifikasi** - Input kekurangan/catatan
-   **Submit Hasil Verifikasi** - Lengkap/Tidak Lengkap

### F2. Laporan Verifikasi

-   **Generate Laporan Hasil Verifikasi**
-   **Submit ke Admin PERAN**

---

## 📊 G. MODUL EVALUASI (TIM POKJA)

### G1. Penugasan Evaluasi

-   **List Tugas Evaluasi Saya**
-   **Detail Tugas** - Lihat dokumen kab/kota
-   **Download Dokumen** untuk review

### G2. Input Hasil Evaluasi

-   **Form Draft Rekomendasi** - Rich text editor
-   **Upload File Draft Rekomendasi**
-   **Catatan Evaluasi** - Input masukan per bagian dokumen
-   **Submit Draft ke Admin PERAN**

### G3. Revisi Evaluasi

-   **Lihat Feedback Kaban**
-   **Revisi Draft Rekomendasi**
-   **Re-submit Draft**

---

## 🏛️ H. MODUL ADMIN PERAN

### H1. Pengelolaan Permohonan

-   **Dashboard Permohonan** - Semua permohonan
-   **Filter** - By status, kab/kota, jenis dokumen
-   **Assign ke Tim Verifikasi**
-   **Assign ke Tim Pokja** untuk evaluasi

### H2. Review Draft Rekomendasi

-   **List Draft dari Pokja** - Status pending approval
-   **Preview Draft Rekomendasi**
-   **Validasi Draft** - Check kelengkapan
-   **Forward ke Kaban** untuk approval

### H3. Pengelolaan Surat Rekomendasi

-   **Generate Surat Rekomendasi** (setelah disetujui Kaban)
-   **Input Nomor & Tanggal Surat**
-   **Upload Surat TTD** (jika TTD manual)
-   **Upload Lampiran** - Berbeda per kab/kota
-   **Kirim Surat ke Kab/Kota** - Notifikasi email/WA

### H4. Monitoring Progress

-   **Dashboard Monitoring** - Real-time status
-   **Timeline View** - Gantt chart/timeline
-   **Laporan Progress** per kab/kota

---

## 👔 I. MODUL KEPALA BADAN (KABAN)

### I1. Dashboard Executive

-   **Summary Statistics** - Total permohonan, status
-   **Chart & Graph** - Visual monitoring
-   **Recent Activities**

### I2. Approval Draft Rekomendasi

-   **List Draft Pending Approval**
-   **Preview Draft Rekomendasi**
-   **Baca Hasil Evaluasi Pokja**
-   **Approve/Reject Draft** dengan catatan
-   **E-Sign / Upload TTD** (jika approval)

### I3. Persetujuan Surat

-   **List Surat Perlu TTD**
-   **Preview Surat Rekomendasi**
-   **TTD Digital** atau Upload file TTD
-   **Approve for Sending**

### I4. Laporan & Monitoring

-   **Laporan Executive Summary**
-   **Export Report** - PDF/Excel
-   **Monitoring Real-time**

---

## 🔄 J. MODUL PELAKSANAAN FASILITASI

### J1. Penjadwalan Pelaksanaan

-   **Buat Jadwal Pelaksanaan** - Tanggal, tempat, agenda
-   **Generate Undangan** - Auto populate data
-   **Kirim Undangan** - Email/WA ke peserta

### J2. Dokumentasi Pelaksanaan

-   **Upload Absensi**
-   **Upload Notulensi**
-   **Upload Berita Acara**
-   **Upload Dokumentasi Foto**
-   **Upload Materi Presentasi**

### J3. Laporan Pelaksanaan

-   **Form Laporan Pelaksanaan**
-   **Download Laporan** - Format PDF

---

## 📤 K. MODUL TINDAK LANJUT & PENETAPAN

### K1. Tindak Lanjut (Kab/Kota)

-   **Form Tindak Lanjut** - Uraian tindak lanjut
-   **Upload Laporan Tindak Lanjut**
-   **Submit ke Provinsi**

### K2. Penetapan Dokumen (Kab/Kota)

-   **Form Penetapan** - Perda/Perkada
-   **Input Nomor Penetapan & Tanggal**
-   **Upload File Penetapan**
-   **Input Nomor Registrasi**
-   **Submit ke Provinsi**

### K3. Monitoring Tindak Lanjut (Admin)

-   **List Tindak Lanjut** - Status per kab/kota
-   **Verifikasi Tindak Lanjut**
-   **Monitoring Penetapan**

---

## 📢 L. MODUL NOTIFIKASI & KOMUNIKASI

### L1. Notifikasi Sistem

-   **List Notifikasi** - Inbox notifikasi
-   **Mark as Read**
-   **Filter** - By type, date

### L2. Integrasi WhatsApp

-   **Setting WhatsApp Gateway**
-   **Template Pesan WA**
-   **Send Notification via WA** - Auto trigger
-   **Log Pengiriman WA**

### L3. Email Notification

-   **Template Email**
-   **Send Email** - Auto trigger
-   **Log Email**

---

## 📊 M. MODUL LAPORAN & MONITORING

### M1. Dashboard Monitoring

-   **Real-time Dashboard** - Status semua permohonan
-   **Grafik Timeline** - Gantt chart progress
-   **Statistik per Kab/Kota**
-   **Alert & Warning** - Deadline approaching

### M2. Laporan Periodik

-   **Laporan Bulanan** - Auto generate
-   **Laporan Triwulan**
-   **Laporan Tahunan**
-   **Export** - PDF, Excel, Word

### M3. Analisis & Statistik

-   **Analisis Durasi Proses**
-   **Tingkat Kelengkapan Dokumen**
-   **Performance per Kab/Kota**
-   **Komparasi Tahun**

### M4. Download & Export

-   **Export Data** - Per periode, per kab/kota
-   **Bulk Download Dokumen**
-   **Generate Report** - Custom filter

---

## ⚙️ N. MODUL PENGATURAN SISTEM

### N1. Pengaturan Umum

-   **Setting Aplikasi** - Nama, logo, footer
-   **Setting Email** - SMTP configuration
-   **Setting WhatsApp** - API integration
-   **Setting Notifikasi** - Template & trigger

### N2. Pengaturan Workflow

-   **Atur Durasi SLA** per tahapan
-   **Atur Auto-reminder**
-   **Atur Auto-escalation**

### N3. Template Dokumen

-   **Template Surat** - Upload template Word
-   **Template Laporan**
-   **Auto-merge Data** ke template

### N4. Backup & Maintenance

-   **Backup Database**
-   **Restore Database**
-   **Log System**
-   **Maintenance Mode**

---

## 📱 O. MODUL MOBILE-FRIENDLY

### O1. Mobile Dashboard

-   **Responsive Dashboard** - Mobile view
-   **Quick Actions** - Tombol cepat

### O2. Mobile Notifications

-   **Push Notification** - Mobile alert
-   **Quick Approval** - Approve via mobile

---

## 🔍 P. MODUL PENCARIAN & FILTER

### P1. Pencarian Global

-   **Search Bar** - Cari di semua module
-   **Advanced Search** - Multi-criteria

### P2. Filter Data

-   **Filter by Status**
-   **Filter by Tahun**
-   **Filter by Kab/Kota**
-   **Filter by Jenis Dokumen**
-   **Custom Date Range**

---

## 📈 Q. FITUR TAMBAHAN

### Q1. Timeline View

-   **Timeline Permohonan** - Visual progress
-   **History Changes** - Audit trail

### Q2. Calendar View

-   **Kalender Jadwal** - View jadwal pelaksanaan
-   **Reminder Deadline**

### Q3. File Management

-   **Repository Dokumen** - Centralized storage
-   **Version Control** - Track document versions
-   **Preview File** - PDF, Word, Excel viewer

### Q4. Activity Log

-   **Log Aktivitas User** - Who did what when
-   **Audit Trail** - Complete history
-   **Export Log**

---

## 🎯 PRIORITAS PENGEMBANGAN

### **FASE 1 - Jangka Pendek (1 Kab/Kota - Ternate)**

-   A: Autentikasi & Dashboard
-   B: Manajemen User
-   C: Master Data (minimal)
-   D: Penjadwalan & Surat Pemberitahuan
-   E: Permohonan (Kab/Kota)
-   F: Verifikasi
-   G: Evaluasi Pokja
-   H: Admin PERAN (basic)
-   I: Approval Kaban
-   L: Notifikasi WA (testing)

### **FASE 2 - Jangka Menengah (3-4 Kab/Kota)**

-   J: Pelaksanaan Fasilitasi
-   K: Tindak Lanjut & Penetapan
-   M: Laporan & Monitoring (enhanced)
-   N: Pengaturan Sistem
-   P: Pencarian & Filter
-   Q: Timeline & Calendar

### **FASE 3 - Jangka Panjang (6 Kab/Kota)**

-   Full features semua module
-   Optimasi performa
-   Advanced analytics
-   Mobile app (optional)
-   API untuk integrasi eksternal

---

## 📝 CATATAN TEKNIS

### Role-Based Access Control (RBAC):

-   **Superadmin**: Akses semua fitur
-   **Kaban**: Approval & monitoring executive
-   **Admin PERAN**: Koordinasi & workflow management
-   **Tim Verifikasi**: Verifikasi dokumen
-   **Tim Evaluasi/Pokja**: Evaluasi & rekomendasi
-   **Kabupaten/Kota**: Submit permohonan & upload dokumen

### Workflow Status Tracking:

1. Draft → Submitted → Verified → Assigned
2. In Evaluation → Draft Recommendation → Approved by Kaban
3. Letter Issued → Sent → Follow-up → Completed

### Notifikasi Otomatis:

-   Email & WhatsApp notification di setiap perubahan status
-   Reminder H-3, H-1 sebelum deadline
-   Escalation jika lewat deadline

### Security Features:

-   Role-based permissions
-   File upload validation
-   Audit trail lengkap
-   Secure file storage
-   SSL/TLS encryption
