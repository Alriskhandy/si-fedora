# Rekomendasi Pengembangan — SI-FEDORA

> 📁 **ARSIP — snapshot 17–23 Oktober 2026**, kecuali §8 (Keputusan Stakeholder) yang tetap diperbarui.
>
> **Status pelaksanaan per 24 Oktober 2026:** Track 1 (1.1–1.6) dan Track 2 item 2.1, 2.2, 2.3, 2.5, 2.9 **selesai di kode** (Fase 0–2), belum dirilis. Item 2.4 (CI) ditunda. Detail per item dan penyimpangannya ada di [Plan Implementasi](../implementasi/README.md). Temuan A1–A4, B1–B3, dan C1–C3 sudah ditangani; lihat "Catatan Pelaksanaan" tiap fase. C4 (tanggal dokumen = waktu generate) tidak lagi menjadi bagian PRD #5 setelah keputusan §8 no. 6.
>
> **Fase 3 per 25 Oktober 2026:** kode PRD #6, #5, #2, #3 selesai (belum dirilis); PRD #1 ditunda sampai fitur PRD selesai.
>
> **Keputusan §8 no. 5–8 dijawab 24 Oktober 2026.** Dampaknya ke tiap fitur dipetakan di [Fase 3 — Pemetaan Fitur Terdampak](../implementasi/FASE-3-EKSEKUSI-PRD.md#pemetaan-fitur-terdampak). Baris §5 (Kesiapan terhadap PRD) untuk PRD #2, #5, #6 sudah diselaraskan.

> Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota — Bappeda Provinsi Maluku Utara
> Snapshot per **17 Oktober 2026**, branch `upgrade/laravel-12` (commit `f175b6a`). **Revisi 23 Oktober 2026**: disinkronkan dengan [`PRD.md`](../produk/PRD.md) dan jawaban stakeholder (§8).
> Dokumen ini adalah **lanjutan** dari [`ANALISIS-KONDISI-EKSISTING.md`](ANALISIS-KONDISI-EKSISTING.md) (21 Sep 2026). Analisis tersebut dicek ulang terhadap kode terkini pasca-upgrade Laravel 12, lalu dikoreksi dan dilengkapi temuan baru. Hasilnya disusun menjadi rekomendasi pengembangan yang bisa dieksekusi.
> Metodologi tetap mengikuti [`role-skill/ANALYST_SYSTEM.md`](../../role-skill/ANALYST_SYSTEM.md): evidence-based, membedakan *Fact / Finding / Risk / Recommendation*, dan menandai hal tanpa evidence sebagai **[UNKNOWN]**.

---

## 1. Ringkasan Eksekutif

SI-FEDORA sudah **berfungsi secara bisnis**: 7 tahapan fasilitasi/evaluasi berjalan end-to-end, framework sudah naik ke **Laravel 12** (PHP 8.3, Carbon 3), dan 10 kab/kota sudah terdaftar.

Pengembangan berikutnya sudah ditetapkan di [`PRD.md`](../produk/PRD.md): pembaruan UI/UX, role baru **OPD**, fitur lihat masukan untuk Kaban, pengaturan tanggal hasil oleh Admin, dan manajemen permission untuk Admin & Superadmin. Format baru hasil fasilitasi **ditunda** sampai formatnya tersedia.

Sebelum PRD dikerjakan, ada **empat masalah fondasi** yang harus diselesaikan karena menyangkut kerahasiaan dokumen pemerintah, keselamatan data, dan langsung menjadi prasyarat fitur PRD:

1. **Dokumen resmi dapat diakses publik tanpa login.** Seluruh upload (±307 MB) disimpan di disk `public` dan nama filenya mudah ditebak (`time()_namaAsli.pdf`). Role OPD yang akan "mengunduh dokumen fasilitasi" akan memperluas paparan ini bila tidak diperbaiki dulu.
2. **Test otomatis tidak terisolasi dari database.** Menjalankan `php artisan test` akan mengosongkan database yang sedang dipakai lewat `migrate:fresh`. Ini **sudah terjadi** pada database lokal saat analisis ini disusun. Kalau `.env` di server mana pun menunjuk ke database bernilai, risiko yang sama berlaku di sana.
3. **Konten rich text dirender tanpa sanitasi.** `HtmlSanitizer` sudah terdaftar tetapi tidak dipakai di jalur aktif, sehingga ada risiko stored XSS. Fitur "input masukan" OPD dan "lihat masukan" Kaban menambah jalur input dan render baru.
4. **Permission tidak pernah ditegakkan (temuan baru C1–C2).** 42 permission sudah di-seed, tetapi semua akses diatur dengan pengecekan **nama role** (`role:` middleware dan `hasRole()`). Akibatnya fitur PRD "manajemen permission" tidak akan berdampak apa pun, dan menambah role OPD berarti menyunting puluhan titik kode. Selain itu Admin PERAN saat ini bisa menaikkan pengguna menjadi superadmin.

Setelah fondasi aman, arah pengembangan adalah:

- **Eksekusi PRD** di atas fondasi otorisasi berbasis permission (§5).
- **Konsolidasi** ringan: Fonnte sebagai satu-satunya provider WA, provider lain dihapus. Perbaikan menyeluruh fitur notifikasi dijadwalkan terpisah (§8).
- **Penyempurnaan workflow dan laporan pimpinan** sebagai backlog setelah PRD.

---

## 2. Pembaruan Status Temuan Sebelumnya

Status setiap temuan dari analisis 21 Sep 2026 setelah dicek ulang terhadap kode saat ini:

| # | Temuan (analisis 21 Sep) | Status 17 Okt | Evidence terkini |
|---|---|---|---|
| 1 | Tidak ada rate limiting OTP | ❌ **Dikoreksi, sudah ada sejak awal.** Analisis lama hanya mengecek `web.php` | [`routes/auth.php:69-80`](../../routes/auth.php): `throttle:5,1` untuk kirim/verifikasi, `throttle:3,1` untuk resend |
| 2 | Aplikasi Laravel 11 | ✅ **Selesai.** Sudah Laravel 12, Carbon 3.14 | commit `8f7dec3` |
| 3 | 3 service WhatsApp paralel (Fonnte, Twilio, WAHA) | ⚠️ Masih berlaku. **Diputuskan**: Fonnte dipertahankan, lainnya dihapus (§8) | `app/Services/{FonteService,WhatsAppService,WahaService}.php`; Twilio masih di-bind di `AppServiceProvider::register()` |
| 4 | Notifikasi legacy "backup" berjalan berdampingan | ⚠️ Masih berlaku (5 lokasi) | `ApprovalController:417,520`, `TindakLanjutController:201`, `PenetapanPerdaController:170`, `HasilFasilitasiController:1557` |
| 5 | Inkonsistensi FK `kab_kota_id` | ⚠️ Masih berlaku (38 referensi) | `grep -rn kab_kota_id app resources` |
| 6 | Kode orphan `Evaluasi` & `koordinator_id` | ⚠️ Masih berlaku | `app/Models/PermohonanDokumen.php:70`, `app/Models/PermohonanTahapan.php:42` |
| 7 | 3 TODO eksplisit | ⚠️ Masih berlaku | `PerpanjanganWaktuController:141`, `PermohonanDokumenController:346`, `LaporanVerifikasiController:285` |
| 8 | Controller gemuk | ⚠️ Masih berlaku | `HasilFasilitasiController` 1.579 baris |
| 9 | Policy hanya 1 class | ⚠️ Masih berlaku | `app/Policies/PerpanjanganWaktuPolicy.php` |
| 10 | `.env.example` masih sqlite, README menyebut Tailwind & Laravel 11 | ⚠️ Masih berlaku | `.env.example:24`, `README.md` tabel teknologi |
| 11 | Tidak ada backup/DR, CI/CD, APM | ⚠️ Masih berlaku | tidak ada `.github/`, tidak ada paket backup di `composer.json` |
| 12 | Test domain bisnis tidak ada | ⚠️ Masih berlaku, **dan memburuk**. Lihat temuan B1–B2 | `tests/` hanya berisi scaffolding Breeze |

---

## 3. Temuan Baru

### A. Keamanan & Kerahasiaan Data

| ID | Fact | Evidence | Finding | Risiko | Severity |
|---|---|---|---|---|---|
| **A1** | Semua dokumen upload disimpan di disk `public` (symlink `public/storage` aktif) dan ditautkan langsung lewat `asset('storage/…')` / `Storage::url()` | 58 pemakaian `'public'` di `app/`; 54 tautan langsung di view, mis. `verifikasi/show.blade.php:128`, `pages/arsip/show.blade.php:142-362` | File bisa diunduh **tanpa autentikasi** oleh siapa pun yang memegang URL. Middleware `auth` dan role tidak berlaku untuk file statis | Bocornya draf rekomendasi, surat resmi, dan dokumen perencanaan sebelum ditetapkan. URL juga bisa tersebar lewat WA atau riwayat browser | **Kritis** |
| **A2** | Nama file dibentuk dari `time() . '_' . getClientOriginalName()` | `PermohonanDokumenController.php:41,87,168`, `TimAssignmentController.php:123,344` | Nama file **dapat ditebak** (timestamp + nama dokumen yang lazim). Nama asli dari klien juga tidak dinormalisasi | Memperparah A1 (enumerasi file). Nama file berisi spasi atau karakter khusus juga berpotensi error | **Tinggi** |
| **A3** | `HtmlSanitizer` di-register sebagai singleton, tetapi **hanya dipanggil** di `HasilFasilitasiDocumentServiceBackup` (file tak terpakai) | `app/Providers/HtmlSanitizerServiceProvider.php`; render mentah di `pages/hasil-fasilitasi/create.blade.php:487,593,717,822` dan `pdf.blade.php:278` | Catatan rich text (TinyMCE) disimpan apa adanya, lalu dirender dengan `{!! !!}` | **Stored XSS**: catatan berisi skrip akan tereksekusi di browser Kaban/Admin yang membukanya | **Tinggi** |
| **A4** | `ArsipController::show()` dan `listByJenis()` tidak memfilter berdasarkan role atau kab/kota pengguna, sementara route-nya terbuka untuk semua user terautentikasi | `app/Http/Controllers/ArsipController.php:29-73`, `routes/web.php` grup `arsip` | Pemohon kab/kota A dapat membuka seluruh arsip kab/kota B, termasuk draf dan dokumen tahapan. **Dikonfirmasi melanggar kebijakan**: kab/kota hanya boleh melihat arsip miliknya (§8) | Pelanggaran prinsip *need-to-know* | **Tinggi** |
| **A5** | Batas ukuran upload bervariasi 2 MB–100 MB dengan kombinasi MIME berbeda di tiap modul | 10 variasi aturan `mimes:…|max:…` di controller | Tidak ada standar. Upload 100 MB tanpa antrian berpotensi membebani PHP worker | Inkonsistensi UX, permukaan serangan tidak seragam | Sedang |

### B. Kualitas & Keselamatan Rekayasa

| ID | Fact | Evidence | Finding | Risiko | Severity |
|---|---|---|---|---|---|
| **B1** | Override DB untuk testing di-comment, tidak ada `.env.testing`, dan 8 file test memakai `RefreshDatabase` | `phpunit.xml:25-26` | `php artisan test` menjalankan `migrate:fresh` pada **database `.env`** (lokal: PostgreSQL `sifedora`) | **Kehilangan data total.** Sudah terjadi pada DB lokal 17 Okt 2026 (semua tabel kosong). Bila dijalankan di server, data produksi bisa hilang | **Kritis** |
| **B2** | 10 dari 25 test gagal | `php artisan test` | Test register/reset password gagal karena fiturnya sengaja dimatikan, tapi test-nya tidak dihapus. Test login gagal karena bug B3 | Test suite tidak bisa dipercaya sebagai jaring pengaman, dan tidak bisa dipasang di CI | Tinggi |
| **B3** | Route `GET /login` di `web.php` (LoginController) **ditimpa** oleh `routes/auth.php`, yang dimuat belakangan. Handler pengganti memanggil `view('/')` | `php artisan route:list --path=login`, `Auth/AuthenticatedSessionController.php:19` | Membuka `/login` langsung menghasilkan **HTTP 500** ("View [.] not found"). Login lewat `/` tetap jalan karena route home merender `auth.login` | Pengguna yang mem-bookmark `/login` melihat error. Ada 2 controller login yang membingungkan | Sedang |
| **B4** | Semua notifikasi WhatsApp (Fonnte) dikirim **sinkron** di dalam request HTTP. Hanya alur surat pemberitahuan yang memakai Job | `PermohonanNotificationService` (1.335 baris) memanggil `FonteService` langsung; `ShouldQueue` hanya di 3 file | Satu aksi (mis. kirim undangan ke banyak penerima) menunggu N panggilan API eksternal | Halaman lambat atau timeout saat Fonnte lambat. Kegagalan tidak di-retry | Sedang |
| **B5** | Carbon 3 (bawaan Laravel 12) mengubah `diffInDays()` menjadi float dan bertanda | `RemindPermohonanDeadline.php:49`, `PenetapanJadwalFasilitasi.php:54` | Perhitungan saat ini kebetulan aman karena memakai `Carbon::today()` dan kolom date. Tetapi **tidak ada test** yang mengunci perilaku ini | Regresi senyap bila sumber tanggal berubah ke datetime | Rendah |
| **B6** | `composer.json` mensyaratkan `php: ^8.2`, padahal runtime dan pedoman proyek memakai PHP 8.3 | `composer.json`, `php -v` = 8.3.29 | Constraint tidak mencerminkan target runtime | Perbedaan environment dev dan server | Rendah |

### C. Kesiapan Fondasi terhadap PRD *(temuan revisi 23 Okt)*

| ID | Fact | Evidence | Finding | Risiko | Severity |
|---|---|---|---|---|---|
| **C1** | 42 permission di-seed dan dipetakan ke 7 role, tetapi **tidak ada** route yang memakai middleware `permission:` atau `can:`. Akses diatur lewat nama role: 31 grup `role:` di route, 74 `hasRole()`/`hasAnyRole()` di `app/`, dan 93 di view. Satu-satunya pemakaian `authorize('evaluasi.*')` menunjuk permission yang tidak ada dan method-nya tidak di-route | `database/seeders/RolePermissionSeeder.php`, `routes/web.php`, `KabupatenKotaController.php:32-76` | Permission hanya data, **tidak berpengaruh ke akses**. Mengubah permission lewat UI tidak mengubah apa pun | PRD #6 (manajemen permission) tidak berfungsi bila dibangun di atas kondisi ini. PRD #2 (role OPD) memaksa penyuntingan ±200 titik pengecekan role | **Tinggi** (blocker PRD) |
| **C2** | Dropdown role di form pengguna menyembunyikan `superadmin`, tetapi validasinya hanya `exists:roles,name` | `UserController.php:97,112,151,164,181` | Admin PERAN dapat mengirim `role=superadmin` lewat request langsung dan **menaikkan hak akses** dirinya atau pengguna lain | Eskalasi hak akses. Risiko membesar saat Admin diberi akses manajemen permission (PRD #6) | **Tinggi** |
| **C3** | `RolePermissionSeeder` memakai `Role::create()` / `Permission::create()` dan hanya dijalankan saat instalasi awal | `RolePermissionSeeder.php:79-87` | Seeder **tidak idempoten**: menjalankannya ulang di server yang sudah berisi data akan gagal karena duplikat | Role OPD dan permission baru tidak bisa ditambahkan dengan aman ke server berjalan | Sedang |
| **C4** | Tanggal pada dokumen hasil fasilitasi diambil dari waktu generate (`new \DateTime()`); tidak ada kolom tanggal hasil yang tersimpan | `HasilFasilitasiDocumentService.php:86,562-566`, `pdf.blade.php:173` (`$tanggalGenerate`) | Tanggal dokumen berubah setiap kali dokumen di-generate ulang dan tidak dapat diatur | PRD #5 membutuhkan kolom tanggal tersimpan. Dokumen yang di-generate ulang berbeda tanggal dengan versi yang sudah ditandatangani | Sedang |

---

## 4. Rekomendasi Perbaikan

Rekomendasi dikelompokkan menjadi lima jalur kerja (*track*). Prioritas ditentukan dari **Impact × Urgency × Risk**, dengan memperhitungkan **Effort** dan **Dependency**. **Track 1 dan item bertanda ★ di Track 2 adalah gerbang wajib sebelum PRD dikerjakan.**

### Track 1 — Keamanan & Integritas Data *(P0, kerjakan sebelum PRD)*

| # | Rekomendasi | Pendekatan teknis | Menutup | Effort |
|---|---|---|---|---|
| 1.1 | **Isolasi database test** | Aktifkan kembali `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` di `phpunit.xml`, **atau** gunakan database PostgreSQL terpisah (`sifedora_testing`) agar perilaku SQL identik. Tambahkan guard di `tests/TestCase.php` yang menggagalkan test bila nama DB bukan DB testing | B1 | Sangat rendah (≤1 jam) |
| 1.2 | **Pindahkan dokumen ke storage privat** | Simpan upload baru ke disk `local` (`storage/app/private`, sudah `serve: true` di Laravel 12). Sajikan file lewat route unduh yang memanggil Policy, atau lewat `Storage::temporaryUrl()` berumur pendek. Migrasikan 307 MB file lama memakai command Artisan sekali jalan, perbarui kolom path, lalu hapus `asset('storage/…')` dari view. Foto profil boleh tetap publik. Route unduh ini menjadi satu-satunya jalur unduh untuk role OPD (PRD #2) | A1 | Sedang |
| 1.3 | **Nama file acak** | Ganti `time()_namaAsli` dengan `store()` / `hashName()`. Simpan nama asli di kolom terpisah untuk ditampilkan dan di-*download as* | A2 | Rendah |
| 1.4 | **Sanitasi rich text** | Panggil `HtmlSanitizer::sanitize()` saat **menyimpan** catatan (mutator/cast atau di service), dan render hanya konten yang sudah disanitasi. Selaraskan whitelist dengan `valid_elements` TinyMCE agar output Word/PDF tidak berubah. Pola yang sama wajib dipakai untuk input masukan OPD (PRD #2–#3) | A3 | Rendah–Sedang |
| 1.5 | **Otorisasi object-level** | Buat Policy untuk `Permohonan` (view/update) yang menegakkan aturan: pemohon **hanya kab/kota sendiri** (keputusan §8 no. 1); verifikator/fasilitator hanya yang di-assign (sekaligus menuntaskan TODO `PermohonanDokumenController:346`); admin/kaban/auditor semua; OPD sesuai keputusan §8 no. 5. Terapkan di `ArsipController`, `PermohonanController`, dan route unduh dari 1.2 | A4, TODO | Sedang |
| 1.6 | **Tutup celah eskalasi role** | Validasi role di `UserController::store()`/`update()` dengan `Rule::in()` berisi daftar role yang boleh diberikan oleh pengguna yang sedang login (bukan sekadar `exists:roles,name`). Tambahkan test: Admin PERAN mencoba memberi `superadmin` → ditolak | C2 | Sangat rendah |

### Track 2 — Fondasi Rekayasa *(P1)*

| # | Rekomendasi | Pendekatan teknis | Effort |
|---|---|---|---|
| 2.1 ★ | **Pulihkan test suite menjadi hijau** | Hapus test untuk fitur yang sengaja dimatikan (register, reset password). Perbaiki B3 dengan menghapus route login duplikat dan memilih satu controller. Pastikan `php artisan test` lulus 100% | Rendah |
| 2.2 ★ | **Test untuk alur bisnis kritis** | Prioritas: (a) transisi status & tahapan permohonan, (b) `getEffectiveDeadline()` + perpanjangan waktu, (c) approval Kaban (setuju/tolak), (d) otorisasi per role dari 1.5–1.6 dan 2.3, (e) reminder H-3/H-1. Gunakan factory dan `Http::fake()` untuk Fonnte. Butir (d) wajib ada sebelum 2.3 dan PRD #2/#6 | Sedang–Tinggi |
| 2.3 ★ | **Otorisasi berbasis permission** | Lengkapi daftar permission agar mencakup seluruh aksi yang kini dijaga `role:`. Ganti middleware `role:` → `can:`/`permission:` per grup route, dan `hasRole()` → `can()`/`@can` di controller & view, secara bertahap per modul dengan test 2.2(d). Superadmin cukup lewat `Gate::before`. Ubah `RolePermissionSeeder` menjadi idempoten (`firstOrCreate` + `syncPermissions`) agar bisa dijalankan ulang saat deploy (menutup C1, C3) | Sedang–Tinggi |
| 2.4 | **CI minimal** | GitHub Actions: `composer install` → `vendor/bin/pint --test` → `php artisan test` pada service PostgreSQL. Wajibkan PR review sebelum merge ke `main` | Rendah |
| 2.5 ★ | **Hapus provider WA selain Fonnte** | Sesuai keputusan §8 no. 4: migrasikan alur surat pemberitahuan dari Twilio ke `FonteService`, lalu hapus `WhatsAppService`, `WahaService`, `WhatsAppChannel`, binding Twilio, blok "legacy backup" di 5 lokasi, dan dependensi `twilio/sdk`. Perbaikan menyeluruh notifikasi (queue, retry, template) **tidak** dikerjakan di sini (lihat 2.10) | Rendah–Sedang |
| 2.6 | **Eksekusi Fase 1 `redesign-database.md`** | Hapus class `Evaluasi`, relasi `koordinator()`, dan field fiktif `TimFasilitasiAssignment`. Rename `kab_kota_id` → `kabupaten_kota_id` (38 referensi). Dikerjakan **setelah** 2.2 agar regresi terdeteksi | Sedang |
| 2.7 | **PHP backed Enum untuk status** | `app/Enums/StatusPermohonan`, `StatusDokumen`, dst., di-cast di model. Tanpa perubahan DB (Fase 2 item 4 di `redesign-database.md`) | Sedang |
| 2.8 | **Ekstraksi controller gemuk** | Mulai dari `HasilFasilitasiController` (1.579 baris) → Action/Service per use case, mengikuti pola `HasilFasilitasiDocumentService`. Validasi dipindah ke Form Request, dengan aturan upload standar bersama (menutup A5). Sebaiknya dilakukan bersamaan dengan pengerjaan format baru hasil fasilitasi agar tidak dikerjakan dua kali | Tinggi, bertahap |
| 2.9 ★ | **Housekeeping** | Hapus `HasilFasilitasiDocumentServiceBackup`, layout orphan (`dashboard..php`, `navBoostrap.blade.php`), `welcome.blade.php`, `index.html`, route `showWithTabs` bila tidak dipakai. Perbarui README & `.env.example` (pgsql, `FONNTE_TOKEN`, Bootstrap/Sneat, Laravel 12). Naikkan constraint `php` ke `^8.3`. Dikerjakan sebelum redesign UI agar tidak ada view mati yang ikut didesain ulang | Rendah |
| 2.10 | **Perbaikan fitur notifikasi** *(ditunda, §8 no. 4)* | Bungkus pengiriman Fonnte dalam Job (`ShouldQueue`, `tries`, `backoff`), jalankan `queue:work` dengan supervisor, dan pantau `failed_jobs`. Dijadwalkan bersama perbaikan notifikasi yang direncanakan stakeholder | Sedang |

### Track 3 — Penyempurnaan Fungsional *(P2, backlog setelah PRD)*

Fokus track ini adalah **menyempurnakan alur yang sudah ada**, bukan menambah modul baru. Item di sini tidak termasuk PRD dan dikerjakan setelah PRD selesai, kecuali 3.1.

| # | Rekomendasi | Nilai bagi pengguna | Effort |
|---|---|---|---|
| 3.1 | **Tuntaskan 3 TODO** | Notifikasi ke Admin PERAN saat pemohon mengajukan perpanjangan waktu (lewat Fonnte), PDF laporan verifikasi, dan cek assignment (sudah tercakup 1.5) | Rendah |
| 3.2 | **SLA per tahapan + eskalasi** | Tambah kolom durasi SLA di `master_tahapan`. Scheduler harian menandai permohonan yang melewati SLA, lalu mengirim reminder ke penanggung jawab dan eskalasi ke Admin PERAN/Kaban (rencana `desain.md` N2 & "Escalation jika lewat deadline"). Saat ini reminder hanya untuk pemohon **sebelum** mengajukan. Bergantung pada 2.10 | Sedang |
| 3.3 | **Status perpanjangan waktu di progress tracker** | Tampilkan badge "menunggu perpanjangan waktu" di tracker utama `Permohonan::getProgressSteps()`. Bisa digabung dengan redesign UI (PRD #1) | Rendah |
| 3.4 | **Modul Pengaturan Sistem (N) versi ringan** | Mulai dari yang paling dibutuhkan: SLA & jadwal reminder (N2), template pesan WA (N1), dan unggah template surat Word (N3). Simpan di tabel `settings` + cache. Hindari "restore database dari UI" (N4) karena berisiko | Sedang |
| 3.5 | **Laporan periodik & KPI (M)** | Laporan bulanan/tahunan per kab/kota & jenis dokumen: jumlah permohonan, rata-rata durasi per tahap (dari timestamp `permohonan_tahapan`), tingkat revisi, ketepatan waktu tindak lanjut. Ekspor Excel memakai `phpoffice/phpspreadsheet` yang sudah terpasang | Sedang |
| 3.6 | **Kalender & timeline (Q1/Q2)** | Kalender jadwal fasilitasi/pelaksanaan lintas kab/kota, plus timeline permohonan dari `activity_log` (sudah terekam via Spatie) | Sedang |
| 3.7 | **Pencarian global (P1)** | Satu kotak pencarian untuk permohonan/kab-kota/tahun/jenis dokumen, dengan hasil yang dibatasi Policy 1.5 | Rendah–Sedang |
| 3.8 | **Format baru hasil fasilitasi** *(ditunda)* | Menunggu format baru dari stakeholder (PRD #4, §8 no. 2). Usulan sebelumnya (tabel terstruktur FORM 1–8 sesuai `FORMAT_RKPD_2026.md`) dievaluasi ulang setelah format tersedia | Tinggi |
| 3.9 | **Versi dokumen (Q3)** | Simpan riwayat revisi dokumen yang diunggah ulang (tabel `dokumen_revisi` sudah ada, tinggal dimanfaatkan konsisten di semua modul upload) agar verifikator bisa membandingkan versi | Sedang |

### Track 4 — Ekstensi *(P3)*

Integrasi dengan BSrE (e-sign) dan SIPD Kemendagri **tidak dilanjutkan** karena tidak ada kerja sama (§8 no. 3).

| # | Rekomendasi | Prasyarat | Effort |
|---|---|---|---|
| 4.1 | **PWA ringan** (O1/O2): manifest + web push untuk notifikasi & quick-approve Kaban | 2.10 (queue) | Sedang |

### Track 5 — Operasional & Keberlanjutan *(P1–P2)*

| # | Rekomendasi | Effort |
|---|---|---|
| 5.1 | **Runbook** deploy, rollback, rotasi token Fonnte, cara menjalankan scheduler, dan cara menjalankan seeder permission idempoten (2.3) saat deploy. Menjadi syarat pengurangan bus factor | Rendah |
| 5.2 | **Uptime monitoring** pada endpoint `/up` + alert ke WA/email admin | Sangat rendah |
| 5.3 | **Error tracking** (mis. Sentry atau channel log `slack`/email untuk level `error`). Audit dulu 629 baris `ERROR` di log lama untuk menemukan bug berulang | Rendah |
| 5.4 | **Asesmen kepatuhan** SPBE dan UU PDP 27/2022 (data no. HP di `otp_codes`, kebijakan retensi `activity_log`). Masuknya akun OPD menambah subjek data pribadi | Butuh pihak Diskominfo |

---

## 5. Kesiapan terhadap PRD

Crosscheck setiap butir [`PRD.md`](../produk/PRD.md) terhadap kondisi kode dan perbaikan yang harus selesai lebih dulu.

| # | Butir PRD | Kondisi saat ini | Perbaikan prasyarat | Catatan implementasi |
|---|---|---|---|---|
| 1 | Pembaruan tampilan / UI-UX | Template Bootstrap/Sneat; 54 tautan `asset('storage/…')` dan 93 pengecekan role tersebar di view; ada layout orphan | 1.2, 1.4, 2.3, 2.9 | Kerjakan setelah tautan file dan pengecekan role di view diganti, supaya setiap view cukup disentuh sekali. Gabungkan 3.3 |
| 2 | Role & akun **OPD**: input masukan pada hasil fasilitasi, lihat proses permohonan, unduh dokumen fasilitasi | Belum ada role OPD. Masukan saat ini hanya dari Tim Fasilitasi (`catatan_masukan` di `hasil_fasilitasi_urusan`) | 1.2, 1.4, 1.5, 1.6, 2.2(d), 2.3 | Tambah role `opd` + permission lewat seeder idempoten. **Keputusan §8 no. 5:** OPD lingkungan Provinsi didaftarkan Admin sebagai master data (dengan urusan bidangnya) + `users.opd_id`; tanpa penugasan. **Keputusan teknis 24 Okt:** OPD termasuk tim fasilitator — hak akses sama dengan fasilitator, input dibatasi pada urusan pemerintahan bidangnya; masukan disimpan di `hasil_fasilitasi_urusan` (struktur fasilitator), bukan tabel terpisah, dan ikut tercetak di dokumen hasil. Masukan urusan OPD maupun fasilitator terkunci selama hasil diajukan ke/disetujui Kaban. Unduh hanya lewat route 1.2 |
| 3 | Fitur **lihat masukan** pada akun Kaban | Belum ada. Catatan Kaban saat ini hanya `hasil_fasilitasi.keterangan_kaban` (masukan **dari** Kaban saat approve/reject, bukan **untuk** Kaban). *Koreksi 24 Okt: versi sebelumnya menyebut `PermohonanDokumen.feedback_kaban`; kolom itu ternyata milik kelas mati `Evaluasi` dan tidak ada di database.* | 1.4, butir PRD #2 | Karena masukan OPD menjadi bagian masukan urusan hasil fasilitasi (keputusan teknis 24 Okt), Kaban melihatnya lewat halaman approval/hasil; yang ditambahkan adalah penanda sumber (nama OPD), filter masukan OPD, dan badge jumlah. Render konten tersanitasi |
| 4 | Hasil fasilitasi format baru | — | — | **Ditunda** sampai format tersedia (3.8). Rencanakan bersama 2.8 |
| 5 | Admin mengatur **tanggal hasil** | Tahap selesai ditandai lewat `permohonan_tahapan.updated_at` (bergeser setiap baris diubah); tidak ada riwayat perubahan tanggal | 2.2 | **Keputusan §8 no. 6:** "tanggal hasil" = tanggal selesai setiap tahapan yang bisa disesuaikan Admin, beserta riwayatnya. Kolom `permohonan_tahapan.tanggal_selesai` + tabel riwayat. Temuan C4 (tanggal dokumen = waktu generate) tidak lagi bagian butir ini |
| 6 | **Manajemen permission** untuk Admin & Superadmin | UI role/permission sudah ada, **khusus superadmin**, tetapi permission tidak ditegakkan (C1). Admin bisa eskalasi role (C2) | 1.6, 2.2(d), **2.3** | Tanpa 2.3 fitur ini tidak berdampak. **Keputusan §8 no. 7:** Admin mengelola semua role selain `superadmin` dan tidak bisa menambah role baru (hanya Superadmin). Matikan pembuatan/penghapusan permission bebas lewat UI; UI cukup mengatur pemetaan role ↔ permission. Semua perubahan dicatat di `activity_log` |

---

## 6. Roadmap Bertahap

| Fase | Indikatif | Isi | Kriteria selesai |
|---|---|---|---|
| **0. Pengamanan** | Minggu 1–2 | 1.1, 1.3, 1.4, 1.6, 2.1 | Test hijau di DB terisolasi; konten rich text tersanitasi; eskalasi role tertutup |
| **1. Tutup celah akses** | Minggu 3–5 | 1.2, 1.5, 2.5, 3.1, 5.1, 5.2 | Tidak ada lagi URL `/storage/` untuk dokumen; akses arsip sesuai role & kab/kota; hanya Fonnte yang tersisa; runbook tersedia |
| **2. Fondasi PRD** | Minggu 6–9 | 2.2, 2.3, 2.4, 2.9 | Seluruh route dijaga permission, bukan nama role; seeder permission idempoten; CI wajib lulus sebelum merge; tidak ada kode orphan |
| **3. Eksekusi PRD** | Minggu 10–16 | PRD #6 → #5 → #2 → #3 → #1 | Butir PRD diuji per role (test 2.2(d) diperluas untuk OPD); redesign UI dikerjakan terakhir di atas view yang sudah bersih |
| **4. Konsistensi data** | Setelah PRD | 2.6, 2.7 | Fase 1 `redesign-database.md` selesai tanpa regresi (dibuktikan test) |
| **5. Backlog** | Menyesuaikan | 2.8 + 3.8 (saat format baru tersedia), 2.10, 3.2, 3.4–3.7, 3.9, 4.1, 5.3, 5.4 | Bergantung keputusan stakeholder |

Urutan ini disengaja. Track 1 menutup risiko yang dampaknya tidak bisa dibatalkan (kebocoran dan kehilangan data). Otorisasi berbasis permission (2.3) diselesaikan **sebelum** PRD karena PRD #2 dan #6 bergantung padanya. Test (2.2) disiapkan **sebelum** 2.3, refactor skema (2.6), dan ekstraksi controller (2.8), supaya perubahan besar punya jaring pengaman. Di dalam Fase 3, manajemen permission (#6) dikerjakan lebih dulu agar role OPD (#2) langsung dibuat lewat mekanisme yang benar.

---

## 7. Indikator Keberhasilan

| Area | Indikator | Target awal |
|---|---|---|
| Keamanan | Dokumen yang dapat diakses tanpa login | 0 |
| Keamanan | Route yang dijaga nama role (`role:`), bukan permission | 0 (dari 31 grup) |
| Kualitas | Test lulus di CI; cakupan alur kritis (2.2 a–e) | 100% lulus; 5/5 alur tercakup |
| Kesiapan PRD | Butir PRD yang prasyaratnya (§5) terpenuhi sebelum dikerjakan | 5/5 (di luar butir yang ditunda) |
| Proses bisnis | Rata-rata durasi permohonan → surat rekomendasi | Baseline diukur setelah PRD, lalu target turun bertahap |
| Keberlanjutan | Developer yang mampu deploy mandiri | ≥ 2 orang |

---

## 8. Keputusan Stakeholder

### Sudah diputuskan

| # | Pertanyaan | Keputusan | Dampak ke rekomendasi |
|---|---|---|---|
| 1 | Apakah kab/kota boleh melihat arsip kab/kota lain? (A4) | Kab/kota **hanya** dapat melihat arsip miliknya | A4 dikonfirmasi sebagai celah; aturan Policy 1.5 |
| 2 | Format hasil fasilitasi (3.8) | Format baru akan diberikan kemudian | 3.8 dan PRD #4 ditunda |
| 3 | E-sign BSrE & SIPD Kemendagri | Tidak ada kerja sama | Rencana integrasi dihapus dari Track 4 |
| 4 | Provider WhatsApp & dependensi `twilio/sdk` | Fonnte sebagai provider sementara, provider lain dihapus. Provider dan fitur notifikasi akan diperbaiki lagi nantinya | 2.5 (penghapusan, termasuk `twilio/sdk`); 2.10 ditunda |
| 5 | OPD mana yang mendapat akun, cakupan permohonan yang terlihat, bagian yang diberi masukan, batas ubah masukan | OPD **provinsi / mitra / teknis** yang **ditentukan Admin** untuk berpartisipasi memberi masukan terhadap dokumen perencanaan **di tahap hasil fasilitasi** (24 Okt 2026). *Klarifikasi 25 Okt 2026:* semua OPD adalah OPD di lingkungan Provinsi | PRD #2–#3: master OPD dengan urusan bidangnya + akun OPD; tanpa penugasan, OPD melihat semua permohonan; masukan hanya pada urusan bidangnya sampai hasil diajukan ke Kaban |
| 6 | Arti "tanggal hasil" dan boleh tidaknya diubah setelah disetujui Kaban | Perubahan **tanggal selesai** dan **riwayatnya** pada setiap tahapan permohonan, untuk penyesuaian manual oleh Admin (24 Okt 2026) | PRD #5 didesain ulang: kolom tanggal selesai per tahapan + tabel riwayat; tidak ada kunci setelah approval Kaban |
| 7 | Batas wewenang Admin PERAN dalam manajemen permission | Admin **tidak dapat menambah role baru** (hanya Superadmin) dan **hanya dapat mengelola role selain superadmin** (24 Okt 2026) | PRD #6: `RolePolicy` diselaraskan. Role `superadmin`/`kaban`/`admin_peran` hanya dapat diberikan atau diubah Superadmin (konfirmasi 24 Okt) |
| T1 | (Keputusan teknis pemilik proyek) Posisi role OPD terhadap tim fasilitasi | OPD / pengguna ber-role `opd` **termasuk tim fasilitator**, dengan pembatasan input masukan hanya pada bagian urusan pemerintahan; selebihnya sama seperti role fasilitator (24 Okt 2026) | PRD #2–#3: masukan OPD memakai `hasil_fasilitasi_urusan`; tabel `masukan_opd` dibatalkan. *Konfirmasi:* masukan OPD **ikut tercetak** di dokumen hasil fasilitasi seperti masukan fasilitator; kunci ubah masukan urusan saat hasil diajukan ke/disetujui Kaban **berlaku juga untuk fasilitator** (terbuka lagi bila ditolak). Detail di [Fase 3](../implementasi/FASE-3-EKSEKUSI-PRD.md#keputusan-teknis-opd-termasuk-tim-fasilitator--24-oktober-2026) |
| 8 | Strategi backup & uji restore | **Ditunda**, dikembangkan nanti (24 Okt 2026) | Tetap di backlog Fase 5; backup manual sesuai runbook sebelum rilis |
| 9 | Format hasil fasilitasi dinamis (lanjutan no. 2) | Format masukan & dokumen hasil diatur lewat data berversi per jenis dokumen; keputusan D1–D7 di [analisis](ANALISIS-MASUKAN-DINAMIS.md#9-keputusan-yang-perlu-dikonfirmasi) dikonfirmasi sesuai usulan (27 Okt 2026) | 3.8 + 2.8 dipindah ke [Fase 6](../implementasi/FASE-6-FORMAT-HASIL-DINAMIS.md). Tahap 1 (template teks) tidak menunggu format baru; Tahap 2 tetap menunggu (no. 2) |

### Masih terbuka

Keputusan stakeholder utama (no. 1–8) sudah terjawab. Pertanyaan turunan untuk implementasi Fase 3 (K1–K10: riwayat & aturan tanggal selesai, kunci bagian hasil fasilitasi, cakupan unduhan OPD, mockup UI) dicatat di [Fase 3 — Keputusan Terbuka Sebelum Lanjut](../implementasi/FASE-3-EKSEKUSI-PRD.md#keputusan-terbuka-sebelum-lanjut).

Konfirmasi turunan 24 Okt 2026 (masukan OPD hanya pada urusan pemerintahan bidangnya sampai diajukan ke Kaban, tanpa penugasan OPD, role `superadmin`/`kaban`/`admin_peran` hanya diberikan/diubah Superadmin) sudah diterapkan di [Fase 3](../implementasi/FASE-3-EKSEKUSI-PRD.md#keputusan-stakeholder-yang-berlaku).

Kebutuhan baru di luar dokumen ini — **penghapusan penugasan tim** — dicatat beserta pertanyaan terbukanya di [Fase 5](../implementasi/FASE-5-BACKLOG.md#penghapusan-penugasan-tim-kebutuhan-baru-24-okt-2026).

---

*Seluruh evidence diambil dari kode, konfigurasi, dan riwayat git per 17 Oktober 2026; temuan C1–C4 per 23 Oktober 2026. Sebelum mengeksekusi setiap item, validasi ulang nomor baris yang dirujuk, karena kode dapat berubah.*
