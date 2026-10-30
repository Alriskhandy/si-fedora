# Analisis Pengembangan — Masukan Hasil Fasilitasi Dinamis

> 📁 **ARSIP — snapshot 27 Oktober 2026.** Keputusan D1–D7 (§9) **dikonfirmasi 27 Okt 2026 sesuai usulan**. Rencana eksekusi ada di [Fase 6 — Format Hasil Dinamis](../implementasi/FASE-6-FORMAT-HASIL-DINAMIS.md).

> Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota — Bappeda Provinsi Maluku Utara
> Snapshot per **27 Oktober 2026**, branch `upgrade/laravel-12`.
> Konteks: [`PRD.md`](../produk/PRD.md) baris 8 — *"Hasil Fasilitasi diubah ke format baru — menunggu format baru"*. Analisis ini menyiapkan fondasi agar format baru (dan perubahan berikutnya) cukup diatur lewat data, tanpa mengubah kode.
> Metodologi mengikuti [`ANALYST_SYSTEM.md`](../../role-skill/ANALYST_SYSTEM.md): membedakan *Fakta / Temuan / Risiko / Rekomendasi*. Hal tanpa bukti ditandai **[UNKNOWN]**.

---

## 1. Ringkasan Eksekutif

Saat ini **format masukan dan format dokumen hasil fasilitasi tertanam di kode**:

- Empat bagian masukan (Sistematika, Konsistensi & Keselarasan, Urusan Pemerintahan, Rekomendasi) masing-masing punya **tabel, model, 3 endpoint, tab, dan blok JS sendiri**.
- Dokumen hasil dibangun oleh **dua renderer terpisah**: Word lewat `HasilFasilitasiDocumentService`, PDF lewat `pdf.blade.php`. Keduanya memuat **teks narasi, daftar dasar hukum, penomoran bab, dan penandatangan secara hard-code**.

Akibatnya, perubahan format sekecil apa pun harus dikerjakan developer. Contohnya dasar hukum tahunan, judul bab, atau satu kolom tambahan. Untuk perubahan struktur (bagian baru), developer harus menyentuh migration, model, controller, route, view, JS, dan dua renderer.

**Rekomendasi:** jadikan format sebagai **data berversi per jenis dokumen**, dengan tiga lapis:

1. **Template dokumen.** Narasi, dasar hukum, dan penandatangan disimpan di database, dengan placeholder seperti `{kabkota}` dan `{tahun}`.
2. **Definisi bagian masukan.** Bagian, rujukan (bab/urusan/tanpa rujukan), kolom tambahan, dan siapa yang boleh mengisi.
3. **Satu tabel masukan generik**, ditambah **satu renderer** yang membangun Word dan PDF dari struktur yang sama.

Dikerjakan bertahap dalam 3 tahap. **Tahap 1** (template teks) sudah menutup kebutuhan perubahan tahunan dengan risiko rendah. **Tahap 2–3** baru dikerjakan setelah format baru dari stakeholder tersedia, agar desain diuji dengan format nyata.

---

## 2. Kondisi Saat Ini

### 2.1 Fakta — apa yang sudah dinamis

| Aspek | Sumber data | Catatan |
| --- | --- | --- |
| Daftar bab sistematika | `master_bab` (per `jenis_dokumen_id`, `parent_id`, `urutan`) | Sudah per jenis dokumen |
| Daftar urusan | `master_urusan` + flag `tampil_hasil_fasilitasi` | Bisa diatur Admin |
| Kelengkapan persyaratan (bab IV dokumen) | `master_kelengkapan_verifikasi` | Ikut verifikasi |
| Akses input | Permission `hasil-fasilitasi.input` dan `hasil-fasilitasi.input-urusan` | Terikat ke 2 kelompok bagian, bukan per bagian |

### 2.2 Fakta — apa yang masih tertanam di kode

