**Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota**  
_Bappeda Provinsi Maluku Utara_

# Fitur Input Catatan/Masukan Rekomendasi & Form Penyelarasan
---

## Deskripsi Singkat

Penambahan fitur input Catatan/Masukan Rekomendasi & Form Penyelarasan untuk user (fasilitator) pada tahapan Hasil Fasilitasi pada halaman '/hasil-fasilitasi/{permohonan}/create'.

---

## Requirement
Card: Masukan Fasilitasi / Evaluasi
Menu:
- Sistematika & Substansi (Sudah ada)
- Konsistensi & Keselarasan (tambahkan)
- Urusan Pemerintahan (Sudah ada)
- Rekomendasi (tambahkan)

---

## Konsistensi & Keselarasan
tabel 'hasil_fasilitasi_form': 
- id
- hasil_fasilitasi_id
- catatan
- user_id
- timestamps

---

Tampilan:
- Form 'Tambah Item'
- Input text (tinyMCE)
- Tombol tambah

Tabel:
- No
- Catatan
- Oleh
- Aksi (Edit / Hapus)

## Rekomendasi
tabel 'hasil_fasilitasi_rekomendasi': 
- id
- hasil_fasilitasi_id
- catatan
- user_id
- timestamps

---

Tampilan:
- Form 'Tambah Item'
- Input text (tinyMCE)
- Tombol tambah

Tabel:
- No
- Catatan
- Oleh
- Aksi (Edit / Hapus)

---

# Perbaikan lainnya

Filter:
- user

Sort (default: urutan): 
- Urutan (Sistematika: bab, urusan: urutan urusan, konsistensi & keselarasan: urutan, rekomendasi: urutan)
- waktu