# Fase 2 — Fondasi PRD

> **Status: ✅ kode selesai 24 Okt 2026 — belum dirilis.** CI (langkah B) ditunda ke Fase 5.
> Hasil: seluruh akses berbasis permission (katalog `config/permissions.php`, `permission:sync`); test snapshot akses route; test alur bisnis; celah submit/hapus permohonan milik akun lain ditutup; kode mati dibersihkan.
> Bagian langkah di bawah adalah **rencana awal**. Penyimpangan (mis. tanpa `Gate::before`) ada di **[Catatan Pelaksanaan](#catatan-pelaksanaan-24-okt-2026)** — itu yang berlaku. Cara kerja hasil akhirnya dijelaskan di [Panduan Pengembang §2](../pengembang/PANDUAN-PENGEMBANG.md#2-otorisasi).

**Indikatif:** Minggu 6–9 · **Item:** 2.2, 2.3, 2.4, 2.9 · **Menutup:** C1, C3, temuan §2 no. 10–12

## Tujuan

- Akses ditentukan oleh **permission**, bukan nama role, sehingga PRD #6 (manajemen permission) benar-benar berdampak dan PRD #2 (role OPD) cukup dibuat lewat data.
- Ada jaring pengaman test dan CI sebelum perubahan fitur.
- Kode mati dibersihkan sebelum redesign UI.

## Prasyarat

- [ ] Fase 1 selesai (Policy `Permohonan` dan `BerkasController` sudah ada)

## Urutan Pengerjaan

`2.2 (test karakterisasi) → 2.4 (CI) → 2.3 (konversi per modul) → 2.9`. Test (2.2) wajib selesai sebelum 2.3, karena 2.3 harus **mempertahankan perilaku akses yang ada persis sama**.

---

### A. Test alur bisnis kritis *(item 2.2)*

**File:** `tests/Feature/…` (baru), `database/factories/*` (lengkapi)

Factory yang sudah ada: `User`, `KabupatenKota`, `Permohonan`, `PermohonanDokumen`. Tambahkan factory yang dibutuhkan (mis. `JadwalFasilitasi`, `HasilFasilitasi`, `PerpanjanganWaktu`) beserta state per status. Untuk role, buat helper `User::factory()->withRole('pemohon')`.

| Kelompok | Test class | Cakupan minimal |
|---|---|---|
| (a) Status & tahapan | `PermohonanWorkflowTest` | submit → verifikasi → revisi/diterima; transisi tahapan pada `permohonan_tahapan` |
| (b) Deadline | `PerpanjanganWaktuTest` | `getEffectiveDeadline()` dengan dan tanpa perpanjangan disetujui; batas Carbon 3 (B5) |
| (c) Approval Kaban | `ApprovalKabanTest` | setuju/tolak draft final; status & `tanggal_disetujui_kaban` tercatat |
| (d) Otorisasi | `RouteAuthorizationTest` | **matriks role × grup route**: untuk tiap 31 grup `role:` di `routes/web.php`, role yang diizinkan → bukan 403, role lain → 403. Ditambah test policy dari Fase 0–1 |
| (e) Reminder | `RemindPermohonanDeadlineTest` | command reminder H-3/H-1 mengirim ke penerima yang benar (`Http::fake()` Fonnte) |

Test (d) adalah **test karakterisasi**: test ini merekam perilaku akses sebelum 2.3 dan harus tetap hijau setelah 2.3 tanpa diubah.

---

### B. CI minimal *(item 2.4)* — **DITUNDA** (dipindah ke Fase 5)

**File:** `.github/workflows/ci.yml` (baru)

1. Trigger: `pull_request` dan `push` ke `main`.
2. Job: PHP 8.3 + service PostgreSQL (`sifedora_testing`) → `composer install` → `vendor/bin/pint --test` → `php artisan test`.
3. Atur branch protection `main` di GitHub: wajib CI hijau dan 1 review sebelum merge.

---

### C. Otorisasi berbasis permission *(item 2.3, menutup C1 & C3)*

**File:** `database/seeders/RolePermissionSeeder.php`, katalog permission baru, `app/Providers/AppServiceProvider.php`, `routes/web.php`, controller & view yang memakai `hasRole()`/`hasAnyRole()` (74 di `app/`, 93 di view), Policy dari Fase 0–1

#### Langkah 1 — Matriks akses
Susun tabel di PR (dan simpan sebagai lampiran di dokumen ini): setiap grup route `role:` dan setiap pengecekan `hasRole()` → aksi bisnis → nama permission baru → role yang memilikinya. Pakai permission yang sudah ada bila sesuai (`permohonan.verify`, `approval.approve`, …). Tambahkan yang belum ada, misalnya `penetapan-jadwal.manage`, `tim-assignment.manage`, `perpanjangan-waktu.approve`, `tindak-lanjut.submit`, `activity-log.view`, `modul.manage`, `role.manage`, `permission.manage`.

Untuk aturan yang membedakan "semua" dan "milik sendiri" (Policy `Permohonan`), pakai pasangan permission, misalnya `permohonan.view-all` dan `permohonan.view-own`, lalu Policy memeriksa permission tersebut ditambah kepemilikan.

#### Langkah 2 — Katalog permission di kode
Satu sumber kebenaran untuk nama permission dan pemetaan default role → permission, misalnya `config/permissions.php` atau backed enum `App\Enums\Permission`. Nama permission tidak lagi dibuat bebas lewat UI (lihat PRD #6 di Fase 3).

#### Langkah 3 — Seeder idempoten
Tulis ulang `RolePermissionSeeder` agar membaca katalog dan memakai `Permission::firstOrCreate()` serta `Role::firstOrCreate()`. Permission yang tidak ada di katalog dihapus.

Ada dua mode:
- **instalasi baru**: `syncPermissions()` sesuai default katalog;
- **server berjalan**: hanya **menambahkan** permission baru ke role default, tanpa mencabut perubahan yang nanti dibuat Admin lewat UI (PRD #6).

Akhiri dengan `app(PermissionRegistrar::class)->forgetCachedPermissions()`.

#### Langkah 4 — Superadmin
Tambahkan `Gate::before(fn ($user) => $user->hasRole('superadmin') ? true : null)` di `AppServiceProvider::boot()`. Superadmin tidak perlu memegang daftar permission.

#### Langkah 5 — Konversi bertahap per modul
Satu PR per modul. Urutan disarankan: master data & pengguna → jadwal & penetapan jadwal → permohonan & verifikasi → hasil fasilitasi & approval → surat → perpanjangan waktu, tindak lanjut, penetapan perda → arsip, modul, activity log.

Per modul:
- route `middleware('role:…')` → `middleware('can:<permission>')`,
- `hasRole()`/`hasAnyRole()` di controller → `$user->can()` / `authorize()`,
- `@if(auth()->user()->hasRole(…))` di view dan sidebar → `@can`,
- Policy yang memakai role → permission.

Setelah tiap PR, test (d) harus tetap hijau **tanpa diubah**.

#### Langkah 6 — Bersihkan
- Hapus method mati `KabupatenKotaController::evaluasi/storeEvaluasi/updateEvaluasi/destroyEvaluasi` (tidak di-route, memakai permission yang tidak ada) beserta view `master-kab-kota/evaluasi.blade.php`.
- Hapus `app/Http/Middleware/CheckRole.php` bila tidak terdaftar.
- Hapus helper role di `app/Models/User.php` (baris ±80–102, termasuk helper yang mengecek role `kabkota` yang tidak pernah dibuat) bila sudah tidak dipakai.

**Deploy:** `php artisan db:seed --class=RolePermissionSeeder --force` lalu `php artisan permission:cache-reset`. Catat di runbook.

**Kriteria:** `grep -rn "role:" routes/web.php` → 0; `hasRole`/`hasAnyRole` hanya tersisa di `Gate::before` dan tempat yang tercatat alasannya; test (d) hijau.

---

### D. Housekeeping *(item 2.9)*

1. Hapus: `app/Services/HasilFasilitasiDocumentServiceBackup.php`, `resources/views/layouts/dashboard..php`, `resources/views/layouts/navBoostrap.blade.php`, `welcome.blade.php`, `public/index.html` (bila ada), dan route `showWithTabs` (setelah dipastikan tidak dirujuk lewat grep).
2. `README.md`: stack sebenarnya (Laravel 12, PHP 8.3, PostgreSQL, Bootstrap/Sneat), cara setup, cara menjalankan test, tautan ke runbook.
3. `.env.example`: `DB_CONNECTION=pgsql`, `FONNTE_TOKEN`, hapus variabel yang tidak dipakai.
4. `composer.json`: constraint `"php": "^8.3"`.

---

## Catatan Pelaksanaan (24 Okt 2026)

- **A — Test**
  - `RouteAuthorizationTest` merekam, untuk 236 route × 8 profil (7 role + tanpa role), apakah middleware route menolak (403). Snapshot disimpan di `tests/Fixtures/route-authorization.json`.
  - Route model binding dimatikan dan action diganti respons kosong, sehingga test berjalan ±5 detik. Pendekatan pertama yang menjalankan controller sungguhan memakan >5 menit dan kehabisan memori.
  - Snapshot dibuat **sebelum** konversi dan tetap identik setelahnya. Uji mutasi (memberi `user.manage` ke auditor) terbukti terdeteksi.
  - Perubahan hak akses yang disengaja: `UPDATE_ROUTE_SNAPSHOT=1 php artisan test --filter=RouteAuthorizationTest`, lalu review diff JSON-nya.
  - Test alur bisnis: `PermohonanControllerTest` (a), `PermohonanDeadlineTest` (b), `ApprovalControllerTest` (c), `RemindPermohonanDeadlineTest` (e), dan policy dari Fase 0–1 (d).
- **Celah yang ditemukan dan ditutup:** `PermohonanController::submit()` dan `destroy()` tidak memeriksa kepemilikan. Pemohon mana pun bisa mengajukan atau menghapus permohonan akun lain lewat ID di URL. Kini ditutup oleh `PermohonanPolicy::update()`.
- **B — CI: ditunda** atas keputusan pemilik proyek; workflow yang sempat dibuat dihapus. Catatan untuk nanti: 68 file lama belum terformat Pint, jadi pengecekan Pint di CI sebaiknya memakai `--diff` terhadap branch target. Selama CI belum ada, jalankan `php artisan test` dan `vendor/bin/pint --dirty` secara manual sebelum merge.
- **C — Permission**
  - Katalog ada di `config/permissions.php` (42 permission per aksi bisnis). Command `permission:sync` dengan `--reset` untuk instalasi/rilis pertama; tanpa `--reset` hanya menambah. `RolePermissionSeeder` memanggil `permission:sync --reset`.
  - **Tanpa `Gate::before` untuk superadmin** (menyimpang dari rencana). Dengan `Gate::before`, superadmin akan lolos ke route khusus pemohon dan perilakunya berubah. Superadmin mendapat permission secara eksplisit.
  - Seluruh 31 grup `role:` → `can:`. Grup master data dipecah per resource. Policy (`Permohonan`, `User`, `PerpanjanganWaktu`) dan scope `visibleTo` dikonversi.
  - **Sengaja tetap berbasis role** (identitas, bukan otorisasi):
    - pemilihan penerima notifikasi (`User::role(...)` di service/controller/command);
    - percabangan dashboard per role (`DashboardController`);
    - validasi bahwa user target memang verifikator/fasilitator (`AdminPeranController`, `TimAssignmentController`, `UndanganPelaksanaanController`);
    - modul yang ditujukan ke role tertentu;
    - pengecekan tata letak di view (kolom kab/kota untuk pemohon, pesan saat data kosong, info pembuat jadwal, blok "Proses Fasilitasi" yang disembunyikan dari superadmin).
  - **Satu perubahan tampilan yang disengaja:** menu "Perpanjangan Waktu" di bagian admin kini tampil juga untuk superadmin (route-nya memang sudah terbuka untuknya). Selain itu, tautan sidebar per role identik dengan versi lama (diverifikasi dengan membandingkan render sidebar lama vs baru).
  - Dihapus: `CheckRole` (tidak terdaftar), helper role di `User` (tidak dipakai, termasuk role `kabkota` yang tidak ada), method `evaluasi*` di `KabupatenKotaController` beserta view-nya, dan menu `pokja` di sidebar (role dan route-nya tidak ada).
  - **Temuan:** `AdminPeranController::assign()` untuk jalur Pokja mensyaratkan role `pokja` dan model `TimPokja`. Role itu tidak ada, jadi jalur ini tidak pernah berhasil. *Pembaruan 24 Okt:* `AdminPeranController` beserta view, route, dan permission `permohonan.assign` dihapus. Halamannya rusak total (query ke kolom `status`, `verifikator_id`, `pokja_id` yang tidak ada), tidak ditautkan dari menu mana pun, dan fungsinya sudah digantikan `TimAssignmentController`.
- **D — Housekeeping**
  - Dihapus: `HasilFasilitasiDocumentServiceBackup`, `layouts/dashboard..php`, `layouts/navBoostrap.blade.php`, `welcome.blade.php`, `resources/views/index.html`, `partials/sidebar-backup.blade.php`, dan route `permohonan.show-tabs` beserta method `showWithTabs`.
  - README, `.env.example` (pgsql), dan `composer.json` (`php: ^8.3`) diperbarui.
- **Insiden saat pengerjaan:** `php artisan db:seed --env=testing` tanpa `.env.testing` berjalan di DB lokal `sifedora`. Akibatnya permission lama di DB lokal diganti katalog (data pengguna dan permohonan tidak tersentuh). Ini setara dengan langkah rilis Fase 2. Pelajaran: jangan memakai `--env=testing` untuk command yang menulis DB; uji lewat PHPUnit.

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Konversi permission membuka atau menutup akses secara tidak sengaja | Test karakterisasi (d) wajib hijau tanpa diubah; satu PR per modul; uji manual per role di staging |
| Cache permission Spatie tidak ter-reset saat deploy | `permission:cache-reset` masuk runbook dan skrip deploy |
| Menu sidebar tidak sinkron dengan route | Sidebar ikut dikonversi ke `@can` di PR modul yang sama |
| Seeder mencabut permission yang sudah diubah lewat UI | Mode "server berjalan" hanya menambah, tidak mencabut |

## Kriteria Selesai Fase 2

- [ ] Test (a)–(e) tersedia dan hijau
- [ ] ~~CI berjalan di setiap PR; `main` terproteksi~~ (ditunda ke Fase 5)
- [ ] Seluruh route dijaga permission; tidak ada grup `role:` tersisa
- [ ] `RolePermissionSeeder` aman dijalankan ulang di server berjalan
- [ ] Kode mati dihapus; README dan `.env.example` akurat
- [ ] Rilis ke server; seeder dan cache reset dijalankan sesuai runbook