| Lapisan | Lokasi | Yang di-hard-code |
| --- | --- | --- |
| Skema data | 4 tabel: `hasil_fasilitasi_sistematika`, `_form`, `_urusan`, `_rekomendasi` | Jumlah bagian dan kolomnya (bab+sub bab, urusan, atau catatan saja) |
| Model | 4 model `HasilFasilitasi*` + 4 relasi di `HasilFasilitasi` | Satu model per bagian |
| Endpoint | `routes/web/fasilitasi.php`: 12 route (store/update/delete × 4), dibagi 2 grup permission | Satu set route per bagian |
| Controller | `HasilFasilitasiController`: 12 method CRUD dengan validasi berbeda per bagian | Aturan field per bagian |
| Tampilan | `create`, `show`, partial `daftar-*`, `input-script` (konfigurasi `judulUbah`, `kolomCatatan`) | Tab, form, dan kolom tabel per bagian |
| Ringkasan | `ApprovalController`, `PermohonanController`, `tahapan/hasil.blade.php`, `approval/partials/masukan-urusan` | Hitungan dan tampilan per bagian |
| Dokumen Word | `HasilFasilitasiDocumentService` (±590 baris) | Bab I–VII, teks Pendahuluan, 9 butir Dasar Hukum, Maksud & Tujuan, Penutup, lebar kolom tabel, "Tabel 1.1", penandatangan "Kepala Bappeda Provinsi Maluku Utara" |
| Dokumen PDF | `pdf.blade.php` (±350 baris) | **Salinan kedua** dari struktur dan teks yang sama |

### 2.3 Temuan

- **T1. Dua renderer, satu isi.** Setiap perubahan teks harus dilakukan dua kali (PHP dan Blade). Kalau salah satu terlewat, Word dan PDF berbeda. **Risiko: tinggi**, karena keduanya dokumen resmi.
- **T2. Dasar hukum akan usang tiap tahun.** Butir (g) merujuk *Permendagri 10/2025 tentang Pedoman Penyusunan RKPD Tahun 2026*. Untuk permohonan tahun berikutnya, butir ini harus diganti developer.
- **T3. Satu narasi untuk semua jenis dokumen.** Pendahuluan merujuk "Rancangan Perkada RKPD" dan Permendagri 86/2017 pasal 102 untuk **semua** jenis (RKPD, RPJMD, RPJPD dan perubahannya). Padahal RPJMD/RPJPD berbentuk Ranperda, bukan Ranperkada. **[UNKNOWN]** apakah dokumen RPJMD/RPJPD selama ini dikoreksi manual setelah diunduh.
- **T4. Bagian masukan tidak bisa berbeda per jenis dokumen.** Semua jenis dokumen memakai 4 bagian yang sama. Format baru di PRD kemungkinan besar mengubah bagian atau kolom.
- **T5. Akses per bagian bersifat biner.** OPD = "hanya urusan" (keputusan teknis 24 Okt 2026). Kalau nanti ada bagian yang juga boleh diisi OPD, harus dibuat permission baru plus perubahan kode.
- **T6. Dokumen lama tidak terikat format.** Draft dibuat ulang dari kode yang berlaku saat tombol "Buat Ulang Draft" ditekan. Setelah format diubah, membuat ulang draft permohonan lama akan memakai format baru. Draft final yang sudah diupload (PDF) aman karena berupa file.

---

## 3. Kebutuhan Perubahan yang Diantisipasi

Ini skenario yang harus bisa dilakukan **Admin/Superadmin tanpa developer**:

| # | Skenario | Frekuensi | Ditangani di tahap |
| --- | --- | --- | --- |
| K1 | Mengganti/menambah butir dasar hukum | Tahunan | 1 |
| K2 | Mengubah teks pendahuluan, maksud & tujuan, penutup, penandatangan | Kadang | 1 |
| K3 | Narasi berbeda per jenis dokumen (RKPD vs RPJMD vs RPJPD) | Sekali, lalu jarang | 1 |
| K4 | Mengubah judul atau urutan bagian di dokumen | Kadang | 2 |
| K5 | Menambah/menghapus bagian masukan (mis. "Indikator Kinerja") | Saat format baru | 2 |
| K6 | Bagian berbeda per jenis dokumen | Saat format baru | 2 |
| K7 | Kolom tambahan pada bagian (mis. "Halaman", "Keterangan") | Saat format baru | 3 |
| K8 | Mengatur role yang boleh mengisi tiap bagian (termasuk OPD) | Jarang | 3 |
| K9 | Format lama tetap dipakai untuk permohonan yang sudah berjalan | Setiap perubahan | 1–2 (versi) |

