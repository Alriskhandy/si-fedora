# Panduan Pengembang — SI-FEDORA

Panduan untuk developer yang akan merawat atau mengembangkan aplikasi ini. Fokusnya mekanisme yang **tidak terlihat jelas dari membaca satu file saja**, dan file mana yang harus disentuh untuk perubahan tertentu.

Setup lokal ada di [`README.md`](../../README.md). Operasional server ada di [`RUNBOOK.md`](../operasional/RUNBOOK.md). Indeks controller, middleware, dan form request ada di [`REFERENSI-HTTP.md`](REFERENSI-HTTP.md); command, policy, service, provider, dan cast di [`REFERENSI-KOMPONEN.md`](REFERENSI-KOMPONEN.md); struktur view dan layout di [`REFERENSI-VIEW.md`](REFERENSI-VIEW.md).

---

## 1. Gambaran Singkat

| Komponen | Keterangan |
|---|---|
| Stack | Laravel 12, PHP 8.3, PostgreSQL, Blade + template Bootstrap (Sneat) |
| Otorisasi | spatie/laravel-permission, berbasis **permission** (bukan nama role) |
| Penyimpanan dokumen | Disk privat `dokumen` (`storage/app/private`), disajikan lewat route yang memeriksa hak akses |
| Notifikasi | Notifikasi database (`notifikasi`) + WhatsApp via Fonnte |
| Test | PHPUnit, database terpisah `sifedora_testing` |

### Alur bisnis

Satu **permohonan** dari kabupaten/kota melewati 7 tahapan (`master_tahapan`, urut menurut kolom `urutan`):

| # | Tahapan | Pelaku utama | Model/tabel utama |
|---|---|---|---|
| 1 | Permohonan | Pemohon | `Permohonan`, `PermohonanDokumen` |
| 2 | Verifikasi | Verifikator, Admin PERAN | `PermohonanDokumen` (status verifikasi), `LaporanVerifikasi` |
| 3 | Penetapan Jadwal | Kaban | `PenetapanJadwalFasilitasi` |
| 4 | Pelaksanaan | Admin PERAN, Tim | `UndanganPelaksanaan`, `DokumenTahapan` |
| 5 | Hasil Fasilitasi / Evaluasi | Fasilitator → Admin → Kaban | `HasilFasilitasi` + `HasilFasilitasi{Sistematika,Urusan,Form,Rekomendasi}` |
| 6 | Tindak Lanjut Hasil | Pemohon | `DokumenTahapan` |
| 7 | Penetapan PERDA / PERKADA | Pemohon | `DokumenTahapan` |

Status tiap tahapan per permohonan disimpan di `PermohonanTahapan`. Status akhir permohonan (`belum`/`proses`/`revisi`/`selesai`) ada di `permohonan.status_akhir`.

---

## 2. Otorisasi

### Lapisan

```
Route middleware  can:<permission>       → boleh membuka fitur?        (routes/web/*.php)
Policy            PermohonanPolicy, dst. → boleh mengakses record ini? (app/Policies)
Scope             Permohonan::visibleTo  → record mana yang tampil di daftar?
View              @can('<permission>')   → tampilkan tombol/menu?
```

- **Sumber nama permission:** [`config/permissions.php`](../../config/permissions.php). Nama permission tidak dibuat lewat UI.
- **Tidak ada `Gate::before` untuk superadmin.** Superadmin mendapat permission secara eksplisit. Dengan `Gate::before`, superadmin akan lolos ke fitur khusus pemohon yang membutuhkan data kab/kota.
- **Pengecekan role (`hasRole`) hanya dipakai untuk identitas**, bukan hak akses: memilih penerima notifikasi, memilih dashboard, memvalidasi bahwa user target memang verifikator, dan tata letak view.

### Menambah atau mengubah permission

1. Tambahkan entri di `config/permissions.php` (nama, modul, label, role default).
2. Pakai di route (`->middleware('can:nama.permission')`), policy, atau view (`@can`).
3. Jalankan `php artisan permission:sync` (lokal & server).
4. Jalankan `UPDATE_ROUTE_SNAPSHOT=1 php artisan test --filter=RouteAuthorizationTest`, lalu **review diff** `tests/Fixtures/route-authorization.json`. Diff ini harus sama dengan perubahan hak akses yang dimaksud.
5. Perbarui [`JENIS-PENGGUNA.md`](../produk/JENIS-PENGGUNA.md).

