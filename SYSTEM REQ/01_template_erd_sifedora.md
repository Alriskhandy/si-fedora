# TEMPLATE 1 — ERD SIFEDORA

## Tujuan
Dokumen ini menjadi acuan struktur database inti SIFEDORA dan digunakan untuk pembuatan ERD (Whimsical / dbdiagram).

## Entitas Inti
### users
- id
- name
- email
- password
- role_id
- kabupaten_kota_id (nullable)
- timestamps

### roles
- id
- name

### kabupaten_kota
- id
- nama

### permohonan
- id
- kabupaten_kota_id
- jenis_dokumen_id
- judul
- status
- created_by
- timestamps

### dokumen_permohonan
- id
- permohonan_id
- jenis
- file_path

### verifikasi
- id
- permohonan_id
- verifikator_id
- status
- catatan

### jadwal_fasilitasi
- id
- permohonan_id
- tanggal
- lokasi

### fasilitator_assignment
- id
- jadwal_id
- fasilitator_id

### hasil_fasilitasi
- id
- permohonan_id
- fasilitator_id
- ringkasan
- file_path

### surat_hasil
- id
- permohonan_id
- nomor_surat
- file_path

### perda_perkada
- id
- permohonan_id
- jenis
- file_path

## Catatan
- permohonan adalah core entity
- gunakan status sebagai pengendali alur