**Di luar cakupan:** pengaturan tata letak bebas, seperti font, margin, atau ukuran kertas per dokumen. Ini tetap di kode sebagai satu gaya baku F4/Arial 12. Membuatnya bebas diatur menambah kompleksitas besar dengan manfaat kecil.

---

## 4. Opsi Arsitektur

### Opsi A — Template teks di database (bagian tetap)

Narasi dan dasar hukum dipindah ke tabel template per jenis dokumen. Keempat bagian masukan tetap di kode.

- ✅ Effort kecil, risiko rendah, langsung menyelesaikan K1–K3.
- ❌ Tidak menyelesaikan K4–K8. Format baru dengan bagian berbeda tetap butuh developer.

### Opsi B — Format terstruktur di database *(direkomendasikan, bertahap)*

Format dokumen = **template berversi** yang terdiri dari **blok berurutan**. Sebagian blok berisi teks dengan placeholder, sebagian lagi mengacu pada **bagian masukan**. Semua masukan disimpan di **satu tabel generik**. Satu renderer membangun Word dan PDF dari blok yang sama.

- ✅ Menyelesaikan K1–K9; menghapus duplikasi renderer (T1); UI input dibangun dari definisi bagian.
- ❌ Effort terbesar; butuh migrasi data dari 4 tabel; desain perlu divalidasi dengan format baru yang nyata.

### Opsi C — Template Word (.docx) yang diupload Admin

Admin mengupload `.docx` berisi placeholder (`${kabkota}`), blok berulang, dan baris tabel berulang. Sistem mengisinya dengan `PhpWord\TemplateProcessor`.

- ✅ Admin bebas mengatur tata letak di Word.
- ❌ Tidak menyelesaikan K5–K8, karena bagian masukan tetap di kode.
- ❌ Rich text dari TinyMCE (list, bold) sulit disisipkan ke placeholder `TemplateProcessor`.
- ❌ Template rusak (placeholder terpecah oleh Word) sulit didiagnosis Admin.
- ❌ PDF harus dikonversi dari docx, yang butuh LibreOffice di server.

### Perbandingan

| Kriteria | A | B | C |
| --- | --- | --- | --- |
| K1–K3 (teks) | ✅ | ✅ | ✅ |
| K4–K6 (bagian) | ❌ | ✅ | ⚠️ sebagian (urutan saja) |
| K7–K8 (kolom & akses) | ❌ | ✅ | ❌ |
| K9 (versi) | ✅ | ✅ | ✅ |
| Hapus duplikasi Word/PDF | ✅ bila sekalian | ✅ | ⚠️ butuh LibreOffice |
| Effort | Kecil | Besar (bertahap) | Sedang |
| Risiko teknis | Rendah | Sedang | Sedang–tinggi |

**Rekomendasi: Opsi B, dimulai dari Opsi A sebagai Tahap 1.** Tahap 1 adalah bagian dari B, sehingga tidak ada pekerjaan yang terbuang.

---

## 5. Rancangan Opsi B

### 5.1 Model data