`permission:sync` tanpa `--reset` hanya **menambah** permission baru ke role default dan tidak mencabut perubahan dari UI; role baru di katalog langsung mendapat seluruh pemetaan default-nya. `--reset` menyamakan semua role persis dengan katalog. Pakai `--reset` hanya untuk instalasi baru atau atas keputusan sadar.

### Test penjaga

`tests/Feature/RouteAuthorizationTest.php` merekam, untuk setiap route × role, apakah middleware menolak (403). Test ini gagal bila ada perubahan akses yang tidak disengaja. Test ini **tidak** mencakup pengecekan di dalam controller/policy; itu dicakup test feature masing-masing (mis. `PermohonanPolicyTest`).

---

## 3. Dokumen & Berkas

- Dokumen resmi disimpan di disk **`dokumen`** (`config/filesystems.php`), **bukan** `public`. Pengecualian: foto profil (disk `public`).
- Simpan dengan `$file->store('<folder>', 'dokumen')`. Nama file otomatis acak. Simpan nama asli di kolom `file_name` bila tersedia.
- **Jangan** menautkan dokumen dengan `asset('storage/...')` atau `Storage::url()`. Gunakan:
  - `route('berkas.show', ['<jenis>', $record->id])` untuk menampilkan inline, atau tambah `'unduh' => 1` untuk mengunduh;
  - atau route unduh khusus modul yang memeriksa hak akses.
- Menambah jenis berkas yang bisa diunduh lewat `berkas.show`: tambahkan entri di `BerkasController::JENIS` (model, kolom path, kolom nama asli). Otorisasi otomatis mengikuti `PermohonanPolicy::downloadDokumen` lewat relasi `permohonan`.
- URL berkas tidak berekstensi. Preview di halaman Arsip membaca jenis file dari atribut `data-file-ext`.

---

## 4. Rich Text

Catatan hasil fasilitasi (TinyMCE) dirender dengan `{!! !!}`. Keamanannya bergantung pada cast **`App\Casts\SanitizedHtml`**, yang menyanitasi saat **menyimpan**.

- Whitelist ada di `app/Providers/HtmlSanitizerServiceProvider.php` dan harus sama dengan `valid_elements` TinyMCE di `resources/views/pages/fasilitasi/hasil-fasilitasi/create.blade.php`.
- **Menambah kolom rich text baru:** daftarkan kolomnya di `casts()` model dengan `SanitizedHtml::class`, lalu tambahkan ke `SanitizeRichText::COLUMNS` agar data lama bisa dibersihkan.
- Teks biasa yang dirender dengan baris baru: gunakan `{!! nl2br(e($teks)) !!}`, bukan `{!! $teks !!}`.

---

## 5. Notifikasi

- Pintu utama: `App\Services\PermohonanNotificationService` (notifikasi database + WhatsApp).
- WhatsApp dikirim lewat `App\Services\FonteService`, **sinkron** di dalam request. Token dibaca dari `config('services.fonnte.token')` (env `FONNTE_TOKEN`); jangan memakai `env()` langsung di kode karena kosong setelah `config:cache`. Pemindahan ke queue direncanakan di backlog 2.10.
- Di beberapa controller (mis. `ApprovalController`) masih ada blok "notifikasi legacy". Sebagian blok itu satu-satunya jalur notifikasi untuk penerima tertentu (superadmin, tim saat reject). **Jangan dihapus** tanpa memindahkan penerimanya ke service. Lihat catatan Fase 1.
- Di test, selalu `Http::preventStrayRequests()` + `Http::fake(['https://api.fonnte.com/*' => ...])`.

---

## 6. Model

Konvensi yang dipakai di `app/Models`:

- Docblock kelas menjelaskan peran model dalam satu kalimat.
- Cast lewat method `casts()`, bukan properti `$casts`.
- Setiap relasi punya return type (`BelongsTo`, `HasMany`, ...), dan scope bertipe `Builder $query`.
- Kolom yang benar-benar ada di tabel saja yang boleh muncul di `$fillable`, relasi, dan cast.

