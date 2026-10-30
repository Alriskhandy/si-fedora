# Fase 5 — Backlog

> **Status:** daftar tunggu. Item dikerjakan saat pemicunya terpenuhi.

**Indikatif:** menyesuaikan · Item di sini tidak dijadwalkan tetap. Masing-masing dikerjakan saat pemicunya terpenuhi dan dibuatkan plan rinci sendiri sebelum mulai.

| Item | Rekomendasi | Pemicu / prasyarat | Catatan plan |
|---|---|---|---|
| ~~**3.8 + 2.8**~~ | ~~Format baru hasil fasilitasi, sekaligus ekstraksi `HasilFasilitasiController`~~ | — | **Dipindah ke [Fase 6](FASE-6-FORMAT-HASIL-DINAMIS.md) (27 Okt 2026)**: format dibuat dinamis berbasis data; ekstraksi controller untuk alur masukan dikerjakan di Tahap 2. Ekstraksi bagian lain controller (upload draft final, aturan upload standar / A5) tetap backlog |
| **2.10** | Perbaikan fitur notifikasi: queue, retry, template | Rencana perbaikan notifikasi dari stakeholder (§8 no. 4) | Job `ShouldQueue` dengan `tries`/`backoff`, `queue:work` di bawah supervisor, pemantauan `failed_jobs`. Termasuk notifikasi untuk OPD yang ditunda di Fase 3 |
| **3.2** | SLA per tahapan + eskalasi | 2.10 | Kolom durasi SLA di `master_tahapan`, scheduler harian, eskalasi ke Admin PERAN/Kaban |
| **3.4** | Modul Pengaturan Sistem versi ringan | 2.10 (untuk template pesan) | Tabel `settings` + cache; tanpa fitur restore DB dari UI |
| **3.5** | Laporan periodik & KPI | Fase 4 (enum status mempermudah agregasi) | Durasi per tahap dari `permohonan_tahapan`; ekspor Excel via `phpoffice/phpspreadsheet` yang sudah terpasang |
| **3.6** | Kalender & timeline | Redesign UI (PRD #1) selesai | Timeline dari `activity_log` |
| **3.7** | Pencarian global | Policy & `scopeVisibleTo` (Fase 1) | Hasil wajib difilter `visibleTo` |
| **3.9** | Versi dokumen | `BerkasController` (Fase 1) | Manfaatkan tabel `dokumen_revisi` di semua modul upload |
| **4.1** | PWA ringan | 2.10 | Manifest + web push |
| **5.3** | Error tracking | Kapan saja; disarankan setelah Fase 1 | Sentry atau channel log email/slack untuk level `error`; audit log `ERROR` lama |
| **5.4** | Asesmen kepatuhan SPBE & UU PDP | Koordinasi dengan Diskominfo; disarankan sebelum akun OPD aktif | Data no. HP di `otp_codes`, retensi `activity_log`, data akun OPD |
| **2.4** | CI (GitHub Actions) + branch protection `main` | Keputusan pemilik proyek (ditunda dari Fase 2) | PHP 8.3 + PostgreSQL + build Vite + `composer audit` + `php artisan test`; Pint dengan `--diff` terhadap branch target karena 68 file lama belum terformat |
| **Backup & DR** | Strategi backup otomatis dan uji restore | Ditunda atas keputusan §8 no. 8 (24 Okt 2026); dikembangkan nanti | Belum dijadwalkan. Sementara memakai backup manual (runbook §2) sebelum setiap rilis |
| **Bug `tanggal_dibuat`** | `HasilFasilitasiController` (±baris 439) membaca `hasil_fasilitasi->tanggal_dibuat`, kolom yang tidak ada (selalu `null`) | Kapan saja; dulu direncanakan di PRD #5, dikeluarkan setelah keputusan §8 no. 6 | Ganti dengan sumber tanggal yang benar atau hapus; tambahkan test |

---

## Penghapusan Penugasan Tim (kebutuhan baru 24 Okt 2026)

> Dicatat di bagian akhir atas arahan pemilik proyek agar tidak mengganggu pengembangan yang sedang berjalan. **Belum dijadwalkan**; buat plan rinci dan konfirmasi pertanyaan terbuka di bawah sebelum mulai.

### Kebutuhan

Penugasan tim per kab/kota, tahun, dan jenis dokumen (`user_kabkota_assignments`, menu **Penugasan Tim**) **tidak lagi relevan**. Semua pengguna yang bertugas sebagai **fasilitator** dapat memberi masukan pada semua permohonan, mirip akun OPD (PRD #2) yang terbatas pada urusan pemerintahan bidangnya.

### Kondisi saat ini — fitur yang bergantung pada penugasan

| Fitur | Lokasi | Ketergantungan |
|---|---|---|
| Hak lihat permohonan verifikator & fasilitator | `PermohonanPolicy::view` (`isAssigned`), scope `Permohonan::visibleTo`, permission `permohonan.view-assigned` | Hanya permohonan sesuai penugasan aktif |
| Daftar & akses hasil fasilitasi | `HasilFasilitasiController::index`, `isTimMember`, `isVerifikator`, `tahapan/hasil.blade.php` (query penugasan langsung di view) | Daftar dan akses unduh Word/PDF dibatasi penugasan |
| Peran koordinator (PIC) | `HasilFasilitasiController::isKoordinator`, `canManageItem` | Koordinator (`is_pic`) boleh generate dokumen, unggah draft final, serta mengubah/menghapus masukan anggota lain |
| Susunan tim di halaman & dokumen hasil | `HasilFasilitasiController` (`timInfo`: verifikator, koordinator, anggota), `ApprovalController` | Nama tim diambil dari penugasan |
| Notifikasi tim | `PermohonanNotificationService`, `HasilFasilitasiController::notifyTeamMembers/notifyKoordinator`, `ApprovalController`, `TindakLanjutController`, `PenetapanPerdaController`, `PermohonanController::updateDeadline` | Penerima = anggota tim sesuai penugasan |
| Dashboard fasilitator | `DashboardController` (memakai `TimFasilitasiAssignment`, tabel lama) | Daftar permohonan milik tim |
| Sidebar | `layouts/partials/sidebar.blade.php` (`permohonan.view-assigned`) | Menu permohonan & hasil fasilitasi |
| Menu Penugasan Tim + SK tim | `TimAssignmentController`, `pages/admin/tim-assignment`, permission `tim-assignment.manage`, berkas SK tim | Seluruh fitur |

### Usulan pendekatan

1. Fasilitator: ganti `permohonan.view-assigned` dengan hak lihat semua permohonan (atau minimal yang berada di tahap hasil fasilitasi); akses input hasil fasilitasi cukup lewat permission `hasil-fasilitasi.input-masukan` dan role pengisi per bagian (Fase 6).
2. Role `opd` sudah diperlakukan sebagai fasilitator terbatas urusan (keputusan teknis 24 Okt 2026, Fase 3) dengan cabang sementara "akun OPD (pemegang `hasil-fasilitasi.input-masukan` yang terikat OPD) dianggap anggota tim". Saat penugasan dihapus, cabang itu digabung dengan aturan fasilitator yang baru.
3. Ganti query penugasan di controller, view, dan service dengan pengecekan permission/policy; pindahkan ke policy yang sudah ada.
4. Hapus menu, route, controller, view, dan permission Penugasan Tim setelah semua pemakai di atas diganti. Tabel `user_kabkota_assignments` dan `tim_fasilitasi_assignment` dihapus lewat migration terpisah setelah data lama diarsipkan.

### Pertanyaan terbuka (perlu jawaban sebelum mulai)

1. **Verifikator** juga dilepas dari penugasan (melihat dan memverifikasi semua permohonan), atau tetap memakai penugasan?
2. **Koordinator:** siapa yang menggantikan wewenang koordinator (generate dokumen, unggah draft final, mengelola masukan anggota lain) — Admin PERAN saja, atau semua fasilitator?
3. **Susunan tim pada dokumen/halaman hasil:** masih perlu dicantumkan? Bila ya, dari mana sumbernya (mis. fasilitator yang memberi masukan pada permohonan itu)?
4. **Notifikasi:** dikirim ke semua fasilitator, atau dihentikan sampai perbaikan notifikasi (2.10)?
5. **SK tim dan data penugasan lama:** diarsipkan atau dihapus?

