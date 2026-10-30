# Referensi Lapisan HTTP — SI-FEDORA

Indeks route, controller, middleware, dan form request: tanggung jawab masing-masing, siapa yang boleh mengaksesnya, dan di mana view-nya.

- **Detail per method** ada di docblock masing-masing file. Dokumen ini hanya indeks.
- **Daftar route lengkap** (URI, method HTTP, middleware) selalu ambil dari kode dengan `php artisan route:list --name=<prefix>`, misalnya `php artisan route:list --name=permohonan`.
- Kolom "Tanggung jawab", "Akses", dan "View" disalin dari docblock kelas. Bila docblock kelas berubah, perbarui barisnya di sini.

Kolom **Akses** memakai nama permission dari [`config/permissions.php`](../../config/permissions.php), misalnya `permohonan.verify`. Mekanismenya dijelaskan di [Panduan Pengembang §2](PANDUAN-PENGEMBANG.md#2-otorisasi).

---

## Struktur Folder

```
app/Http/Controllers/
├── Controller.php   kelas dasar
├── Fasilitasi/  Alur tahapan permohonan (tahapan 1–7) dan perpanjangan waktu
├── Jadwal/      Jadwal, penetapan jadwal, surat pemberitahuan, dan undangan pelaksanaan
├── MasterData/  Data referensi yang dikelola Admin
├── Admin/       Administrasi sistem: pengguna, tim, role & permission, log, modul
├── Umum/        Fitur lintas role: dashboard, arsip, unduh berkas, notifikasi, profil
└── Auth/        Login, logout, verifikasi email & nomor WhatsApp, kata sandi
```

Aturan penempatan controller baru:

- Pilih folder menurut **area bisnis**, bukan menurut role pengguna. Contoh: controller untuk tahapan permohonan masuk `Fasilitasi/`, meskipun yang memakainya Kaban.
- Namespace mengikuti folder (`App\Http\Controllers\Fasilitasi`), lalu `use App\Http\Controllers\Controller;`.
- Buat dengan `php artisan make:controller Fasilitasi/NamaController`.
- Folder baru hanya dibuat bila sebuah area punya lebih dari 3 controller dan tidak cocok dengan folder mana pun. Catat di bagian ini.

## Route

```
routes/
├── web.php             pintu masuk: route `/` + grup auth & verified.phone yang memuat file di web/
├── web/
│   ├── umum.php        dashboard, profil, notifikasi, berkas, arsip          → Controllers/Umum
│   ├── admin.php       role & permission, activity log, pengguna, tim, modul → Controllers/Admin
│   ├── master-data.php kabupaten/kota dan master data                        → Controllers/MasterData
│   ├── jadwal.php      jadwal, penetapan jadwal, undangan, surat pemberitahuan → Controllers/Jadwal
│   └── fasilitasi.php  permohonan & tahapan 1–7, perpanjangan waktu          → Controllers/Fasilitasi
├── auth.php            login, logout, verifikasi email & WhatsApp            → Controllers/Auth
└── console.php         kosong (command berupa kelas; jadwal di bootstrap/app.php)
```

- **Satu file `routes/web/*.php` untuk satu folder controller.** Route baru masuk ke file area controller-nya.
- Semua file di `routes/web/` dimuat **di dalam** grup `auth` + `verified.phone` di `web.php`, jadi tidak perlu menambahkan middleware itu lagi.
- `auth.php` dimuat terpisah oleh `bootstrap/app.php` (setelah `web.php`) dan sengaja tanpa `verified.phone`, agar pengguna bisa memverifikasi nomornya.
- `fasilitasi.php` diurutkan mengikuti alur tahapan 1 → 7, dengan penanda `// Tahapan N — …`.

### Aturan menulis route

1. Hak akses memakai `->middleware('can:<permission>')`. Jangan memakai `role:`.
2. Route statis (`/create`, `/export`) harus didaftarkan **sebelum** route ber-parameter (`/{id}`) dengan prefix yang sama. Jika tidak, URL statis tertangkap sebagai parameter.
3. Beri nama setiap route (`->name()`) dan rujuk di view dengan `route('nama')`, bukan URL tertulis.
4. Gunakan grup `prefix()->name()->controller()` untuk route satu controller; nama route mengikuti pola `<prefix>.<aksi>`.
5. Komentar menjelaskan **siapa yang boleh dan kenapa**, tanpa menyebut nama role (role bisa berubah lewat UI, permission tidak).
6. Setelah menambah, mengubah, atau menghapus route:
   - `php artisan route:list --name=<prefix>` untuk memeriksa hasilnya;
   - `UPDATE_ROUTE_SNAPSHOT=1 php artisan test --filter=RouteAuthorizationTest`, lalu review diff `tests/Fixtures/route-authorization.json`;
   - bila menambah controller, perbarui tabel di bawah.

---

## Controller

### `Fasilitasi/` — Alur tahapan permohonan (tahapan 1–7) dan perpanjangan waktu

| Controller | Tanggung jawab | Akses | Nama route | View |
|---|---|---|---|---|
| [`PermohonanController`](../../app/Http/Controllers/Fasilitasi/PermohonanController.php) | Permohonan fasilitasi: daftar, buat, ajukan, hapus, dan halaman per tahapan. Inti alur bisnis. | lihat: PermohonanPolicy::view + scope visibleTo; buat/ajukan/hapus: permohonan.create + PermohonanPolicy::update; ubah batas waktu: tahapan.update-deadline | `permohonan.*`, `permohonan.tahapan.*` | `pages/fasilitasi/permohonan` |
| [`PermohonanDokumenController`](../../app/Http/Controllers/Fasilitasi/PermohonanDokumenController.php) | Unggah dan hapus file dokumen kelengkapan permohonan oleh pemohon (tahapan 1 dan revisi). | permohonan.upload-dokumen; hanya pemohon pembuat permohonan | `permohonan-dokumen.*` | — |
| [`VerifikasiController`](../../app/Http/Controllers/Fasilitasi/VerifikasiController.php) | Verifikasi kelengkapan dokumen permohonan oleh verifikator (tahapan 2) dan lembar verifikasi (Word). | permohonan.verify | `verifikasi.*` | — |
| [`LaporanVerifikasiController`](../../app/Http/Controllers/Fasilitasi/LaporanVerifikasiController.php) | Laporan hasil verifikasi kelengkapan dokumen oleh Admin PERAN, termasuk unduh PDF. | laporan-verifikasi.manage | `laporan-verifikasi.*` | `pages/fasilitasi/laporan-verifikasi` |
| [`PelaksanaanFasilitasiController`](../../app/Http/Controllers/Fasilitasi/PelaksanaanFasilitasiController.php) | Tahapan pelaksanaan (tahapan 4): unggah, unduh, hapus dokumen pelaksanaan, dan menyelesaikan tahapan. | unggah/hapus: pelaksanaan.upload-dokumen; selesaikan: pelaksanaan.complete; unduh: PermohonanPolicy::view | `pelaksanaan-fasilitasi.*`, `permohonan.dokumen.*` | — |
| [`HasilFasilitasiController`](../../app/Http/Controllers/Fasilitasi/HasilFasilitasiController.php) | Hasil fasilitasi/evaluasi (tahapan 5): input masukan oleh Tim per **bagian format** (Fase 6: `storeMasukan`/`updateMasukan`/`destroyMasukan`, bagian dari format yang terkunci di hasil; aturan validasi dibangun dari rujukan bagian), pembuatan dokumen Word/PDF, unggah draft final, dan pengajuan ke Kepala Badan. Catatan hanya dapat diubah/dihapus pemiliknya atau koordinator tim (fasilitator PIC). Semua aksi tambah/ubah/hapus ditolak (423) selama hasil dinilai/disetujui Kaban (`HasilFasilitasi::isLocked`). Akun OPD (pemegang `input-masukan` yang terikat OPD) hanya mengisi urusan bidang OPD-nya dan dianggap anggota tim semua permohonan (cabang sementara sampai penugasan tim dihapus). Nilai kolom tambahan bagian divalidasi dari definisi bagian (`BagianMasukan::aturanNilai`). Teks catatan disanitasi lewat cast SanitizedHtml. | lihat & unduh: hasil-fasilitasi.view; halaman input & route masukan: hasil-fasilitasi.input-masukan, lalu per bagian `BagianMasukan::bisaDiisiOleh` (role pengisi bagian); ajukan hasil & buat draft: hasil-fasilitasi.kelola-draft; draft final & ajukan ke Kaban: hasil-fasilitasi.submit-kaban | `hasil-fasilitasi.*`, `hasil-fasilitasi.masukan.store` (`/{permohonan}/masukan/{kode}`), `hasil-fasilitasi.masukan.update`/`.destroy` (`/{permohonan}/masukan/{masukan}`) | `pages/fasilitasi/hasil-fasilitasi` |
| [`TanggalSelesaiTahapanController`](../../app/Http/Controllers/Fasilitasi/TanggalSelesaiTahapanController.php) | Penyesuaian tanggal selesai tahapan oleh Admin (PRD #5). Tanggal selesai diisi otomatis oleh sistem saat tahapan selesai; setiap perubahan oleh Admin dicatat di riwayat tahapan dan activity log. Panel "Kelola Tanggal" (ubah langsung di baris) tampil di detail permohonan hanya untuk Admin/Superadmin; riwayat dicatat tetapi tidak ditampilkan. | tahapan.update-tanggal-selesai + PermohonanPolicy::view | `permohonan.tahapan.update-tanggal-selesai` | `pages/fasilitasi/permohonan/partials/tanggal-selesai` |
| [`ApprovalController`](../../app/Http/Controllers/Fasilitasi/ApprovalController.php) | Persetujuan Kepala Badan atas draft final hasil fasilitasi (tahapan 5): review, setujui, atau minta revisi. Halaman review menampilkan masukan urusan beserta asal OPD (filter "Hanya OPD"); daftar menampilkan jumlah masukan OPD (PRD #3). Disetujui: tahapan Hasil Fasilitasi selesai dan tahapan berikutnya dimulai. Ditolak: admin/koordinator mengunggah ulang draft final, dan kunci masukan terbuka lagi. | approval.approve | `approval.*` | `pages/fasilitasi/approval` |
| [`SuratPenyampaianHasilController`](../../app/Http/Controllers/Fasilitasi/SuratPenyampaianHasilController.php) | Surat penyampaian hasil fasilitasi oleh Kepala Badan. | surat-penyampaian-hasil.manage (unduh: semua pengguna login) | `public.*`, `public.surat-penyampaian-hasil.*`, `surat-penyampaian-hasil.*` | `pages/fasilitasi/surat-penyampaian-hasil` |
| [`TindakLanjutController`](../../app/Http/Controllers/Fasilitasi/TindakLanjutController.php) | Unggah dan finalisasi dokumen tindak lanjut hasil fasilitasi oleh pemohon (tahapan 6). | tindak-lanjut.submit; hanya pemohon pembuat permohonan | `tindak-lanjut.*` | — |
| [`PenetapanPerdaController`](../../app/Http/Controllers/Fasilitasi/PenetapanPerdaController.php) | Unggah dan finalisasi dokumen penetapan PERDA/PERKADA oleh pemohon (tahapan 7). | penetapan-perda.submit; hanya pemohon pembuat permohonan | `penetapan-perda.*` | — |
| [`PerpanjanganWaktuController`](../../app/Http/Controllers/Fasilitasi/PerpanjanganWaktuController.php) | Pengajuan dan pemrosesan perpanjangan batas waktu (unggah dokumen atau pengajuan permohonan). | lihat: perpanjangan-waktu.view; ajukan: perpanjangan-waktu.submit; proses: perpanjangan-waktu.process (lihat PerpanjanganWaktuPolicy) | `perpanjangan-waktu.*` | `pages/fasilitasi/perpanjangan-waktu` |

### `Jadwal/` — Jadwal, penetapan jadwal, surat pemberitahuan, dan undangan pelaksanaan

| Controller | Tanggung jawab | Akses | Nama route | View |
|---|---|---|---|---|
| [`JadwalFasilitasiController`](../../app/Http/Controllers/Jadwal/JadwalFasilitasiController.php) | Jadwal fasilitasi per tahun anggaran dan jenis dokumen, termasuk batas pengajuan permohonan dan file undangan. | lihat: semua pengguna login (non-pengelola hanya jadwal published); kelola: jadwal.manage | `jadwal.*` | `pages/jadwal/jadwal-fasilitasi` |
| [`PenetapanJadwalController`](../../app/Http/Controllers/Jadwal/PenetapanJadwalController.php) | Penetapan jadwal pelaksanaan fasilitasi oleh Kepala Badan (tahapan 3). | penetapan-jadwal.manage | `penetapan-jadwal.*` | `pages/jadwal/penetapan-jadwal` |
| [`SuratPemberitahuanController`](../../app/Http/Controllers/Jadwal/SuratPemberitahuanController.php) | Surat pemberitahuan jadwal fasilitasi kepada kabupaten/kota. | surat-pemberitahuan.manage | `surat-pemberitahuan.*` | `pages/jadwal/surat-pemberitahuan` |
| [`UndanganPelaksanaanController`](../../app/Http/Controllers/Jadwal/UndanganPelaksanaanController.php) | Undangan pelaksanaan fasilitasi: dibuat dan dikirim Admin PERAN, dilihat dan dikonfirmasi penerima. | kelola: undangan-pelaksanaan.manage; lihat undangan sendiri: pengguna login | `my-undangan.*`, `pemohon.undangan.*`, `undangan-pelaksanaan.*` | `pages/jadwal/undangan-pelaksanaan` |

### `MasterData/` — Data referensi yang dikelola Admin

| Controller | Tanggung jawab | Akses | Nama route | View |
|---|---|---|---|---|
| [`KabupatenKotaController`](../../app/Http/Controllers/MasterData/KabupatenKotaController.php) | Master data kabupaten/kota. | master.kabkota.manage | `kabupaten-kota.*` | `pages/master-data/kabupaten-kota` |
| [`MasterJenisDokumenController`](../../app/Http/Controllers/MasterData/MasterJenisDokumenController.php) | Master jenis dokumen perencanaan (RPJPD, RPJMD, RKPD). | master.jenis-dokumen.manage | `master-jenis-dokumen.*` | `pages/master-data/jenis-dokumen` |
| [`MasterTahapanController`](../../app/Http/Controllers/MasterData/MasterTahapanController.php) | Master tahapan fasilitasi. Hanya bisa diubah (nama, urutan); daftar tahapan tetap 7. | master.tahapan.manage | `master-tahapan.*` | `pages/master-data/tahapan` |
| [`MasterKelengkapanController`](../../app/Http/Controllers/MasterData/MasterKelengkapanController.php) | Master dokumen kelengkapan yang wajib diunggah pemohon. | master.kelengkapan.manage | `master-kelengkapan.*` | `pages/master-data/kelengkapan` |
| [`MasterBabController`](../../app/Http/Controllers/MasterData/MasterBabController.php) | Master sistematika dokumen (bab dan sub-bab) per jenis dokumen. | master.bab.manage | `master-bab.*` | `pages/master-data/bab` |
| [`MasterUrusanController`](../../app/Http/Controllers/MasterData/MasterUrusanController.php) | Master urusan pemerintahan sesuai kamus data urusan: kode `X.XX` (kelompok 1–8), OPD pengampu, status, dan penanda **tampil di hasil fasilitasi** (tombol aktif/nonaktif per urusan). Kelompok, rumpun, dan singkatan OPD pengampu diisi otomatis oleh model. Urusan yang sudah dipakai masukan hasil fasilitasi tidak bisa dihapus. Daftar: pencarian kode/nama/OPD pengampu, filter kelompok, status, tampil di hasil fasilitasi, dan OPD pengampu (sudah/belum diisi), 15 per halaman. | master.urusan.manage | `master-urusan.*` (termasuk `master-urusan.toggle-hasil-fasilitasi`) | `pages/master-data/urusan` |
| [`FormatDokumenController`](../../app/Http/Controllers/MasterData/FormatDokumenController.php) | Format dokumen hasil fasilitasi berbasis data (Fase 6): daftar versi per jenis dokumen, buat draft dari format awal, susun bagian masukan dan blok pada versi draft lewat **satu form** (`update`: isi, urutan & tanda hapus dari halaman, plus `aksi` `sisip_blok`/`tambah_bagian` yang dijalankan setelah perubahan lain tersimpan), pratinjau PDF dengan data contoh, salin ke versi/jenis lain, aktifkan (versi aktif lain dengan tahun berlaku sama menjadi arsip), arsipkan, hapus draft yang belum dipakai. Versi aktif/arsip hanya-baca. Form Request: `UpdateFormatDokumenRequest` | master.format-dokumen.manage | `format-dokumen.*` | `pages/master-data/format-dokumen` |
| [`OpdController`](../../app/Http/Controllers/MasterData/OpdController.php) | Master OPD lingkungan Provinsi sesuai kamus data OPD (kode, nama, jenis, rumpun urusan, status, OPD induk) beserta urusan yang boleh diberi masukan (disinkronkan otomatis dari kode urusan diampu, tidak dipilih manual); dipakai saat membuat akun ber-role `opd`. Daftar: pencarian kode/nama/singkatan, filter jenis, rumpun, status, dan urusan masukan (sudah/belum diatur), 15 per halaman. OPD yang masih punya akun tidak bisa dihapus. | master.opd.manage | `opd.*` | `pages/master-data/opd` |

### `Admin/` — Administrasi sistem: pengguna, tim, role & permission, log, modul

| Controller | Tanggung jawab | Akses | Nama route | View |
|---|---|---|---|---|
| [`UserController`](../../app/Http/Controllers/Admin/UserController.php) | Kelola akun pengguna. Akun superadmin hanya dapat dikelola superadmin (UserPolicy). Role terlindungi (`superadmin`, `kaban`, `admin_peran`) hanya dapat diberikan atau diubah superadmin (`User::nonAssignableRolesFor`, `User::isRoleLockedFor`). Akun ber-role `opd` wajib terhubung ke OPD aktif. | user.manage (ekspor: user.export) | `users.*` | `pages/admin/users` |
| [`TimAssignmentController`](../../app/Http/Controllers/Admin/TimAssignmentController.php) | Penugasan tim (verifikator & fasilitator) ke kabupaten/kota per tahun dan jenis dokumen, termasuk file SK tim. | tim-assignment.manage | `tim-assignment.*` | `pages/admin/tim-assignment` |
| [`RoleController`](../../app/Http/Controllers/Admin/RoleController.php) | Halaman Role & Hak Akses (PRD #6): checklist hak akses per role (label saja), kembalikan ke default, tambah/hapus role. Bagi Admin, role superadmin dan permission tingkat pengelola tidak ditampilkan. Batas wewenang di `RolePolicy`; perubahan dicatat di activity log (ditambah/dicabut). | role.manage (+ RolePolicy) | `admin.roles.*` | `pages/admin/roles` |
| [`PermissionController`](../../app/Http/Controllers/Admin/PermissionController.php) | Katalog hak akses read-only per modul beserta role pemiliknya (label saja, tanpa nama permission). Bagi Admin, role superadmin dan permission tingkat pengelola tidak ditampilkan. | role.manage | `admin.permissions.index` | `pages/admin/permissions` |
| [`ActivityLogController`](../../app/Http/Controllers/Admin/ActivityLogController.php) | Menampilkan log aktivitas pengguna (spatie/laravel-activitylog) dengan filter. | activity-log.view | `activity-log.*` | `pages/admin/activity-log` |
| [`ModulController`](../../app/Http/Controllers/Admin/ModulController.php) | Modul/panduan pengguna. Setiap modul dapat ditujukan ke satu role atau semua. | lihat & unduh: pengguna login sesuai role modul; kelola: modul.manage | `modul.*` | `pages/admin/modul` |

### `Umum/` — Fitur lintas role: dashboard, arsip, unduh berkas, notifikasi, profil

| Controller | Tanggung jawab | Akses | Nama route | View |
|---|---|---|---|---|
| [`DashboardController`](../../app/Http/Controllers/Umum/DashboardController.php) | Dashboard per role. Memilih tampilan berdasarkan role pengguna (identitas, bukan hak akses). | pengguna login | `dashboard` | `pages/umum/dashboard` |
| [`ArsipController`](../../app/Http/Controllers/Umum/ArsipController.php) | Arsip seluruh dokumen permohonan per jenis dokumen. Daftar dibatasi scope Permohonan::visibleTo, detail oleh PermohonanPolicy. | semua pengguna login (dibatasi policy) | `arsip.*` | `pages/umum/arsip` |
| [`BerkasController`](../../app/Http/Controllers/Umum/BerkasController.php) | Menyajikan dokumen dari disk privat `dokumen` setelah memeriksa hak akses. Jenis berkas yang boleh diakses didaftarkan di konstanta JENIS. | mengikuti PermohonanPolicy::downloadDokumen (atau PerpanjanganWaktuPolicy::view untuk perpanjangan tanpa permohonan) | `berkas.*` | — |
| [`NotifikasiController`](../../app/Http/Controllers/Umum/NotifikasiController.php) | Notifikasi in-app milik pengguna yang sedang login. | pengguna login (hanya notifikasinya sendiri) | `notifikasi.*` | `pages/umum/notifikasi` |
| [`ProfileController`](../../app/Http/Controllers/Umum/ProfileController.php) | Profil pengguna: data diri, nomor WhatsApp, foto profil, dan hapus akun. | pengguna login (akunnya sendiri) | `profile.*` | `pages/umum/profile` |

### `Auth/` — Login, logout, verifikasi email & nomor WhatsApp, kata sandi

| Controller | Tanggung jawab | Akses | Nama route | View |
|---|---|---|---|---|
| [`AuthenticatedSessionController`](../../app/Http/Controllers/Auth/AuthenticatedSessionController.php) | Login dan logout. Login dibatasi 5 percobaan per email+IP (LoginRequest); login/logout dicatat di activity log. | tamu (login), pengguna login (logout) | `login`, `logout` | `auth` |
| [`ConfirmablePasswordController`](../../app/Http/Controllers/Auth/ConfirmablePasswordController.php) | Konfirmasi ulang kata sandi sebelum aksi sensitif (bawaan Breeze). | pengguna login | `password.*` | `auth` |
| [`EmailVerificationNotificationController`](../../app/Http/Controllers/Auth/EmailVerificationNotificationController.php) | Kirim ulang email verifikasi (bawaan Breeze). | pengguna login | `verification.*` | — |
| [`EmailVerificationPromptController`](../../app/Http/Controllers/Auth/EmailVerificationPromptController.php) | Halaman pemberitahuan verifikasi email (bawaan Breeze). | pengguna login | `verification.*` | `auth` |
| [`PasswordController`](../../app/Http/Controllers/Auth/PasswordController.php) | Ganti kata sandi dari halaman profil (bawaan Breeze). | pengguna login | `password.*` | — |
| [`PhoneVerificationController`](../../app/Http/Controllers/Auth/PhoneVerificationController.php) | Verifikasi nomor WhatsApp dengan OTP via Fonnte. Wajib sebelum mengakses fitur lain (middleware EnsurePhoneIsVerified). | pengguna login | `phone.*`, `phone.verify.*`, `phone.verify.otp.*` | `auth` |
| [`VerifyEmailController`](../../app/Http/Controllers/Auth/VerifyEmailController.php) | Tandai email terverifikasi dari tautan bertanda tangan (bawaan Breeze). | pengguna login, URL signed | `verification.*` | — |

> [`Controller`](../../app/Http/Controllers/Controller.php) adalah kelas dasar kosong. Controller yang memakai `$this->authorize()` menambahkan trait `AuthorizesRequests` sendiri. Controller baru disarankan memakai `Gate::authorize()`.

---

## Middleware

| Middleware | Alias | Fungsi |
|---|---|---|
| [`EnsurePhoneIsVerified`](../../app/Http/Middleware/EnsurePhoneIsVerified.php) | `verified.phone` | Mengarahkan pengguna ke verifikasi nomor WhatsApp bila belum terverifikasi. Dipasang pada grup utama route terautentikasi di `routes/web.php`. |
| `Illuminate\Auth\Middleware\Authorize` (bawaan Laravel) | `can` | Memeriksa permission atau policy, misalnya `->middleware('can:jadwal.manage')`. |
| Middleware Spatie (terdaftar, tidak dipakai di route) | `role`, `permission`, `role_or_permission` | Jangan dipakai untuk hak akses baru; gunakan `can:`. |

Alias didaftarkan di [`bootstrap/app.php`](../../bootstrap/app.php). Pengguna yang belum login diarahkan ke `/` (halaman login), bukan `/login`.

---

## Form Request

| Request | Dipakai oleh | Isi aturan |
|---|---|---|
| [`Auth\LoginRequest`](../../app/Http/Requests/Auth/LoginRequest.php) | `AuthenticatedSessionController::store` | Email + kata sandi; maksimal 5 percobaan gagal per email + IP. |
| [`ProfileUpdateRequest`](../../app/Http/Requests/ProfileUpdateRequest.php) | `ProfileController::update` | Data profil; nomor WhatsApp format `62…`; foto maksimal 2 MB. |
| [`StoreUserRequest`](../../app/Http/Requests/StoreUserRequest.php) | `UserController::store` | Pengguna baru; role terlindungi (`superadmin`, `kaban`, `admin_peran`) hanya untuk pemegang `user.manage-superadmin`; `opd_id` wajib (OPD aktif) bila role `opd`. |
| [`UpdateUserRequest`](../../app/Http/Requests/UpdateUserRequest.php) | `UserController::update` | Otorisasi `UserPolicy::manage`; Admin tidak bisa memberi role terlindungi dan tidak bisa mengubah role akun yang sudah memilikinya (pesan "hanya dapat diubah Superadmin"); `opd_id` wajib bila role `opd`. |
| [`UpdateTanggalSelesaiTahapanRequest`](../../app/Http/Requests/UpdateTanggalSelesaiTahapanRequest.php) | `TanggalSelesaiTahapanController::update` | Hanya tahapan selesai; tanggal tidak di masa depan, tidak sebelum permohonan dibuat, dan urut terhadap tahapan sebelum/sesudah yang sudah selesai (tanpa alasan). |
| [`MasterUrusanRequest`](../../app/Http/Requests/MasterUrusanRequest.php) | `MasterUrusanController::store/update` | Kode urusan `X.XX` unik dengan digit pertama 1–8; nama maks 150; setiap kode OPD pengampu (dipisah `;`) berformat `82.XX.XX`/`82.XX.XX.XX` dan terdaftar; penanda tampil di hasil fasilitasi; status Aktif/Tidak Aktif; tanggal pemutakhiran. |
| [`OpdRequest`](../../app/Http/Requests/OpdRequest.php) | `OpdController::store/update` | Kode OPD `82.XX.XX` atau `82.XX.XX.XX` (Biro/UPTD) unik dan diawali kode wilayah; nama unik maks 150; singkatan wajib maks 20; jenis & rumpun dari daftar di `Opd`; status Aktif/Tidak Aktif; tanggal pemutakhiran; kode urusan diampu `X.XX` (lebih dari satu dipisah `;`) dan terdaftar di master urusan; OPD induk harus terdaftar. |
| [`StoreRoleRequest`](../../app/Http/Requests/StoreRoleRequest.php) | `RoleController::store` | Otorisasi `RolePolicy::create`; nama role huruf kecil/angka/garis bawah, unik; permission harus ada di katalog. |
| [`UpdateRoleRequest`](../../app/Http/Requests/UpdateRoleRequest.php) | `RoleController::update` | Otorisasi `RolePolicy::update`; 403 bila mengirim permission terkunci (`RolePolicy::lockedPermissionsFor`); permission harus ada di katalog. |

Sebagian besar controller lama masih memvalidasi dengan `$request->validate()` langsung di method. Untuk kode baru, gunakan Form Request (`php artisan make:request`).

---

## Checklist Controller Baru

1. Buat di folder area yang sesuai (lihat [Struktur Folder](#struktur-folder)), misalnya `php artisan make:controller Fasilitasi/NamaController`. Satu controller untuk satu sumber daya atau satu tahapan.
2. Tulis docblock kelas berisi tanggung jawab, **Akses**, dan **View**, dengan format seperti controller lain. Tambahkan barisnya ke tabel di atas.
3. Tulis docblock untuk setiap method publik: satu kalimat apa yang dilakukan, ditambah aturan bisnis yang tidak terlihat dari kode (siapa yang boleh, status apa yang disyaratkan, efek samping seperti notifikasi atau activity log).
4. Lindungi route dengan `can:<permission>`. Pengecekan per record dilakukan lewat Policy, bukan `hasRole()`.
5. Lakukan validasi di Form Request, dengan pesan validasi berbahasa Indonesia.
6. Simpan berkas ke disk `dokumen`. Jangan menautkannya dengan `asset('storage/...')`.
7. Tulis test feature (akses ditolak, validasi gagal, sukses + perubahan data), lalu perbarui snapshot route (`UPDATE_ROUTE_SNAPSHOT=1`).