Hal yang perlu diketahui:

| Hal | Keterangan |
|---|---|
| Kolom status | Di-cast ke enum `app/Enums` (Fase 4): atributnya enum, bukan string. Bandingkan dengan kasus enum; aturan lengkap di [REFERENSI-KOMPONEN §Enums](REFERENSI-KOMPONEN.md#enums). |
| `PersyaratanDokumen`, `TimFasilitasiAssignment` | Tabel lama. Penggantinya `MasterKelengkapanVerifikasi` dan `UserKabkotaAssignment`. |
| `tim_fasilitasi_assignment` | Tidak punya `updated_at` (`UPDATED_AT = null`). |

Untuk memeriksa relasi yang menunjuk tabel/kolom yang tidak ada, instansiasi setiap relasi lalu cocokkan kunci-kuncinya dengan `Schema::hasColumn`. Cara ini dipakai saat perapian 24 Okt 2026.

---

## 7. Test

- Database: `sifedora_testing` (diatur di `phpunit.xml`). `Tests\TestCase::createApplication()` **menghentikan test** bila koneksi menunjuk database lain. Ini mencegah `RefreshDatabase` mengosongkan database aplikasi.
- **Jangan** menjalankan command artisan yang menulis database dengan `--env=testing`. Tanpa `.env.testing`, command itu jatuh ke `.env` (database aplikasi). Uji lewat PHPUnit.
- Factory: `User::factory()->withRole('pemohon')` membuat user dengan role beserta permission default dari katalog. Tersedia juga factory `Permohonan`, `JadwalFasilitasi`, `HasilFasilitasi`, `KabupatenKota`, `Master*`.
- Perintah:

```bash
php artisan test --compact                              # semua
php artisan test --compact --filter=PermohonanPolicyTest
vendor/bin/pint --dirty                                 # format file yang berubah
```

CI belum disiapkan (backlog Fase 5); jalankan keduanya manual sebelum merge.

---

## 8. Command Artisan Aplikasi

| Command | Kegunaan | Kapan |
|---|---|---|
| `permission:sync [--reset]` | Sinkronkan role & permission dengan katalog | Setiap rilis; `--reset` sekali di rilis Fase 2 |
| `berkas:pindah-ke-privat [--dry-run] [--rollback]` | Pindahkan dokumen lama dari folder publik ke disk `dokumen` | Sekali di rilis Fase 1 |
| `richtext:sanitize [--dry-run]` | Sanitasi ulang rich text lama | Sekali di rilis Fase 0, atau setelah menambah kolom rich text |
| `permohonan:remind-deadline` | Pengingat H-3/H-2/H-1 batas pengajuan | Scheduler, harian 08:00 |
| `otp:cleanup` | Hapus kode OTP kedaluwarsa | Scheduler, tiap jam |
| `permohonan:sync-tahapan` | Lengkapi record tahapan permohonan yang belum punya | Manual bila diperlukan |

---

## 9. Utang Teknis yang Diketahui

| Item | Rencana |
|---|---|
| Notifikasi sinkron & blok legacy | Backlog 2.10 |
| `HasilFasilitasiController` ±1.500 baris | Dipecah bersama format hasil dinamis ([Fase 6](../implementasi/FASE-6-FORMAT-HASIL-DINAMIS.md) Tahap 2) |
| Status non-utama belum di-cast enum (`dokumen_verifikasi_detail`, `laporan_verifikasi.status_kelengkapan`, surat, undangan) | Dikerjakan saat modulnya disentuh |
| 68 file PHP lama belum mengikuti format Pint | Dirapikan saat file disentuh |
| `public/undangan` berisi file undangan lama di web root (sudah dikeluarkan dari git) | Cek record produksi, lalu pindahkan ke disk `dokumen` |
| Domain `si-fedora.malutprov.go.id` ditulis langsung di pesan WhatsApp (`PermohonanNotificationService`) | Ganti dengan `config('app.url')` saat notifikasi diperbaiki (backlog 2.10) |
