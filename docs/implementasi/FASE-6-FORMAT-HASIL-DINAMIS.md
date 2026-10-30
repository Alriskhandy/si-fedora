# Fase 6 — Format Hasil Fasilitasi Dinamis

> **Status: 🔄 Tahap 1–3 kode selesai (27 Okt 2026), belum dirilis.** Sisa: 2.8 (hapus tabel/model lama) pada rilis berikutnya setelah migrasi data di server terverifikasi. Lihat [Catatan Pelaksanaan](#catatan-pelaksanaan).

**Indikatif:** Tahap 1 kapan saja; Tahap 2–3 setelah format baru diterima · **Butir:** PRD #4, backlog 3.8 + 2.8 · **Analisis:** [`ANALISIS-MASUKAN-DINAMIS.md`](../analisis/ANALISIS-MASUKAN-DINAMIS.md)

## Tujuan

Format masukan dan dokumen hasil fasilitasi diatur lewat data berversi per jenis dokumen, sehingga perubahan format berikutnya tidak lagi membutuhkan perubahan kode. Contohnya dasar hukum tahunan, narasi, bagian masukan, kolom, dan pengisi.

## Keputusan yang Berlaku (dikonfirmasi 27 Okt 2026, sesuai usulan analisis §9)

| # | Keputusan | Diterapkan di |
|---|---|---|
| D1 | Format dokumen dikelola **Superadmin dan Admin** lewat permission baru `master.format-dokumen.manage` (role `superadmin`, `admin_peran`) | Tahap 1 — 1.6 |
| D2 | **Satu format per jenis dokumen** (RKPD, RKPD Perubahan, RPJMD, RPJMD Perubahan, RPJPD, RPJPD Perubahan). Format bisa disalin dari jenis lain | Tahap 1 — 1.1, 1.6 |
| D3 | Format berlaku per tahun lewat `berlaku_mulai_tahun`. Permohonan memakai format **aktif** terbaru dengan `berlaku_mulai_tahun ≤ tahun permohonan` | Tahap 1 — 1.1, 1.5 |
| D4 | Narasi dipisah per jenis dokumen. Tahap 1 men-seed teks saat ini **apa adanya** untuk keenam jenis, sehingga hasil dokumen tidak berubah. Teks RPJMD/RPJPD (Ranperda, bukan Ranperkada) diperbaiki tim lewat master format, bukan oleh developer | Tahap 1 — 1.2 |
| D5 | ~~Tahap 2 baru dimulai setelah format baru (PRD #4) diterima~~ **Diubah 27 Okt 2026 oleh pemilik proyek:** Tahap 2 dikerjakan tanpa menunggu format baru, memakai empat bagian yang ada sebagai data awal. Format baru nanti cukup disusun di master; bila butuh hal yang tidak terantisipasi, sesuaikan kemudian | Tahap 2 — gerbang masuk |
| D6 | **Tidak ada** upload template Word untuk saat ini. Bisa ditambah kemudian sebagai kop/sampul | — (di luar cakupan) |
| D7 | Permission `hasil-fasilitasi.input` dan `hasil-fasilitasi.input-urusan` diganti pengaturan **pengisi per bagian** (`role_pengisi`). Aturan "OPD hanya urusan bidangnya" **tetap di kode** | Tahap 3 — 3.2 |

## Prasyarat

- [ ] Fase 3 dirilis (masukan OPD dan kunci masukan sudah berjalan di server)
- [ ] Backup DB dan `storage/app` sebelum setiap rilis tahap ini (runbook §2)
- [ ] Tahap 2: format baru dari stakeholder sudah diterima dan fit-gap (2.0) disetujui

## Gambaran Arsitektur

```
format_dokumen (versi, per jenis dokumen, berlaku_mulai_tahun, status)
 ├─ format_blok (urutan, tipe, judul, isi + placeholder, butir)        ← Tahap 1
 └─ bagian_masukan (kode, rujukan, tampilan, kolom_tambahan, pengisi)   ← Tahap 2–3

hasil_fasilitasi.format_dokumen_id   ← dikunci saat hasil dibuat
hasil_fasilitasi_masukan             ← Tahap 2, pengganti 4 tabel lama

DokumenHasilRenderer → struktur blok terisi → WordWriter (PhpWord) / PdfWriter (DomPDF)
```

Gaya baku (kertas F4, Arial 12, margin, lebar kolom proporsional) tetap di kode. Admin hanya mengatur isi, urutan, dan bagian.

---

## Tahap 1 — Template Teks & Satu Renderer — ✅ kode selesai 27 Okt 2026

**Gerbang masuk:** tidak ada. **Menutup:** K1–K3, K9; temuan T1–T3, T6.

### 1.0 Rekam hasil dokumen saat ini (golden file)

Ini dikerjakan **sebelum** kode renderer diubah.

1. Buat fixture permohonan lengkap: Kabupaten dan Kota, satu jenis dokumen, beberapa masukan di keempat bagian, dan kelengkapan.
2. Generate Word dengan service lama. Ekstrak teksnya (PhpWord `IOFactory::load` → teks per paragraf/sel), lalu simpan ke `tests/Fixtures/dokumen-hasil/*.txt`.
3. Lakukan hal yang sama untuk HTML hasil `pdf.blade.php`, dibandingkan setelah tag dibuang.

File ini menjadi patokan bahwa format hasil seed menghasilkan dokumen yang sama.

### 1.1 Skema & model

Migration:

- `format_dokumen`: `id`, `jenis_dokumen_id` (FK `master_jenis_dokumen`), `nama`, `versi` (int, unik per jenis), `berlaku_mulai_tahun` (smallint), `status` (`draft|aktif|arsip`), `dibuat_oleh` (FK users, nullable), `diaktifkan_pada` (timestamp, nullable), timestamps.
- `format_blok`: `id`, `format_dokumen_id` (FK cascade), `urutan`, `tipe`, `judul` (nullable), `isi` (text, nullable), `butir` (json, nullable), `penomoran` (`tanpa|angka|huruf`, untuk tipe daftar), `sumber` (string, nullable, untuk tipe `bagian_masukan` di Tahap 1), timestamps.
- `hasil_fasilitasi.format_dokumen_id`: FK nullable, `nullOnDelete`.

Model:

- `FormatDokumen`: relasi `jenisDokumen`, `blok` (urut `urutan`), `hasilFasilitasi`. Konstanta `STATUS`. Scope `aktif`. Method statis `untuk(int $jenisDokumenId, int $tahun): ?self` (D3). `isTerkunci()`: status ≠ draft.
- `FormatBlok`: konstanta `TIPE` = `judul_dokumen`, `bab`, `teks`, `daftar`, `kelengkapan`, `bagian_masukan`, `tanda_tangan`. Cast `butir` ke array.
- Factory untuk keduanya.

Aturan: penomoran bab (I, II, …) **otomatis** dari urutan blok `bab`, sehingga tidak diketik Admin.

### 1.2 Seeder format awal (D2, D4)

`FormatDokumenSeeder` membuat satu format **"Format Awal" versi 1, status aktif, `berlaku_mulai_tahun` = 2020** untuk setiap jenis dokumen. Isinya disalin persis dari `HasilFasilitasiDocumentService`:

| Urutan | Tipe | Isi |
|---|---|---|
| 1 | `judul_dokumen` | "HASIL FASILITASI" / "RANCANGAN AKHIR {jenis_dokumen_kapital} {jenis_wilayah_kapital} {kabkota_kapital}" / "TAHUN {tahun}" |
| 2 | `bab` + `daftar` (huruf) | PENDAHULUAN, butir a–b |
| 3 | `bab` + `daftar` (huruf) | DASAR HUKUM, butir a–i |
| 4 | `bab` + `teks` | MAKSUD DAN TUJUAN |
| 5 | `bab` + `teks` + `kelengkapan` | KELENGKAPAN PERSYARATAN FASILITASI |
| 6 | `bab` + `teks` + `teks`/`bagian_masukan` | HASIL FASILITASI: pengantar, lalu sub-bagian 1 Sistematika (`sumber=sistematika`), 2 Konsistensi (`konsistensi`), 3 Urusan (`urusan`) |
| 7 | `bab` + `bagian_masukan` | REKOMENDASI (`rekomendasi`) |
| 8 | `bab` + `teks` | PENUTUP |
| 9 | `tanda_tangan` | Jabatan dan instansi penandatangan |

Seeder bersifat idempoten: jenis dokumen yang sudah punya format dilewati. Seeder didaftarkan di `DatabaseSeeder`.

### 1.3 Placeholder

Kelas `App\Support\PlaceholderDokumen` (tanpa framework, unit-testable):

- `nilai(Permohonan): array` menghasilkan placeholder berikut. Setiap placeholder juga punya varian `_kapital`.

  | Placeholder | Nilai |
  |---|---|
  | `{kabkota}`, `{jenis_wilayah}` | Nama dan jenis kabupaten/kota |
  | `{jenis_dokumen}`, `{tahun}` | Dari permohonan |
  | `{produk_hukum}` | Ranperbup/Ranperwal |
  | `{jabatan_kepala_daerah}` | Bupati/Walikota |
  | `{tanggal}` | Tanggal generate, format Indonesia |

- `isi(string $teks, array $nilai): string` memakai `strtr`.
- `tidakDikenal(string $teks): array` mendaftar placeholder yang tidak dikenal, untuk peringatan di pratinjau.
- **Tidak** memakai Blade, `eval`, atau ekspresi.

### 1.4 Satu renderer

1. `App\Services\DokumenHasil\DokumenHasilRenderer::struktur(HasilFasilitasi): array`. Isinya daftar blok terisi: placeholder sudah diganti, bab sudah bernomor, data bagian masukan sudah dikelompokkan dan diurutkan. Logika `groupSistematika`/`groupUrusan` dipindah ke sini.
2. `WordWriter::tulis(array $struktur): string` (path file) memakai kode tabel dan gaya dari `HasilFasilitasiDocumentService`.
3. `PdfWriter::tulis(array $struktur): string` memakai satu Blade generik `pages/fasilitasi/hasil-fasilitasi/pdf-format.blade.php` yang me-render struktur.
4. `HasilFasilitasiController::generate/downloadPdf/previewPdf` memakai renderer baru.
5. Hapus teks hard-code dari `HasilFasilitasiDocumentService` (service menjadi pembungkus tipis atau dihapus) dan hapus `pdf.blade.php` lama.

**Test:** dokumen dari format seed sama dengan golden file 1.0 (Word dan PDF); bab bernomor otomatis; bagian kosong menampilkan "Tidak ada catatan …" seperti sekarang.

### 1.5 Penguncian format pada hasil (D3, K9)

- Saat `HasilFasilitasi` dibuat (`creating`), isi `format_dokumen_id = FormatDokumen::untuk(jenis, tahun)?->id`.
- Command sekali jalan `hasil-fasilitasi:isi-format` mengisi kolom untuk hasil yang sudah ada. Command ini idempoten, punya opsi `--dry-run`, dan dicatat di runbook.
- `generate` memakai format milik hasil. Bila hasil belum punya format dan tidak ada format aktif, tampilkan pesan "Format dokumen untuk {jenis} tahun {tahun} belum tersedia. Hubungi Admin." Pesan ini bukan error 500.

**Test:** hasil baru terkunci ke format aktif yang sesuai tahun; mengaktifkan versi baru tidak mengubah format hasil lama; pesan bila format tidak ada.

### 1.6 Master "Format Dokumen Hasil" (D1, D2)

- **Permission:** `master.format-dokumen.manage` di `config/permissions.php` (modul Master Data, role `$adminSistem`). Jalankan `php artisan permission:sync` saat deploy.
- **Route:** `master/format-dokumen` dengan middleware `can:master.format-dokumen.manage`. Perbarui snapshot `RouteAuthorizationTest`.
- **Halaman:**
  - **Indeks:** per jenis dokumen; daftar versi dengan status, tahun berlaku, dan jumlah hasil yang memakai.
  - **Edit versi draft:** daftar blok yang bisa diubah judul, isi, dan butir. Blok bisa diurutkan (naik/turun) dan ditambah atau dihapus, dengan tipe terbatas pada `FormatBlok::TIPE`. Panel bantuan menampilkan daftar placeholder.
  - **Aksi:**
    - **Buat Versi Baru:** salin versi aktif menjadi draft.
    - **Salin ke Jenis Lain:** salin menjadi draft di jenis dokumen lain.
    - **Pratinjau:** PDF dengan data contoh, ditambah peringatan placeholder tidak dikenal.
    - **Aktifkan:** versi aktif sebelumnya berubah menjadi arsip. Sebelumnya tampil konfirmasi yang menyebut jumlah permohonan yang terdampak.
    - **Arsipkan.**
- **Aturan:**
  - Hanya versi `draft` yang bisa diedit. Versi aktif atau arsip ditolak di Form Request/Policy.
  - Versi yang dipakai suatu hasil tidak bisa dihapus.
  - Setiap aksi dicatat di activity log.
- **Menu:** sidebar Master Data → "Format Dokumen Hasil".

**Test:** pengguna tanpa permission → 403; edit versi aktif ditolak; aktivasi mengarsipkan versi lama; salin versi menghasilkan blok yang sama; pratinjau menandai placeholder tidak dikenal.

### 1.7 Dokumentasi Tahap 1

REFERENSI-HTTP (route master), REFERENSI-KOMPONEN (renderer, placeholder, command), REFERENSI-VIEW, RUNBOOK (langkah deploy: migrate → seeder → `hasil-fasilitasi:isi-format` → `permission:sync`), JENIS-PENGGUNA (hak kelola format).

---

## Tahap 2 — Bagian Masukan Dinamis — ✅ kode selesai 27 Okt 2026 (kecuali 2.8)

**Gerbang masuk:** Tahap 1 dirilis **dan** format baru dari stakeholder diterima (D5). **Menutup:** K4–K6, T4, backlog 2.8 (ekstraksi controller untuk alur masukan).

### 2.0 Fit-gap format baru

Cocokkan format baru dengan model data di bawah: bagian, rujukan, tampilan tabel, dan kolom. Catat penyesuaian rancangan dan hitung ulang estimasi di dokumen ini **sebelum** menulis kode.

### 2.1 Skema & model

- `bagian_masukan`: `id`, `format_dokumen_id` (FK cascade), `kode` (slug, unik per format), `nama`, `deskripsi` (nullable), `urutan`, `rujukan` (`tanpa|bab|urusan`), `tampilan_dokumen` (`tabel|daftar_bernomor`), timestamps.
- `hasil_fasilitasi_masukan`: `id`, `hasil_fasilitasi_id` (FK cascade), `bagian_masukan_id` (FK restrict), `rujukan_id` (nullable), `sub_rujukan` (string, nullable), `catatan` (text, cast `SanitizedHtml`), `nilai` (json, nullable; dipakai Tahap 3), `user_id` (FK), timestamps. Index `(hasil_fasilitasi_id, bagian_masukan_id)`.
- `format_blok.bagian_masukan_id` (FK nullable) menggantikan kolom `sumber`.
- Model `BagianMasukan` (method `rujukanModel()`: `MasterBab`/`MasterUrusan`/null) dan `HasilFasilitasiMasukan` (relasi `bagian`, `user`, aksesor `rujukan`). Relasi `HasilFasilitasi::masukan()`.

### 2.2 Migrasi data

1. Migration membuat bagian `sistematika` (rujukan bab), `konsistensi` (tanpa), `urusan` (rujukan urusan), dan `rekomendasi` (tanpa) untuk setiap format yang ada. Migration ini juga mengisi `format_blok.bagian_masukan_id` dari `sumber`.
2. Command `hasil-fasilitasi:migrasi-masukan` menyalin 4 tabel lama ke `hasil_fasilitasi_masukan` dalam satu transaksi.
   - Pemetaan: `master_bab_id`/`master_urusan_id` → `rujukan_id`, `sub_bab` → `sub_rujukan`. `user_id` dan `created_at` dipertahankan.
   - Command idempoten (melewati baris yang sudah disalin), dengan `--dry-run`.
   - Di akhir, command mencetak perbandingan jumlah baris per bagian.
3. Verifikasi: generate dokumen beberapa permohonan sebelum dan sesudah migrasi, lalu bandingkan teksnya.
4. Tabel lama **tetap ada satu rilis** (read-only, tidak dipakai kode). Penghapusannya dilakukan di 2.8.

### 2.3 Endpoint & otorisasi generik

- Route `hasil-fasilitasi/{permohonan}/masukan/{bagian:kode}` untuk POST, PUT `/{masukan}`, dan DELETE `/{masukan}`. Ini menggantikan 12 route lama; perbarui snapshot route.
- Form Request `SimpanMasukanRequest` dengan aturan yang dibangun dari bagian:
  - `rujukan_id` wajib untuk rujukan bab (harus `master_bab` sesuai jenis dokumen) atau urusan (harus `master_urusan` yang `untukHasilFasilitasi`).
  - `sub_rujukan` opsional.
  - `catatan` wajib.
- Action per use case (`TambahMasukan`, `UbahMasukan`, `HapusMasukan`) memuat kunci hasil (423), pemilik/koordinator, aturan OPD (urusan bidangnya), activity log, dan notifikasi koordinator. Logikanya dipindah dari `HasilFasilitasiController`.
- **Test:** port `HasilFasilitasiOpdTest` dan bagian endpoint `HasilFasilitasiInputPageTest` ke endpoint generik. Semua perilaku lama tetap hijau.

### 2.4 Halaman input & detail

- Tab dibangun dari `bagian_masukan` milik format hasil.
- Satu partial `daftar-masukan` memilih tampilan sesuai `rujukan`: bab → tabel dengan kolom Bab/Sub Bab; urusan → dikelompokkan per urusan; tanpa → tabel catatan. Ini menggantikan `daftar-sistematika`, `daftar-urusan`, dan `daftar-catatan`.
- Form tambah dan modal ubah dibangun dari bagian. Konfigurasi `judulUbah` dan `kolomCatatan` di `input-script` diganti atribut data dari server.
- Daftar "urusan bidang OPD yang belum diberi masukan" tetap tampil untuk bagian dengan rujukan urusan.

### 2.5 Konsumen lain

`ApprovalController` (badge jumlah masukan OPD), `PermohonanController`, kartu Kaban di `tahapan/hasil.blade.php`, partial `approval/partials/masukan-urusan`, dan command `SanitizeRichText` beralih ke `hasil_fasilitasi_masukan`.

### 2.6 Renderer

Blok `bagian_masukan` di-render sesuai `tampilan_dokumen`:

- **Tabel:** kolom rujukan (bila ada) + catatan, dikelompokkan per rujukan.
- **Daftar bernomor:** satu butir per masukan.

Golden file Tahap 1 tetap harus cocok untuk format awal.

### 2.7 Master: editor bagian

Pada versi draft, Admin dapat menambah, mengubah, mengurutkan, dan menghapus bagian (nama, deskripsi, rujukan, tampilan). Admin juga dapat menyisipkan blok `bagian_masukan` di posisi mana pun. Bagian yang sudah berisi masukan tidak bisa dihapus dari versi yang dipakai; penghapusan dilakukan lewat versi baru.

### 2.8 Pembersihan (rilis berikutnya)

Hapus 4 model, 4 tabel lama (migration dengan `down()` yang membuat ulang skema), method CRUD lama di controller, dan partial lama.

---

## Tahap 3 — Kolom Tambahan & Pengisi per Bagian — ✅ kode selesai 27 Okt 2026

**Gerbang masuk:** Tahap 2 dirilis. **Menutup:** K7–K8, T5.

### 3.1 Kolom tambahan

- `bagian_masukan.kolom_tambahan` (json): `[{kode, label, tipe: teks|angka|pilihan, wajib, pilihan[]}]`.
- Editor kolom di master (versi draft).
- `SimpanMasukanRequest` menambah aturan `nilai.{kode}` sesuai definisi: `teks` maks 1000, `angka` numeric, `pilihan` `in:`, dan `required` bila wajib. Nilai di luar definisi dibuang.
- Kolom tambahan tampil di form, daftar (sebagai kolom tabel), dan dokumen Word/PDF.

**Test:** validasi per tipe kolom; nilai tidak dikenal dibuang; kolom tampil di dokumen.

### 3.2 Pengisi dan pembaca per bagian (D7)

- `bagian_masukan.role_pengisi` (json) dan `tampil_untuk` (json, role yang boleh melihat bagian di halaman detail). Keduanya bisa diatur di master.
- Permission `hasil-fasilitasi.input` dan `hasil-fasilitasi.input-urusan` digabung menjadi `hasil-fasilitasi.input-masukan`, yang menjadi gerbang halaman input. Izin per bagian ditentukan `role_pengisi`.
  - Migrasi `config/permissions.php` + `permission:sync`. Role yang dulu punya salah satu permission lama mendapat permission baru.
  - Format awal diisi: `fasilitator` untuk semua bagian, `opd` hanya untuk `urusan`. Dengan begitu perilaku Fase 3 tidak berubah.
- `tampil_untuk` menggantikan `$showExtendedTabs` di `show()`. Format awal: Konsistensi dan Rekomendasi tampil untuk `superadmin`, `admin_peran`, `kaban`.
- **Aturan "OPD hanya urusan bidangnya" tetap di kode** (Action 2.3) untuk bagian dengan rujukan urusan.

**Test:** pengguna hanya melihat dan mengisi bagian sesuai `role_pengisi`; OPD tetap ditolak di luar urusan bidangnya; `tampil_untuk` mengatur tab detail; matriks role lama tetap sama untuk format awal.

---

## Rilis & Deploy

| Rilis | Isi | Langkah khusus |
|---|---|---|
| R1 | Tahap 1 | Backup → `migrate` → `db:seed --class=FormatDokumenSeeder` → `hasil-fasilitasi:isi-format` → `permission:sync` → generate ulang 1–2 dokumen dan bandingkan |
| R2 | Tahap 2 (2.1–2.7) | Backup → `migrate` → `hasil-fasilitasi:migrasi-masukan --dry-run` → tanpa `--dry-run` → cek jumlah baris → uji input/detail/approval |
| R3 | 2.8 + Tahap 3 | Backup → `migrate` (hapus tabel lama, kolom JSON baru) → `permission:sync` |

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Dokumen resmi berubah setelah renderer diganti | Golden file 1.0 dan test perbandingan; format awal disalin persis |
| Desain bagian tidak cocok dengan format baru | Tahap 2 menunggu format (D5); fit-gap 2.0 sebelum kode |
| Migrasi masukan gagal sebagian | Satu transaksi, `--dry-run`, perbandingan jumlah baris, tabel lama dipertahankan satu rilis, backup |
| Admin salah mengubah format aktif | Hanya draft yang bisa diedit; pratinjau; aktivasi dengan konfirmasi; versi lama bisa diaktifkan kembali dengan menyalinnya |
| Aturan OPD hilang saat akses menjadi generik | Aturan urusan bidang tetap di Action; test OPD lama di-port, bukan dihapus |
| Penggabungan permission memutus akses role kustom | Migrasi permission memetakan pemilik permission lama ke permission baru; uji matriks role |

## Kriteria Selesai Fase 6

- [x] Tidak ada teks narasi, dasar hukum, atau penandatangan di kode renderer
- [x] Word dan PDF dibangun dari satu struktur; golden file format awal cocok
- [x] Setiap hasil fasilitasi terkunci ke satu versi format
- [x] Admin dapat membuat, menyalin, mempratinjau, dan mengaktifkan versi format tanpa developer
- [ ] Bagian masukan, kolom tambahan, dan pengisi diatur dari master *(✅ 27 Okt 2026)*; 4 tabel lama dihapus *(menunggu 2.8)*
- [x] Perilaku Fase 3 (masukan OPD, kunci, tampilan Kaban) tetap terjaga oleh test
- [ ] Seluruh test hijau; setiap rilis dengan backup

## Catatan Pelaksanaan

### Tahap 1 — 27 Oktober 2026

**Hasil**

- **Golden file (1.0):** `tests/Fixtures/dokumen-hasil/*.word.txt` direkam dari renderer lama untuk dua skenario (kabupaten dengan masukan lengkap; kota tanpa masukan). `DokumenHasilFasilitasiTest` membuktikan dokumen Word dari format seeder **identik** dengan golden, dan PDF berisi **teks yang sama** dengan Word.
- **Skema & model (1.1):** migration `create_format_dokumen_tables`; model `FormatDokumen` (`untuk()`, `salin()`, `aktifkan()`), `FormatBlok` (tipe, sumber, penomoran), enum `StatusFormatDokumen`, factory.
- **Seeder (1.2):** `FormatDokumenSeeder` membuat "Format Awal" v1 aktif (berlaku mulai 2020) untuk setiap jenis dokumen; isi bloknya disalin dari teks lama. Jarak baris (`spasi_setelah`) dan halaman baru juga direplikasi.
- **Placeholder (1.3):** `PlaceholderDokumen` dengan 8 placeholder + varian `_kapital`; ditambah `{jenis_dokumen_asli}` (lihat temuan di bawah).
- **Renderer (1.4):** `DokumenHasilRenderer` → `WordWriter` / `PdfWriter` (view generik `dokumen-pdf`). `HasilFasilitasiDocumentService` kini mengatur alurnya; `pdf.blade.php` lama dan teks hard-code di service dihapus.
- **Penguncian (1.5):** hook `HasilFasilitasi::creating`, command `hasil-fasilitasi:isi-format`, pesan bila format belum ada.
- **Master (1.6):** `FormatDokumenController` + 2 view, permission `master.format-dokumen.manage`, menu sidebar, snapshot route diperbarui. Penyuntingan dilakukan per versi draft (satu tombol Simpan untuk semua blok); tambah, pindah, dan hapus blok memakai form terpisah.
- **Test:** `DokumenHasilFasilitasiTest` (2), `FormatDokumenTest` (15), `tests/Unit/PlaceholderDokumenTest` (3).

**Penyimpangan dari plan**

- Kelas pendukung ditaruh di `app/Services/DokumenHasil/` (bukan `App\Support`), agar tidak membuat folder dasar baru.
- Tipe blok ditambah `subbab` (bernomor 1, 2, … per bab) dan `keterangan_tabel`. Kolom blok ditambah `spasi_setelah`, agar tata letak lama bisa direplikasi tanpa aturan tersembunyi.
- Pratinjau tidak diwajibkan sebelum aktivasi (tetap tersedia dan disarankan di konfirmasi).
- Beberapa versi boleh aktif untuk satu jenis dokumen asalkan tahun berlakunya berbeda (konsekuensi D3); aktivasi hanya mengarsipkan versi aktif dengan tahun berlaku yang sama.

**Perubahan isi PDF (perbaikan bug lama)** — PDF kini dibangun dari struktur yang sama dengan Word, sehingga:

- `&` di catatan/nama dokumen tidak lagi tampil sebagai `&amp;` (dulu escape ganda).
- Angka Romawi di nama bab/sub bab tabel sistematika kapital (`Bab II`, dulu `Bab Ii`).
- Jarak antarbagian PDF mengikuti `spasi_setelah` (sedikit berbeda dari jarak CSS lama).

**Temuan untuk tim**

- `{jenis_dokumen}` (dipertahankan dari kode lama) menghasilkan "Rkpd"/"Rpjmd" di narasi karena `ucwords(strtolower())`. Placeholder `{jenis_dokumen_asli}` ("RKPD") sudah tersedia; tim dapat menggantinya lewat master format bila ingin.
- Narasi RPJMD/RPJPD masih memakai teks Ranperkada RKPD (D4); perbaikannya dilakukan tim lewat master format.

### Tahap 2 — 27 Oktober 2026

Dikerjakan tanpa format baru atas keputusan pemilik proyek (D5 diubah); empat bagian lama menjadi data awal.

**Hasil**

- **2.0 Fit-gap:** dilewati karena format baru belum ada. Rancangan diuji dengan menambah bagian baru lewat master (test `test_section_added_in_master_appears_on_input_page_and_in_document`).
- **2.1 Skema:** migration `create_bagian_masukan_tables` membuat `bagian_masukan` dan `hasil_fasilitasi_masukan`, serta `format_blok.bagian_masukan_id` (menggantikan `sumber`). Empat bagian awal dibuat untuk setiap format yang ada. Bagian punya `label_catatan` (label form & kolom tabel) dan `tampilan_dokumen` (`tabel`, `daftar_huruf`, `daftar_angka`; bagian berujukan bab/urusan selalu tabel).
- **2.2 Migrasi data:** command `hasil-fasilitasi:migrasi-masukan` menyalin isi apa adanya (tanpa sanitasi ulang), pengirim dan waktu dipertahankan, idempoten lewat `sumber_lama`/`sumber_lama_id`, dalam satu transaksi. Data lokal: 1.194 baris (949 sistematika, 54 konsistensi, 164 urusan, 27 rekomendasi). Golden test kini mengisi tabel lama → migrasi → dokumen, dan hasilnya tetap identik.
- **2.3 Endpoint:** 12 route/method CRUD diganti 3 (`hasil-fasilitasi.masukan.store/update/destroy`). Controller turun dari ±1.550 ke ±1.180 baris.
- **2.4 Tampilan:** tab, form, daftar, dan modal ubah di halaman input & detail dibangun dari bagian format; JS tidak lagi menyimpan peta per bagian.
- **2.5 Konsumen:** ApprovalController (badge & partial masukan urusan), PermohonanController, kartu Kaban di tahapan hasil, hapus urusan di master, command `richtext:sanitize`.
- **2.6 Renderer:** blok bagian masukan dirender menurut rujukan/tampilan bagian; tipe struktur baru `tabel_catatan`.
- **2.7 Master:** daftar bagian di halaman format (ubah nama, label, petunjuk, rujukan, tampilan; tambah; naik/turun; hapus bila belum berisi masukan); blok "Bagian masukan tim" memilih bagian milik format yang sama (divalidasi). `FormatDokumen::salin()` ikut menyalin bagian dan memetakan ulang blok.
- **Test:** `MasukanHasilFasilitasiTest` (8) dan test lama di-port ke endpoint/tabel baru (`HasilFasilitasiOpdTest`, `HasilFasilitasiInputPageTest`, `KabanMasukanHasilTest`, `MasterUrusanControllerTest`).

**Penyimpangan dari plan**

- **Validasi tanpa Form Request dan tanpa Action.** Form Request berjalan sebelum controller sehingga mengubah urutan respons yang dikunci test (kunci 423 dan bagian terlarang 403 harus mendahului validasi 422). Aturan validasi tetap dibangun dari bagian (`aturanMasukan`), dijalankan di controller setelah cek kunci & akses. Logika tetap di `HasilFasilitasiController` (3 method generik) karena memakai helper otorisasi tim yang ada; ekstraksi penuh (backlog 2.8 lama) belum dilakukan.
- **Akses per bagian sementara** di `BagianMasukan::bisaDiisiOleh()` / `terlihatOleh()`: meniru perilaku lama berdasarkan rujukan (urusan → `input-urusan`; lainnya → `input`; bagian tanpa rujukan hanya terlihat Admin & Kaban di halaman detail). Tahap 3 menggantinya dengan pengaturan per bagian.
- Empat relasi lama (`hasilSistematika`, dst.) dihapus dari `HasilFasilitasi`; model dan tabel lama tetap ada sampai 2.8.

**Belum dikerjakan:** 2.8 (hapus 4 tabel & model lama serta kolom `sumber_lama*`) — rilis berikutnya setelah migrasi data di server terverifikasi.

### Tahap 3 — 27 Oktober 2026

**Hasil**

- **Skema:** migration `add_kolom_dan_akses_to_bagian_masukan_table` menambah `kolom_tambahan`, `role_pengisi`, `tampil_untuk` (JSON) pada `bagian_masukan`. Nilai awal meniru perilaku lama: sistematika/konsistensi/rekomendasi diisi `fasilitator`; urusan diisi `fasilitator` & `opd`; konsistensi & rekomendasi tampil di detail hanya untuk `superadmin`, `admin_peran`, `kaban`.
- **3.1 Kolom tambahan:** tipe `teks` (maks 1000), `angka`, `pilihan`; bisa wajib. Validasi dibangun dari definisi (`BagianMasukan::aturanNilai`), kunci di luar definisi dibuang (`saringNilai`). Tampil di form tambah, modal ubah, dan daftar ("Label: nilai"). Di dokumen: menjadi **kolom sendiri** pada bagian bertampilan tabel tanpa rujukan; pada tabel bab/urusan dan daftar ditulis di belakang catatan, "(Label: nilai; …)". Kode kolom tetap saat label diubah, sehingga nilai lama tetap terbaca.
- **3.2 Pengisi & pembaca (D7):** `hasil-fasilitasi.input` dan `hasil-fasilitasi.input-urusan` diganti:
  - `hasil-fasilitasi.input-masukan` (fasilitator, OPD): halaman input & route masukan; bagian yang boleh diisi = role pengguna ada di `role_pengisi` bagian.
  - `hasil-fasilitasi.kelola-draft` (fasilitator): ajukan hasil untuk validasi & buat draft.
  Migration memindahkan hak ke permission baru untuk setiap role yang memegang permission lama (termasuk role kustom), lalu `permission:sync` menghapus permission lama. Dicek di database lokal: `input-masukan` → fasilitator, opd; `kelola-draft` → fasilitator.
- **Master:** per bagian ada pilihan role "Diisi oleh" dan "Tampil di halaman detail untuk" (kosong = semua yang dapat melihat hasil), serta tabel kolom tambahan (ubah, tambah satu per simpan, hapus). Kolom bertipe pilihan wajib berisi daftar pilihan.
- **Test:** 4 test baru di `MasukanHasilFasilitasiTest` (validasi & penyaringan nilai, pencetakan nilai, pengisi/pembaca dari pengaturan, penyimpanan master); seluruh test lama tetap hijau.

**Penyimpangan dari plan**

- Permission `hasil-fasilitasi.input` ternyata juga menjaga aksi khusus tim fasilitator (ajukan hasil, buat draft), sehingga tidak dihapus begitu saja melainkan **diganti nama** menjadi `hasil-fasilitasi.kelola-draft` dengan perilaku sama. Bila digabung, OPD akan ikut mendapat akses ke aksi tersebut.
- Pembatasan "OPD hanya urusan bidangnya" kini ditentukan dari akun yang **terikat OPD** (`opd_id` terisi) dan memegang `input-masukan`, bukan dari kombinasi dua permission lama.
- Pada tabel bab/urusan, nilai kolom tambahan dicetak menyertai catatan (bukan kolom sendiri), karena satu baris tabel tersebut bisa menggabungkan beberapa catatan.
- Test migrasi permission tidak dibuat (permission lama tidak ada di database test yang dibangun dari katalog); pemindahan diverifikasi manual di database lokal.

### Perbaikan UX penyunting format — 27 Oktober 2026

- Halaman dibagi tiga tab: **Isi Dokumen** (kerangka blok ringkas, bisa dibuka per blok; nomor bab/sub bab dan indentasi tampil langsung), **Bagian Masukan**, dan **Pengaturan** (nama, tahun berlaku, salin, hapus draft).
- **Tidak ada lagi perubahan yang hilang:** pindah (naik/turun) dan hapus berlangsung di halaman tanpa memuat ulang, lalu tersimpan saat Simpan (urutan & tanda hapus lewat field tersembunyi; hapus bisa dibatalkan). Sisip blok di posisi mana pun (menu ⋮ "Sisipkan di bawah") dan tambah bagian mengirim form utama dengan `aksi`, sehingga semua perubahan lain ikut tersimpan lebih dulu. Enam endpoint terpisah (tambah/pindah/hapus blok & bagian) dihapus.
- Bilah simpan menempel di bawah dengan status "Ada perubahan yang belum disimpan", tombol batalkan, dan peringatan saat meninggalkan halaman; Pratinjau mengingatkan bila ada perubahan belum disimpan; Aktifkan ditolak sampai perubahan disimpan.
- Placeholder bisa diklik untuk disisipkan ke isian yang sedang diketik. Role tampil dengan nama yang mudah dibaca (`config('permissions.label_role')`) sebagai tombol pilihan.
- Field yang tidak relevan disembunyikan (daftar pilihan hanya untuk kolom bertipe pilihan; catatan tampilan untuk bagian berujukan); kolom tambahan ditambah/dihapus per baris di halaman.
- Validasi: bagian yang berisi masukan tidak dapat dihapus (pesan validasi, bukan halaman galat); setelah validasi gagal, urutan dan tanda hapus yang sedang disusun dipertahankan dan baris yang bermasalah terbuka dengan garis merah.
