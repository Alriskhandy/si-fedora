**Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota**  
_Bappeda Provinsi Maluku Utara_

# Fitur Generate File hasil verifikasi
---

## Deskripsi Singkat

Pembuatan fitur generate file / download file dengan format .xlx / excel untuk lembar hasil verifikasi untuk user dengan role verifikator pada halaman '/permohonan/{permohonan}/tahapan/verifikasi' setelah semua dokumen kelengkapan berhasil di verifikasi oleh verifikator.

---

## Requirement
Font: Arial
Ukuran: 
- Judul : 11pt - UPPERCASE
- Isi : 8pt - Capital First Character Each Word

---

Nama File: Lembar Verifikasi Dokumen Persyaratan Fasilitasi {Jenis Dokumen} {Tahun} {Nama Kab / Kota}
- Judul (sel A1-H1): Dokumen Persyaratan Fasilitasi {Jenis Dokumen}
- Judul (sel A2-H2): {Nama Kab / Kota} Tahun {Tahun}
- Tabel :
8 kolom : NO, URAIAN, ADA, TIDAK ADA, TANGGAL SURAT, TANGGAL PENYAMPAIAN, TANGGAL VERIFIKASI, KET (baris 1 - uppercase, center)
baris 2: angka (1-8, center, warna gray)
- Isi Kolom:
NO: Angka
URAIAN: looping daftar dokumen kelengkapan (untuk form dimulai dari FORM 1 - dan seterusnya) sesuai jenis dokumen
ADA: icon centang (default ada karena di pastikan semua dokumen ada)
TIDAK ADA: kosong
Tanggal Surat: kosong
Tanggal Penyampaian: tanggal upload oleh user (pemohon) pada tahapan permohonan
Tanggal Verifikasi: tanggal verifikasi oleh user (verifikator) pada tahapan verifikasi
Ket: kosong

- ttd: 
sel H26, Sofifi, {tanggal generate}
Verifikator PIC - {Nama Kab/kota}

- Format tanggal contoh: 20 Mei 2026
---