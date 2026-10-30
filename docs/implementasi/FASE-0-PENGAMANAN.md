# Fase 0 — Pengamanan

> **Status: ✅ kode selesai 24 Okt 2026 — belum dirilis.**
> Hasil: test terisolasi di `sifedora_testing`; eskalasi role ditutup (`UserPolicy`, Form Request); upload memakai nama acak; rich text disanitasi (`SanitizedHtml`); `/login` diperbaiki.
> Bagian "Urutan Pengerjaan" di bawah adalah **rencana awal**. Penyimpangan (mis. guard di `createApplication()`, tanpa `FileUploader`) ada di **[Catatan Pelaksanaan](#catatan-pelaksanaan-24-okt-2026)** — itu yang berlaku.

**Indikatif:** Minggu 1–2 · **Item:** 1.1, 1.6, 1.3, 1.4, 2.1 · **Menutup:** B1, B2, B3, C2, A2, A3

## Tujuan

Menghentikan risiko yang dampaknya tidak bisa dibatalkan, dengan perubahan kecil yang tidak mengubah alur bisnis:

- test yang mengosongkan database,
- eskalasi hak akses oleh Admin PERAN,
- nama file yang bisa ditebak,
- XSS lewat rich text.

## Prasyarat

- [ ] Backup manual database produksi (`pg_dump`) dan arsip folder `storage/app` disimpan di luar server
- [ ] Database PostgreSQL `sifedora_testing` dibuat di mesin developer (dan nanti di CI)

## Urutan Pengerjaan

`0.1 → 0.2 → 0.5 → 0.3 → 0.4`. Item 0.1 wajib paling awal, karena item lain menambah test.

---

### 0.1 Isolasi database test *(item 1.1, menutup B1)*

**File:** `phpunit.xml`, `tests/TestCase.php`, `.env.testing` (baru, tidak di-commit bila berisi kredensial)

1. Di `phpunit.xml`, tetapkan `DB_CONNECTION=pgsql` dan `DB_DATABASE=sifedora_testing`. PostgreSQL dipilih, bukan sqlite, agar perilaku SQL (enum, `ilike`, JSON) sama dengan produksi. Hapus baris sqlite yang di-comment.
2. Di `tests/TestCase.php`, override hook `beforeRefreshingDatabase()` yang dipanggil `RefreshDatabase` sebelum `migrate:fresh`. Bila `config('database.connections.pgsql.database')` bukan `sifedora_testing`, lempar exception dan hentikan test.
3. Tambahkan `.env.testing.example` berisi variabel DB testing, lalu dokumentasikan di README.

**Test/verifikasi:**
- Ubah `DB_DATABASE` sementara ke `sifedora` → test langsung gagal oleh guard, dan DB `sifedora` tidak tersentuh.
- `php artisan test` berjalan di `sifedora_testing`.

---

### 0.2 Tutup celah eskalasi role *(item 1.6, menutup C2)*

**File:** `app/Http/Controllers/UserController.php`, Form Request baru `StoreUserRequest` / `UpdateUserRequest`

Fakta di kode:
- `store()`/`update()` memvalidasi role hanya dengan `exists:roles,name`.
- `edit()`, `update()`, `destroy()`, `resetPassword()` tidak menolak target yang ber-role `superadmin`. Jadi Admin PERAN bisa mengubah, menghapus, atau mereset password superadmin lewat URL langsung, walaupun daftar pengguna menyembunyikannya.

Langkah:
1. Buat method `User::assignableRoles(): array` yang mengembalikan role yang boleh diberikan oleh pengguna login. Superadmin boleh semua role. Admin PERAN boleh semua role **kecuali** `superadmin`, `kaban`, dan `admin_peran`, sesuai daftar yang sudah dipakai di form `create()`. Untuk form `edit()`, samakan daftarnya.
2. Pindahkan validasi ke Form Request dan pakai `Rule::in($user->assignableRoles())`.
3. Tambahkan guard: pengguna non-superadmin tidak boleh `edit`/`update`/`destroy`/`resetPassword` akun ber-role `superadmin` (403). Untuk sementara pakai Policy `UserPolicy` berbasis role; di Fase 2 policy ini dikonversi ke permission.
4. Catat perubahan role pengguna di `activity_log`.

**Test (feature):**
- Admin PERAN mengirim `role=superadmin` saat create/update → 422.
- Admin PERAN membuka edit / reset password akun superadmin → 403.
- Superadmin tetap bisa memberi semua role.

---

### 0.5 Pulihkan test suite menjadi hijau *(item 2.1, menutup B2, B3)*

**File:** `tests/Feature/Auth/*`, `routes/web.php`, `routes/auth.php`, `app/Http/Controllers/Auth/*`

1. Petakan alur login aktif. Saat ini `GET /login` didefinisikan dua kali (`web.php:63` → `LoginController`, `auth.php:21` → `AuthenticatedSessionController`). Yang menang adalah `auth.php`, dan handler-nya memanggil `view('/')` sehingga menghasilkan HTTP 500.
2. Pilih **satu** controller login. Pertahankan yang menjalankan alur OTP/verifikasi telepon yang dipakai produksi; periksa `LoginController::login()` dan `LoginRequest`. Hapus route dan controller lainnya.
3. Hapus test untuk fitur yang sengaja dimatikan: `RegistrationTest`, `PasswordResetTest`, dan `EmailVerificationTest` bila verifikasi email memang tidak dipakai. Hapus juga route dan controller-nya bila sudah tidak di-route.
4. Sesuaikan `AuthenticationTest` dengan alur login yang dipilih (termasuk redirect ke verifikasi telepon bila berlaku).

> Catatan: `resources/views/auth/login.blade.php` sedang diubah di working tree. Selesaikan atau commit perubahan itu sebelum mengerjakan item ini.

**Kriteria:** `php artisan test` lulus 100%, dan `GET /login` mengembalikan 200 atau redirect yang benar.

---

### 0.3 Nama file acak *(item 1.3, menutup A2)*

**File:** controller yang menyimpan upload (±19 titik `store`/`storeAs`/`move`; daftar final diambil ulang dengan grep saat eksekusi, foto profil di `ProfileController` ikut diseragamkan): `PermohonanDokumenController`, `UndanganPelaksanaanController`, `SuratPemberitahuanController`, `PerpanjanganWaktuController`, `JadwalFasilitasiController`, `HasilFasilitasiController`, `TindakLanjutController`, `SuratPenyampaianHasilController`, `PenetapanPerdaController`, `PelaksanaanFasilitasiController`, `ModulController`, `TimAssignmentController`

1. Buat satu helper/service `App\Support\FileUploader` dengan method `store(UploadedFile $file, string $folder, string $disk): array`. Method ini mengembalikan `['path' => …, 'original_name' => …, 'size' => …, 'mime' => …]` dan memakai `$file->store($folder, $disk)` (nama acak dari `hashName()`).
2. Ganti semua pola `time() . '_' . getClientOriginalName()` dengan helper ini. Untuk fase ini **disk belum diubah** (tetap `public`); pemindahan disk dikerjakan di Fase 1 (1.2).
3. Simpan nama asli di kolom `file_name` bila tabelnya sudah punya. Untuk tabel yang belum punya, catat dulu di daftar; kolomnya ditambahkan di Fase 1 bersama route unduh.
4. File lama tidak di-rename (tetap valid); hanya upload baru yang memakai nama acak.

**Test:** satu test per controller upload utama memakai `Storage::fake()`: path tersimpan tidak mengandung nama asli, dan `file_name` berisi nama asli.

---

### 0.4 Sanitasi rich text *(item 1.4, menutup A3)*

**File:** `app/Providers/HtmlSanitizerServiceProvider.php`, cast baru `app/Casts/SanitizedHtml.php`, model terkait, 7 view yang memakai `{!! !!}`

View yang merender HTML mentah: `laporan-verifikasi/show`, `pages/fasilitasi/tahapan/verifikasi`, `pages/hasil-fasilitasi/{show,create,pdf}`, `pages/approval/show`, `surat-pemberitahuan/show`.

1. Inventaris kolom rich text yang dirender lewat `{!! !!}` di 7 view tersebut (mis. `hasil_fasilitasi_urusan.catatan_masukan`, catatan sistematika, catatan laporan verifikasi). Catat di PR.
2. Buat cast `SanitizedHtml` (`set()` memanggil `HtmlSanitizer::sanitize()`), lalu terapkan ke kolom-kolom tersebut di `casts()` model. Dengan begitu sanitasi terjadi saat **menyimpan**.
3. Selaraskan whitelist `HtmlSanitizerServiceProvider` dengan `valid_elements` TinyMCE. Uji ekspor Word (`HasilFasilitasiDocumentService`) dan PDF dengan contoh konten yang berisi tabel, list, dan bold.
4. Buat command sekali jalan `php artisan richtext:sanitize --dry-run` untuk menyanitasi data lama. Jalankan di server setelah backup.
5. Biarkan `{!! !!}` di view (konten sudah bersih). Tambahkan komentar Blade singkat bahwa sumbernya sudah disanitasi.

**Test:** menyimpan catatan berisi `<script>`/`onerror=` → yang tersimpan sudah bersih; tag format yang diizinkan (tabel, list) tetap utuh.

---

## Catatan Pelaksanaan (24 Okt 2026)

Penyimpangan dari rencana dan temuan saat eksekusi:

- **0.1** — Guard ditaruh di `TestCase::createApplication()`, bukan `beforeRefreshingDatabase()`. Alasannya, trait `RefreshDatabase` di kelas test menimpa method dengan nama sama di `TestCase`. `.env.testing.example` tidak dibuat karena `phpunit.xml` sudah menetapkan DB testing. Di mesin developer, database `sifedora_testing` (owner `sifedora_user`) dibuat manual karena `sifedora_user` tidak punya hak `CREATEDB`.
- **0.2** — Aturan role mengikuti daftar yang sudah ditampilkan form, sehingga alur sah tidak berubah:
  - create: semua role kecuali `superadmin`, `kaban`, `admin_peran`;
  - edit: semua role kecuali `superadmin`; superadmin tetap boleh memberi `superadmin`.

  Guard target ada di `UserPolicy::manage`. Perubahan role dicatat di `activity_log`. **Sisa risiko**: Admin PERAN masih bisa mengubah role pengguna lain menjadi `kaban`/`admin_peran` lewat form edit (perilaku form sebelumnya). *Pembaruan 24 Okt 2026:* keputusan §8 no. 7 menyatakan Admin mengelola semua role selain superadmin, sehingga perilaku form edit ini sesuai keputusan; yang perlu diselaraskan justru form tambah pengguna (lihat pemetaan di Fase 3).
- **0.5** — Login aktif sebenarnya `AuthenticatedSessionController` (route di `auth.php` menimpa `web.php`). `LoginController` dihapus; pencatatan `activity_log` login/logout dipindah ke controller yang aktif. Pengecekan `is_active` di `LoginController` tidak dibawa karena **kolom `users.is_active` tidak ada**. Fitur nonaktifkan akun belum pernah berfungsi, termasuk field `is_active` di form edit pengguna yang selama ini diabaikan; field itu kini dibuang dari `update()`. Controller, view, dan test register/reset password dihapus. `UserFactory` kini default nomor HP terverifikasi (state `phoneUnverified()` dan `withRole()` ditambahkan).
- **0.3** — Helper `FileUploader` tidak dibuat: sebagian besar titik upload sudah memakai `->store()` (nama acak). Yang diperbaiki hanya 6 titik yang benar-benar menghasilkan nama mudah ditebak: `TimAssignmentController` (2×), `PenetapanPerdaController`, `TindakLanjutController`, `PelaksanaanFasilitasiController`, `HasilFasilitasiController` (draft final). **SK Tim disimpan langsung di `public/sk-tim` (web root, di luar disk `public`)**; lokasinya belum dipindah agar tampilan tidak rusak. File lama di sana wajib ikut dipindah di Fase 1 (1.2).
- **0.4** — Kolom yang disanitasi (cast `App\Casts\SanitizedHtml`): `hasil_fasilitasi_form.catatan`, `hasil_fasilitasi_rekomendasi.catatan`, `hasil_fasilitasi_sistematika.catatan_penyempurnaan`, `hasil_fasilitasi_urusan.catatan_masukan`. Render `{!! nl2br(e(...)) !!}` di view lain sudah aman. Whitelist `HtmlSanitizerServiceProvider` diperketat agar sama dengan `valid_elements` TinyMCE; elemen lain dihapus dengan teks dipertahankan. Ekspor Word tetap kompatibel (`cleanText()` men-decode entitas).
- Factory baru/dilengkapi untuk test: `KabupatenKota`, `MasterJenisDokumen`, `MasterTahapan`, `MasterUrusan`, `JadwalFasilitasi`, `Permohonan`, `HasilFasilitasi`.

**Langkah rilis:**
1. Backup DB dan `storage/app` + `public/sk-tim`.
2. Deploy.
3. `php artisan richtext:sanitize --dry-run`, periksa jumlahnya, lalu `php artisan richtext:sanitize`.

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Sanitasi menghapus format yang dibutuhkan dokumen Word/PDF | Uji dengan konten produksi nyata (salinan DB) sebelum menjalankan command sanitasi |
| Menghapus controller login yang ternyata dipakai | Petakan route aktif dengan `php artisan route:list --path=login` dan uji login manual di staging |
| Guard role memblokir alur admin yang sah | Test mencakup alur Admin PERAN yang normal (buat pemohon, verifikator, fasilitator) |

## Kriteria Selesai Fase 0

- [ ] `php artisan test` hijau di `sifedora_testing`; guard menolak DB lain
- [ ] Admin PERAN tidak bisa memberi atau menyentuh akun superadmin (dibuktikan test)
- [ ] Upload baru memakai nama acak
- [ ] Konten rich text tersanitasi saat disimpan, data lama sudah dibersihkan
- [ ] `/login` tidak lagi menghasilkan 500
- [ ] Rilis ke server, dengan backup sebelum rilis
