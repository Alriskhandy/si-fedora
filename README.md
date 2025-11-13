# 🏛️ SI-FEDORA

**Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota**  
_Bappeda Provinsi Maluku Utara_

---

## 📖 Deskripsi Singkat

**SI-FEDORA** merupakan aplikasi berbasis web yang dikembangkan untuk mendukung proses **fasilitasi dan evaluasi dokumen perencanaan Kabupaten/Kota** di Provinsi Maluku Utara secara **digital, cepat, dan terintegrasi**.

Sistem ini menggantikan proses manual yang sebelumnya dilakukan melalui surat-menyurat atau email dengan platform terpusat yang memudahkan:

-   Pengunggahan dokumen oleh Bappeda Kabupaten/Kota,
-   Verifikasi oleh tim Bappeda Provinsi,
-   Penyampaian hasil fasilitasi dan evaluasi secara digital,
-   Monitoring status proses secara real-time.

---

## 🎯 Tujuan dan Manfaat

**Tujuan utama:**

-   Meningkatkan efektivitas dan efisiensi proses fasilitasi/evaluasi dokumen perencanaan daerah.
-   Mewujudkan koordinasi digital antara Bappeda Provinsi dan Bappeda Kabupaten/Kota.

**Manfaat:**

-   Proses lebih cepat, transparan, dan terdokumentasi.
-   Mendukung pelaksanaan SPBE dan transformasi digital pemerintahan.
-   Menjamin keselarasan perencanaan antara provinsi dan kabupaten/kota.
-   Memudahkan pimpinan dalam melakukan monitoring dan pengambilan keputusan berbasis data.

---

## 🧭 Alur Proses SI-FEDORA

Berdasarkan _Alur Pelaksanaan Fasilitasi/Evaluasi Dokren Kab/Kota_, tahapan utama sistem meliputi:

1. **Penyampaian Jadwal Fasilitasi/Evaluasi** oleh Bidang PERAN.
2. **Permohonan dari Kabupaten/Kota** beserta dokumen persyaratan.
3. **Verifikasi Dokumen** oleh Tim Bidang terkait.
4. **Penetapan Jadwal Pelaksanaan** dan **Undangan Fasilitasi/Evaluasi**.
5. **Pelaksanaan Fasilitasi/Evaluasi Dokumen.**
6. **Penyusunan dan Verifikasi Draft Hasil Evaluasi.**
7. **Persetujuan Kepala Bappeda dan Penerbitan Surat Rekomendasi.**
8. **Penyampaian Hasil ke Kabupaten/Kota.**
9. **Tindak Lanjut dan Penetapan Perda/Perkada Dokumen Perencanaan.**

---

## 💡 Fitur Utama

-   **Manajemen Dokumen:** upload, validasi, dan kelengkapan dokumen per kategori (RPJPD, RPJMD, RKPD).
-   **Workflow Otomatis:** alur verifikasi, revisi, dan persetujuan digital sesuai tahapan.
-   **Notifikasi & Reminder:** pemberitahuan otomatis ke Kabupaten/Kota (email/WhatsApp).
-   **Dashboard Monitoring:** pantauan status fasilitasi per Kabupaten/Kota secara real-time.
-   **Manajemen User:** role-based access (Admin Provinsi, Tim Bidang, Kabupaten/Kota).
-   **Pelaporan & Arsip Digital:** penyimpanan hasil fasilitasi/evaluasi dan rekomendasi final.

---

## ⚙️ Teknologi yang Digunakan

| Komponen       | Teknologi                                  |
| -------------- | ------------------------------------------ |
| Framework      | [Laravel 11](https://laravel.com)          |
| Database       | PostgreSQL                                 |
| Frontend       | Blade + TailwindCSS                        |
| Authentication | Laravel Breeze / Sanctum                   |
| Notifikasi     | Laravel Notification (Email & WhatsApp)    |
| Storage        | Laravel Filesystem (Local/Private Storage) |

---

## 🚀 Instalasi dan Setup

Pastikan telah menginstal **Composer**, **PHP ≥ 8.2**, dan **PostgreSQL**.

### 1️⃣ Clone Repository

```bash
git clone https://github.com/bappeda-malut/si-fedora.git
cd si-fedora
```
