# Referensi View — SI-FEDORA

Struktur `resources/views`, sistem layout, dan aturan menulis view. Folder view **mengikuti folder controller dan file route**, sehingga satu fitur ditemukan dengan nama area yang sama di tiga tempat:

| Area | Controller | Route | View |
|---|---|---|---|
| Umum | `app/Http/Controllers/Umum` | `routes/web/umum.php` | `resources/views/pages/umum` |
| Admin | `app/Http/Controllers/Admin` | `routes/web/admin.php` | `resources/views/pages/admin` |
| Master data | `app/Http/Controllers/MasterData` | `routes/web/master-data.php` | `resources/views/pages/master-data` |
| Jadwal | `app/Http/Controllers/Jadwal` | `routes/web/jadwal.php` | `resources/views/pages/jadwal` |
| Fasilitasi | `app/Http/Controllers/Fasilitasi` | `routes/web/fasilitasi.php` | `resources/views/pages/fasilitasi` |
| Autentikasi | `app/Http/Controllers/Auth` | `routes/auth.php` | `resources/views/auth` |

---

## Struktur

```
resources/views/
├── layouts/
│   ├── app.blade.php            layout utama (sidebar + navbar + konten)
│   ├── guest.blade.php          layout tamu untuk <x-guest-layout> (bawaan Breeze)
│   └── partials/                sidebar, navbar, footer (di-include oleh layouts/app)
├── components/                  komponen Blade bawaan Breeze, hanya dipakai halaman auth
├── auth/                        login, verifikasi WhatsApp/OTP, verifikasi email, konfirmasi kata sandi
├── errors/                      halaman error (konvensi Laravel: nama file = kode HTTP)
└── pages/
    ├── umum/         dashboard/ (satu file per role), arsip/, notifikasi/, profile/
    ├── admin/        users/, roles/, permissions/, tim-assignment/, activity-log/, modul/
    ├── master-data/  kabupaten-kota/, jenis-dokumen/, tahapan/, kelengkapan/, bab/, urusan/, opd/, format-dokumen/
    ├── jadwal/       jadwal-fasilitasi/, penetapan-jadwal/, undangan-pelaksanaan/, surat-pemberitahuan/
    └── fasilitasi/   permohonan/ (+ tahapan/), laporan-verifikasi/, hasil-fasilitasi/, approval/,
                      surat-penyampaian-hasil/, perpanjangan-waktu/
```

### Pemetaan halaman tahapan permohonan

`pages/fasilitasi/permohonan/show.blade.php` adalah halaman detail dengan progress tracker. Halaman per tahapan ada di `pages/fasilitasi/permohonan/tahapan/`:

| Tahapan | File | Method controller |
|---|---|---|
| 1 Permohonan | `tahapan/permohonan.blade.php` | `PermohonanController::tahapanPermohonan` |
| 2 Verifikasi | `tahapan/verifikasi.blade.php` | `PermohonanController::tahapanVerifikasi` |
| 3 Penetapan jadwal & undangan | `tahapan/jadwal.blade.php` | `PermohonanController::tahapanJadwal` |
| 4 Pelaksanaan | `tahapan/pelaksanaan.blade.php` | `PermohonanController::tahapanPelaksanaan` |
| 5 Hasil fasilitasi | `tahapan/hasil.blade.php` | `PermohonanController::tahapanHasil` |
| 6 Tindak lanjut | `tahapan/tindak-lanjut.blade.php` | `PermohonanController::tahapanTindakLanjut` |
| 7 Penetapan PERDA | `tahapan/penetapan.blade.php` | `PermohonanController::tahapanPenetapan` |

### Partial fitur baru (Fase 3)