```
format_dokumen                      ← satu versi format untuk satu jenis dokumen
  id, jenis_dokumen_id, nama, versi,
  berlaku_mulai_tahun, status (draft|aktif|arsip), dibuat_oleh, timestamps

format_blok                         ← isi dokumen, berurutan
  id, format_dokumen_id, urutan,
  tipe (judul | teks | daftar | kelengkapan | bagian_masukan | tanda_tangan),
  judul,                            ← mis. "II. DASAR HUKUM" (penomoran boleh otomatis)
  isi (text, placeholder),          ← untuk tipe teks/judul/tanda_tangan
  butir (json),                     ← untuk tipe daftar (mis. dasar hukum)
  bagian_masukan_id (nullable)      ← untuk tipe bagian_masukan

bagian_masukan                      ← definisi satu bagian yang diisi tim
  id, format_dokumen_id, kode (slug), nama, deskripsi, urutan,
  rujukan (tanpa | bab | urusan),   ← kolom pertama tabel / pengelompokan
  tampilan_dokumen (tabel | daftar_bernomor),
  kolom_tambahan (json)             ← [{kode, label, tipe: teks|angka|pilihan, wajib, pilihan[]}]
  role_pengisi (json)               ← mis. ["fasilitator"] atau ["fasilitator","opd"]
  tampil_untuk (json)               ← role yang boleh melihat di halaman detail (mis. Kaban)

hasil_fasilitasi                    ← tambah kolom
  format_dokumen_id                 ← dikunci saat hasil dibuat (K9)

hasil_fasilitasi_masukan            ← pengganti 4 tabel lama
  id, hasil_fasilitasi_id, bagian_masukan_id,
  rujukan_id (nullable)             ← id master_bab atau master_urusan, sesuai bagian.rujukan
  sub_rujukan (nullable string)     ← mis. sub bab "1.2 Dasar Hukum"
  catatan (rich text, disanitasi),
  nilai (json)                      ← isi kolom_tambahan
  user_id, timestamps
```

**Alasan desain:**

- **Rujukan dibatasi ke enum (`bab`/`urusan`), bukan polymorphic bebas.** Validasi dan aturan OPD ("hanya urusan bidangnya") tetap bisa ditegakkan di kode.
- **Kolom tambahan disimpan di JSON**, bukan dengan menambah kolom tabel. Format baru tidak perlu migration. Validasi dibangun dari definisi `kolom_tambahan`.
- **Format berversi.** Format yang sudah dipakai suatu hasil tidak diedit di tempat. Admin membuat versi baru (salin → ubah → aktifkan), dan hasil lama tetap memakai versinya sendiri.

### 5.2 Placeholder

Placeholder memakai daftar tetap yang **bukan** Blade atau ekspresi, agar Admin tidak bisa menyisipkan kode:

| Placeholder | Sumber |
| --- | --- |
| `{kabkota}`, `{jenis_wilayah}` | `kabupaten_kota` |
| `{jenis_dokumen}`, `{tahun}` | `permohonan` |
| `{produk_hukum}` | Ranperbup/Ranperwal/Ranperda (aturan per format) |
| `{jabatan_kepala_daerah}` | Bupati/Walikota |
| `{tanggal}` | tanggal generate, format Indonesia |

Penggantiannya memakai `strtr()` dengan daftar di atas. Placeholder yang tidak dikenal ditampilkan apa adanya, dan diberi peringatan di layar pratinjau format.

### 5.3 Satu renderer

```
FormatDokumenRenderer
  ├─ bangunStruktur(hasil): daftar blok terisi (teks sudah diganti placeholder,
  │                         bagian masukan sudah dikelompokkan & diurutkan)
  ├─ WordWriter  (PhpWord)  ← menulis struktur ke .docx
  └─ PdfWriter   (DomPDF)   ← satu Blade generik yang me-render struktur yang sama
```

Gaya baku (F4, Arial 12, margin, lebar kolom proporsional) tetap di kode. Admin hanya mengatur isi dan urutan.

### 5.4 Halaman yang menyesuaikan diri

| Halaman | Perilaku baru |
| --- | --- |
| Input (`create`) | Tab dibangun dari `bagian_masukan` milik format hasil; form menampilkan pilihan bab/urusan sesuai `rujukan` + `kolom_tambahan`; tab tampil bila role pengguna ada di `role_pengisi` |
| Detail (`show`) | Tab dari `bagian_masukan` dan `tampil_untuk` (menggantikan `$showExtendedTabs`) |
| Kaban/approval | Ringkasan "jumlah per bagian" diambil dari data, bukan 4 relasi tetap |
| Endpoint | 3 route generik: `POST/PUT/DELETE hasil-fasilitasi/{permohonan}/masukan/{bagian:kode}/{id?}`, dengan otorisasi berdasarkan `role_pengisi` + aturan rujukan urusan OPD |
| Master baru | "Format Dokumen Hasil" (Superadmin/Admin): daftar versi, editor blok, editor bagian, **pratinjau PDF dengan data contoh**, tombol aktifkan |

