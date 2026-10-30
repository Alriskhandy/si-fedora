# Referensi Komponen Aplikasi — SI-FEDORA

Indeks isi `app/` di luar lapisan HTTP: command, policy, provider, service, cast, dan komponen view. Detail tiap kelas dan method ada di docblock masing-masing file. Lapisan HTTP (controller, middleware, form request) ada di [Referensi HTTP](REFERENSI-HTTP.md); model di [Panduan Pengembang §6](PANDUAN-PENGEMBANG.md#6-model).

---

## Struktur `app/`

```
app/
├── Casts/             cast Eloquent kustom
├── Enums/             backed enum untuk kolom status
├── Console/Commands/  command artisan (terdaftar otomatis)
├── Http/              controller, middleware, form request   → REFERENSI-HTTP.md
├── Models/            model Eloquent                          → PANDUAN-PENGEMBANG.md §6
├── Policies/          aturan akses per record (ditemukan otomatis)
├── Providers/         konfigurasi dan pendaftaran layanan
├── Services/          logika yang dipakai lebih dari satu controller atau memanggil layanan luar
└── View/              komponen dan composer Blade
```

### Di mana menaruh kode baru?

| Jenis kode | Taruh di | Contoh |
|---|---|---|
| Aturan "boleh melihat/mengubah record ini?" | `Policies/` (nama `NamaModelPolicy`, ditemukan otomatis) | `PermohonanPolicy::update` |
| Pemanggilan layanan luar (API, WhatsApp) | `Services/` | `FonteService` |
| Logika bisnis yang dipakai beberapa controller | `Services/` | `PermohonanNotificationService` |
| Tugas terjadwal atau perbaikan data sekali jalan | `Console/Commands/` (`php artisan make:command`) | `RemindPermohonanDeadline` |
| Transformasi nilai kolom saat simpan/baca | `Casts/` | `SanitizedHtml` |
| Daftar nilai tetap sebuah kolom (status) beserta label/badge-nya | `Enums/` + cast di model | `StatusPermohonan` |
| Data yang selalu dibutuhkan satu partial view | `View/Composers/` + daftarkan di `AppServiceProvider` | `NavbarComposer` |
| Konfigurasi atau nilai dari `.env` | `config/*.php`, dibaca dengan `config()` | `services.fonnte.token` |

> **Jangan memanggil `env()` di dalam `app/`.** Setelah `php artisan config:cache` (langkah deploy), `env()` di luar folder `config/` mengembalikan `null`. Tambahkan kuncinya di `config/*.php`, lalu baca dengan `config()`.

---

## `Console/Commands/`

Semua command terdaftar otomatis. Jadwalnya diatur di [`bootstrap/app.php`](../../bootstrap/app.php) (`withSchedule`).

| Command | Kelas | Fungsi | Kapan dijalankan |
|---|---|---|---|
| `permission:sync [--reset]` | [`SyncPermissions`](../../app/Console/Commands/SyncPermissions.php) | Sinkronkan role & permission dengan `config/permissions.php`. Role yang baru dibuat langsung mendapat seluruh pemetaan default-nya | Setiap rilis; `--reset` hanya untuk instalasi baru / rilis Fase 2 |
| `berkas:pindah-ke-privat [--dry-run] [--rollback]` | [`PindahkanBerkasKePrivat`](../../app/Console/Commands/PindahkanBerkasKePrivat.php) | Pindahkan dokumen lama ke disk `dokumen` | Sekali, rilis Fase 1 (maintenance mode) |
| `richtext:sanitize [--dry-run]` | [`SanitizeRichText`](../../app/Console/Commands/SanitizeRichText.php) | Sanitasi ulang rich text lama | Sekali, rilis Fase 0; atau setelah whitelist sanitizer diubah |
| `permohonan:remind-deadline` | [`RemindPermohonanDeadline`](../../app/Console/Commands/RemindPermohonanDeadline.php) | Pengingat H-3/H-2/H-1 batas pengajuan permohonan | Terjadwal, setiap hari 08:00 |
| `otp:cleanup` | [`CleanupExpiredOtp`](../../app/Console/Commands/CleanupExpiredOtp.php) | Hapus OTP kedaluwarsa/terpakai | Terjadwal, setiap jam |
| `permohonan:sync-tahapan` | [`SyncPermohonanTahapan`](../../app/Console/Commands/SyncPermohonanTahapan.php) | Lengkapi tahapan pertama permohonan lama | Manual, bila ada permohonan tanpa record tahapan |
| `hasil-fasilitasi:migrasi-masukan [--dry-run]` | [`MigrasiMasukanHasilFasilitasi`](../../app/Console/Commands/MigrasiMasukanHasilFasilitasi.php) | Salin masukan dari empat tabel lama ke `hasil_fasilitasi_masukan` (bagian sesuai kode pada format hasil); idempoten lewat `sumber_lama` | Sekali, rilis Fase 6 Tahap 2 (setelah `hasil-fasilitasi:isi-format`) |
| `hasil-fasilitasi:isi-format [--dry-run]` | [`IsiFormatHasilFasilitasi`](../../app/Console/Commands/IsiFormatHasilFasilitasi.php) | Kunci format dokumen pada hasil fasilitasi lama yang belum punya format | Sekali, rilis Fase 6 Tahap 1 (setelah `FormatDokumenSeeder`) |

Command yang mengubah data sebaiknya punya opsi `--dry-run` dan aman dijalankan ulang.

---

## `Policies/`

Ditemukan otomatis berdasarkan nama (`App\Models\X` → `App\Policies\XPolicy`); tidak perlu didaftarkan. Pengecualian: `RolePolicy` untuk model `Role` milik Spatie didaftarkan di `AppServiceProvider` (`Gate::policy`). Policy memeriksa **permission** (`$user->can('...')`), bukan nama role.

| Policy | Ability | Dipakai oleh |
|---|---|---|
| [`PermohonanPolicy`](../../app/Policies/PermohonanPolicy.php) | `view`, `update`, `downloadDokumen` | `PermohonanController`, `ArsipController`, `BerkasController`, `PelaksanaanFasilitasiController` |
| [`PerpanjanganWaktuPolicy`](../../app/Policies/PerpanjanganWaktuPolicy.php) | `viewAny`, `view`, `create`, `delete` | `PerpanjanganWaktuController`, `BerkasController` |
| [`UserPolicy`](../../app/Policies/UserPolicy.php) | `manage` | `UserController`, `UpdateUserRequest` |
| [`RolePolicy`](../../app/Policies/RolePolicy.php) | `create`, `update`, `delete`; helper `lockedPermissionsFor`, `hiddenRolesFor`, `isCatalogRole` | `RoleController`, `StoreRoleRequest`, `UpdateRoleRequest`. Role terlindungi (`superadmin`) dan permission tingkat pengelola (`role.manage`, `role.manage-protected`, `user.manage-superadmin`) hanya untuk pemegang `role.manage-protected`; role katalog tidak bisa dihapus. Test: `RoleControllerTest`. |

`PermohonanPolicy::view` punya pasangan untuk query daftar: scope `Permohonan::visibleTo`. **Ubah keduanya bersamaan.** Test: `PermohonanPolicyTest`, `ArsipControllerTest`.

---

## `Providers/`

Didaftarkan di [`bootstrap/providers.php`](../../bootstrap/providers.php).

| Provider | Fungsi |
|---|---|
| [`AppServiceProvider`](../../app/Providers/AppServiceProvider.php) | Gate `viewLogViewer` (Log Viewer → permission `log-viewer.view`), paginasi Bootstrap 5, `NavbarComposer` untuk `layouts.partials.navbar`. |
| [`HtmlSanitizerServiceProvider`](../../app/Providers/HtmlSanitizerServiceProvider.php) | Singleton `HtmlSanitizer` dengan whitelist elemen. Whitelist harus sama dengan `valid_elements` TinyMCE di `pages/fasilitasi/hasil-fasilitasi/create.blade.php`. |

---

## `Services/`

| Service | Fungsi | Dipakai oleh | Catatan |
|---|---|---|---|
| [`FonteService`](../../app/Services/FonteService.php) | Kirim pesan WhatsApp lewat API Fonnte (tunggal dan massal), format nomor ke `62…` | `PermohonanNotificationService`, `PhoneVerificationController` (OTP), `JadwalFasilitasiController`, `TimAssignmentController` | Token dari `config('services.fonnte.token')` (env `FONNTE_TOKEN`). Pengiriman sinkron. |
| [`PermohonanNotificationService`](../../app/Services/PermohonanNotificationService.php) | Notifikasi database + WhatsApp untuk setiap peristiwa alur permohonan (diajukan, diverifikasi, jadwal, hasil, approval, tindak lanjut, perpanjangan, pengingat) | Controller tahapan, `RemindPermohonanDeadline` | Satu method publik per peristiwa (`notify…`). Penerima dipilih berdasarkan role atau penugasan tim. |
| [`HasilFasilitasiDocumentService`](../../app/Services/HasilFasilitasiDocumentService.php) | Membuat draft hasil fasilitasi (Word & PDF) di disk `dokumen` dari format berbasis data; pratinjau format dengan data contoh | `HasilFasilitasiController::generate`, `FormatDokumenController::pratinjau` | `formatUntuk()` memakai format yang terkunci di hasil, atau mengunci format aktif untuk hasil lama |
| [`DokumenHasil/DokumenHasilRenderer`](../../app/Services/DokumenHasil/DokumenHasilRenderer.php) | Menyusun **struktur** dokumen dari blok format: placeholder diganti, bab/sub bab bernomor otomatis, masukan dikelompokkan | `HasilFasilitasiDocumentService` | Satu struktur untuk Word dan PDF, sehingga isinya selalu sama |
| [`DokumenHasil/WordWriter`](../../app/Services/DokumenHasil/WordWriter.php), [`PdfWriter`](../../app/Services/DokumenHasil/PdfWriter.php) | Menulis struktur ke .docx (PhpWord) dan PDF (DomPDF, view `hasil-fasilitasi/dokumen-pdf`) | `HasilFasilitasiDocumentService` | Gaya baku F4/Arial 12 ada di kode; isi diatur Admin di master format |
| [`DokumenHasil/PlaceholderDokumen`](../../app/Services/DokumenHasil/PlaceholderDokumen.php), [`TeksDokumen`](../../app/Services/DokumenHasil/TeksDokumen.php) | Placeholder teks format (`{kabkota}`, `{tahun}`, …, varian `_kapital`) dan pembersih teks dokumen | Renderer, master format | Penggantian dengan daftar tetap (`strtr`), bukan Blade, agar teks Admin tidak bisa menjalankan kode |

Menambah notifikasi baru: buat method `notify<Peristiwa>()` di `PermohonanNotificationService` (notifikasi database dulu, lalu WhatsApp bila penerima punya `no_hp`), bungkus dengan `try/catch` + `Log::error` agar kegagalan pengiriman tidak menggagalkan aksi utama, lalu uji dengan `Http::fake()`.

---

## `Casts/`

| Cast | Fungsi | Dipakai di |
|---|---|---|
| [`SanitizedHtml`](../../app/Casts/SanitizedHtml.php) | Menyanitasi HTML saat **menyimpan** agar aman dirender dengan `{!! !!}` | `HasilFasilitasiForm.catatan`, `HasilFasilitasiRekomendasi.catatan`, `HasilFasilitasiSistematika.catatan_penyempurnaan`, `HasilFasilitasiUrusan.catatan_masukan` |

Bila menambah kolom rich text: daftarkan cast di model **dan** tambahkan kolomnya ke `SanitizeRichText::COLUMNS`.

---

## `Enums/`

Kolom status di-cast ke backed enum (Fase 4 butir C), sehingga atributnya bernilai enum, **bukan string**.

| Enum | Kolom | Method |
|---|---|---|
| [`StatusPermohonan`](../../app/Enums/StatusPermohonan.php) | `permohonan.status_akhir`, `permohonan_tahapan.status` | `label()`, `badgeClass()`, `bisaUbahDokumen()` |
| [`StatusHasilFasilitasi`](../../app/Enums/StatusHasilFasilitasi.php) | `hasil_fasilitasi.status_draft` (nullable) | `label()`, `badgeClass()`, `terkunci()` |
| [`StatusJadwal`](../../app/Enums/StatusJadwal.php) | `jadwal_fasilitasi.status` | `label()` |
| [`StatusDokumen`](../../app/Enums/StatusDokumen.php) | `dokumen_tahapan.status` | `label()` |
| [`StatusVerifikasiDokumen`](../../app/Enums/StatusVerifikasiDokumen.php) | `permohonan_dokumen.status_verifikasi` | `label()` |
| [`StatusFormatDokumen`](../../app/Enums/StatusFormatDokumen.php) | `format_dokumen.status` (draft/aktif/arsip, Fase 6) | `label()`, `badgeClass()` |

Aturan pemakaian:

- **Bandingkan dengan kasus enum**, bukan string: `$permohonan->status_akhir === StatusPermohonan::Revisi`. Perbandingan `=== 'revisi'` selalu `false`. Di Blade tulis lengkap: `\App\Enums\StatusPermohonan::Revisi`.
- `in_array` dengan kasus enum dan `true`, atau pakai method enum (`bisaUbahDokumen()`, `terkunci()`).
- Aman tanpa perubahan: query builder (`where('status_akhir', 'proses')`), assignment string (`$model->status = 'selesai'`, divalidasi oleh cast), echo Blade `{{ $model->status_akhir }}`, dan `Collection::where/firstWhere`.
- Perlu `->value`: `match`, indeks array (`$label[$x->status->value]`), fungsi string (`strtoupper`, `ucfirst`), penggabungan string, array yang dibandingkan kemudian (mis. data lama untuk log), dan `Collection::whereIn` (tidak memahami enum).
- `Builder::value('status')` juga mengembalikan enum.
- Belum di-cast (sisa): `dokumen_verifikasi_detail.status`, `laporan_verifikasi.status_kelengkapan`, `surat_pemberitahuan.status`, `undangan_pelaksanaan.status`. Status master `opd`/`master_urusan` memakai konstanta `STATUS_AKTIF`.

---

## `View/`

| Kelas | Fungsi |
|---|---|
| [`Components/GuestLayout`](../../app/View/Components/GuestLayout.php) | Komponen `<x-guest-layout>` (bawaan Breeze) untuk halaman konfirmasi kata sandi dan verifikasi email. Halaman lain memakai `@extends('layouts.app')`. |
| [`Composers/NavbarComposer`](../../app/View/Composers/NavbarComposer.php) | Menyediakan `$unreadNotifications` untuk `layouts.partials.navbar`. |