| Partial | Isi |
|---|---|
| `pages/fasilitasi/permohonan/partials/tanggal-selesai.blade.php` | Panel tanggal selesai tahapan, modal ubah (Admin), dan riwayat perubahan tanggal (PRD #5) |
| `pages/fasilitasi/hasil-fasilitasi/partials/pengirim.blade.php` | Nama pengirim masukan + asal OPD (dipakai di input, detail hasil, approval) |
| `pages/fasilitasi/hasil-fasilitasi/partials/input-*.blade.php` | Bagian halaman input (`create`) dan detail (`show`): `input-ringkasan` (info, batas waktu, status, tombol draft), `input-dokumen` (alur 3 langkah draft → draft final → Kaban, untuk Admin/Koordinator), `input-filter` (saring Semua/Saya/Dari OPD + bab/urusan), `input-oleh`, `input-aksi` (menu Ubah/Hapus), `input-kosong`, `input-tombol-form`, `input-script` (khusus input: TinyMCE dimuat saat dibuka, draf otomatis di sessionStorage, muat ulang + sorot item) |
| `pages/fasilitasi/hasil-fasilitasi/partials/daftar-*.blade.php`, `form-masukan`, `atribut-masukan`, `gaya`, `preview-pdf` | Dipakai bersama `create` dan `show`, dibangun dari **bagian format** (Fase 6): `daftar-masukan` (tabel; kolom Bab/Sub Bab untuk bagian berujukan bab) → `daftar-urusan` (dikelompokkan per urusan) dengan opsi `$denganAksi`, `$denganSaya`, `$denganOpd`; `form-masukan` (form tambah; field rujukan mengikuti bagian); `atribut-masukan` (atribut data baris untuk saringan & modal ubah); `daftar-script` (tab diingat via `#tab-…`, saring & urutkan, konfirmasi `.js-konfirmasi`, preview PDF); `gaya` (CSS bersama); `preview-pdf` (modal) |
| `pages/fasilitasi/approval/partials/masukan-urusan.blade.php` | Panel masukan urusan di halaman approval Kaban, dengan filter "Hanya OPD" (PRD #3) |

### Template PDF

Template PDF (DomPDF) berada di folder fiturnya dengan nama `pdf.blade.php` dan **tidak** memakai `layouts.app`:

- `pages/fasilitasi/hasil-fasilitasi/dokumen-pdf.blade.php`: dokumen hasil fasilitasi (generik; isi dari struktur `DokumenHasilRenderer`, sama dengan Word — Fase 6)
- `pages/fasilitasi/laporan-verifikasi/pdf.blade.php`: laporan verifikasi

---

## Layout

Halaman aplikasi memakai `@extends('layouts.app')` dengan slot berikut:

| Slot | Cara mengisi | Isi |
|---|---|---|
| Judul tab | `@section('title', 'Judul Halaman')` | Judul di `<title>` |
| Konten | `@section('main') … @endsection` | Isi halaman |
| CSS tambahan | `@push('styles') … @endpush` | `<link>`/`<style>` khusus halaman |
| JS tambahan | `@push('scripts') … @endpush` | `<script>` khusus halaman, dimuat setelah jQuery & template |

> **Gunakan `@push`, bukan `@section`, untuk `styles` dan `scripts`.** Layout memakai `@stack`, sehingga `@section('scripts')` tidak pernah dirender, dan CSS/JS halaman diam-diam tidak termuat. Lima form (surat pemberitahuan, role, pengguna) sempat terkena masalah ini dan sudah diperbaiki 24 Okt 2026.

Partial di `layouts/partials/`:

| Partial | Catatan |
|---|---|
| `sidebar.blade.php` | Menu per permission (`@can`/`@canany`). Menu baru ditampilkan dengan permission yang sama dengan route tujuannya. |
| `navbar.blade.php` | Menerima `$unreadNotifications` dari `NavbarComposer` (didaftarkan di `AppServiceProvider` untuk view `layouts.partials.navbar`). |
| `footer.blade.php` | — |

---

## Aturan Menulis View

1. **Lokasi:** `pages/<area>/<fitur>/<aksi>.blade.php`. Nama view di controller: `view('pages.<area>.<fitur>.<aksi>')`. Nama aksi mengikuti method resource: `index`, `create`, `show`, `edit`.
2. **Partial khusus fitur** disimpan di subfolder `partials/` di dalam folder fitur, misalnya `pages/umum/profile/partials/`.
3. **Hak akses di view:** tombol dan menu memakai `@can('<permission>')`, bukan `hasRole()`. Pengecekan role di view hanya untuk tata letak (mis. menyembunyikan kolom kab/kota bagi pemohon).
4. **Tautan dokumen:** `route('berkas.show', ['<jenis>', $record->id])`. Jangan memakai `asset('storage/…')` atau `Storage::url()`. Untuk preview, sertakan `data-file-ext` karena URL berkas tidak berekstensi.
5. **Escaping:** gunakan `{{ }}`. `{!! !!}` hanya untuk HTML yang sudah disanitasi (kolom dengan cast `SanitizedHtml`) atau `{!! nl2br(e($teks)) !!}`.
6. **Tautan antarhalaman:** `route('nama.route')`, bukan URL tertulis.
7. **Rujukan view** (`view()`, `@extends`, `@include`, `loadView()`) selalu memakai nama lengkap bertitik dari `resources/views`.

### Checklist halaman baru

1. Buat file di folder area & fitur yang sesuai.
2. `@extends('layouts.app')`, isi `title` dan `main`; CSS/JS lewat `@push`.
3. Bila perlu menu, tambahkan di `layouts/partials/sidebar.blade.php` dengan `@can` permission route tujuannya.
4. Buka halaman sebagai role yang berhak dan yang tidak berhak. Untuk halaman dengan data, tambahkan test feature yang memanggil route-nya (`assertOk()` + `assertSee()`).

---

## Memeriksa Rujukan View

Tidak ada nama view yang dibangun dinamis di aplikasi ini; semua rujukan berupa string literal. Untuk memastikan tidak ada view yang hilang setelah memindahkan atau mengganti nama file:

```bash
php artisan view:cache && php artisan view:clear   # semua view bisa dikompilasi
grep -rnoE "(view|loadView|@extends|@include)\('[a-z0-9_.-]+'" app resources/views   # daftar rujukan
```

Per 24 Oktober 2026 semua rujukan view menunjuk file yang ada (184 rujukan, 0 rusak). Tiga rujukan rusak yang sebelumnya tercatat sudah diperbaiki:

| Dulu | Perbaikan |
|---|---|
| `MasterBabController::create/edit` merender view yang tidak ada (500) | Method dan route dihapus; tambah/ubah bab memakai form di halaman daftar (`store`/`update`). |
| `TimAssignmentController::edit` jalur non-AJAX merender view yang tidak ada | Jalur non-AJAX diarahkan ke `tim-assignment.index`; jalur AJAX (JSON untuk modal) tidak berubah. |
| `ValidasiHasilController::generatePdf` merujuk `hasil-fasilitasi.pdf` | Seluruh `ValidasiHasilController`, route `validasi-hasil.*`, dan permission `validasi-hasil.manage` dihapus (tidak dipakai view mana pun). |