Partial `daftar-*`, `input-filter`, dan `input-aksi` hasil perbaikan UX 26 Okt 2026 **bisa dipakai ulang**, karena sudah berbasis atribut `data-jenis`/`data-bagian`.

### 5.5 Pengamanan

- Isi blok teks hanya teks biasa dengan placeholder. Catatan masukan tetap melalui sanitizer yang sudah ada (`SanitizedHtml`).
- Format aktif tidak bisa diedit. Hanya versi `draft` yang bisa diubah, dan perubahan dicatat di activity log.
- Bagian yang sudah berisi masukan tidak bisa dihapus dari versi yang dipakai. Penghapusan dilakukan dengan membuat versi baru.
- Pengelolaan format memakai permission baru `master.format-dokumen.manage`, mengikuti katalog `config/permissions.php`.

---

## 6. Tahapan Pengerjaan

| Tahap | Isi | Menyelesaikan | Prasyarat | Estimasi kasar* |
| --- | --- | --- | --- | --- |
| **1. Template teks** | Tabel `format_dokumen` + `format_blok` (tipe teks/daftar/tanda tangan/kelengkapan); format awal di-seed **persis** dari teks saat ini; satu renderer Word+PDF; `hasil_fasilitasi.format_dokumen_id`; halaman master sederhana + pratinjau | K1–K3, K9, T1–T3, T6 | Tidak ada | 5–8 hari kerja |
| **2. Bagian masukan dinamis** | `bagian_masukan` + `hasil_fasilitasi_masukan`; migrasi data dari 4 tabel; endpoint & UI generik; ringkasan Kaban/approval dari data | K4–K6, T4 | **Format baru dari stakeholder tersedia** | 10–15 hari kerja |
| **3. Kolom tambahan & akses per bagian** | `kolom_tambahan` + validasi dinamis; `role_pengisi`/`tampil_untuk` menggantikan permission per kelompok | K7–K8, T5 | Tahap 2 | 5–8 hari kerja |

\*Estimasi untuk satu developer yang sudah mengenal kode, termasuk test. **[UNKNOWN]** sampai format baru diterima. Angka ini perlu dihitung ulang setelah melihat format baru.

### 6.1 Migrasi data (Tahap 2)

1. Buat versi format "Format 2025" yang mendefinisikan 4 bagian lama dengan kode `sistematika`, `konsistensi`, `urusan`, `rekomendasi`.
2. Salin baris dari 4 tabel lama ke `hasil_fasilitasi_masukan` dalam satu transaksi. Pemetaannya: `master_bab_id`/`master_urusan_id` → `rujukan_id`, `sub_bab` → `sub_rujukan`.
3. Isi `hasil_fasilitasi.format_dokumen_id` untuk semua hasil yang sudah ada.
4. Verifikasi dengan membandingkan jumlah baris per bagian dan membandingkan hasil generate dokumen sebelum dan sesudah migrasi untuk beberapa permohonan.
5. Tabel lama **dipertahankan satu rilis** sebagai cadangan (read-only), baru dihapus di rilis berikutnya.

---

## 7. Dampak terhadap Kode

| Area | Tahap 1 | Tahap 2 | Tahap 3 |
| --- | --- | --- | --- |
| `HasilFasilitasiDocumentService`, `pdf.blade.php` | Diganti renderer berbasis blok | Blok bagian masukan generik | Kolom tambahan di tabel |
| 4 model + 4 tabel `hasil_fasilitasi_*` | — | Diganti `HasilFasilitasiMasukan` (tabel lama diarsipkan) | — |
| `HasilFasilitasiController` (12 method CRUD) | — | Diganti 3 method generik | Validasi dari definisi |
| Route `hasil-fasilitasi.{bagian}.*` | — | Diganti route generik; snapshot `RouteAuthorizationTest` diperbarui | — |
| View input/detail + `input-script` | — | Tab & form dari data | Field tambahan |
| `ApprovalController`, `PermohonanController`, `tahapan/hasil`, approval partial | — | Hitungan dari data | — |
| `config/permissions.php` | `master.format-dokumen.manage` | — | `input`/`input-urusan` digantikan `role_pengisi` (perlu keputusan) |
| `SanitizeRichText` command | — | Menyasar tabel baru | — |
| Test | Renderer: dokumen dari format seed = dokumen lama | Port `HasilFasilitasiOpdTest`, `HasilFasilitasiInputPageTest`, `KabanMasukanHasilTest` | Validasi kolom & akses |

---

## 8. Risiko

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Desain bagian tidak cocok dengan format baru | Tahap 2 dikerjakan ulang | Tahap 2 baru dimulai **setelah** format baru diterima; Tahap 1 tidak bergantung padanya |
| Hasil dokumen berubah setelah renderer diganti | Dokumen resmi berbeda dari biasanya | Format awal di-seed persis dari teks lama; test membandingkan teks hasil generate |
| Migrasi data gagal sebagian | Masukan hilang | Satu transaksi, verifikasi jumlah baris, tabel lama disimpan satu rilis, backup sebelum migrasi (lihat RUNBOOK) |
| Admin salah mengubah format aktif | Draft berikutnya keliru | Versi aktif tidak bisa diedit; pratinjau wajib sebelum aktivasi; versi lama bisa diaktifkan kembali |
| Fleksibilitas berlebihan membuat master sulit dipakai | Admin bingung | Batasi tipe blok dan tipe kolom (§5.1); tanpa pengaturan tata letak |
| Aturan khusus OPD hilang saat akses jadi generik | OPD bisa mengisi urusan di luar bidangnya | Aturan "urusan bidang OPD" tetap di kode untuk bagian dengan `rujukan = urusan`; test lama dipertahankan |

---

## 9. Keputusan yang Perlu Dikonfirmasi

> ✅ Semua usulan di bawah dikonfirmasi pemilik proyek pada 27 Oktober 2026. Penerapannya ada di [Fase 6 — Keputusan yang Berlaku](../implementasi/FASE-6-FORMAT-HASIL-DINAMIS.md#keputusan-yang-berlaku-dikonfirmasi-27-okt-2026-sesuai-usulan-analisis-9).

| # | Pertanyaan | Usulan (dikonfirmasi) |
| --- | --- | --- |
| D1 | Siapa yang boleh mengelola format dokumen? | Superadmin, dan Admin dengan permission `master.format-dokumen.manage` |
| D2 | Apakah narasi dan bagian berbeda per jenis dokumen (RKPD / RPJMD / RPJPD dan perubahannya)? | Ya, satu format per jenis dokumen. Format boleh disalin dari jenis lain |
| D3 | Apakah format berlaku per tahun permohonan? | Ya, `berlaku_mulai_tahun`; permohonan memakai format aktif terbaru yang tahunnya ≤ tahun permohonan |
| D4 | Apakah teks pendahuluan saat ini juga dipakai untuk RPJMD/RPJPD (T3)? | Perlu dicek ke tim. Jika tidak, Tahap 1 sekaligus memperbaikinya |
| D5 | Kapan format baru (PRD baris 8) tersedia? | Penentu jadwal Tahap 2 |
| D6 | Apakah perlu upload template Word (Opsi C)? | Tidak untuk saat ini; bisa ditambah kemudian sebagai "kop/sampul" saja |
| D7 | Apakah permission `hasil-fasilitasi.input` dan `input-urusan` diganti `role_pengisi` per bagian (Tahap 3)? | Ya, tetapi tetap mempertahankan "OPD hanya urusan bidangnya" di kode |

---

## 10. Langkah Berikutnya

1. Stakeholder menjawab D1–D5.
2. Kerjakan **Tahap 1**. Tahap ini aman dikerjakan sekarang dan langsung mengurangi pekerjaan tahunan (dasar hukum).
3. Setelah format baru diterima, cocokkan format itu dengan model §5.1, sesuaikan rancangan, dan hitung ulang estimasi Tahap 2–3.
