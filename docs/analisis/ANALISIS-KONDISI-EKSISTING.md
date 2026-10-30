# Analisis Kondisi Eksisting — SI-FEDORA

> 📁 **ARSIP — snapshot 21 September 2026.** Isi dokumen ini tidak diperbarui. Status terkini setiap temuan ada di [Rekomendasi Pengembangan §2](REKOMENDASI-PENGEMBANGAN.md#2-pembaruan-status-temuan-sebelumnya) dan [Plan Implementasi](../implementasi/README.md). Rujukan `docs/ANALYST_SYSTEM.md` di bawah sekarang berada di `role-skill/ANALYST_SYSTEM.md`.

> Sistem Informasi Fasilitasi/Evaluasi Dokumen Perencanaan Kabupaten/Kota — Bappeda Provinsi Maluku Utara
> Disusun mengikuti metodologi **System Analyst Review** (`docs/ANALYST_SYSTEM.md`): evidence-based, per-aspek, membedakan *Fact* (dari evidence kode/konfigurasi), *Finding* (hasil analisis), *Risk/Impact*, dan *Recommendation*. Snapshot per **21 September 2026**, seluruh evidence diambil langsung dari kode, migrasi, route, dan riwayat git — bukan dari asumsi dokumen rancangan.
>
> **Legenda tabel per-aspek:** kolom *Kondisi Eksisting* = Fact ringkas • *Evidence* = rujukan file/kode konkret • *Gap/Temuan* = Finding • *Risiko* = Risk/Impact • *Rekomendasi* = Recommendation • *Prioritas* = Tinggi/Sedang/Rendah (lihat metode skoring di §6). Baris bertanda **[UNKNOWN]** berarti tidak ada evidence di repo untuk disimpulkan — dinyatakan eksplisit sesuai prinsip "jangan berasumsi".

---

## 1. Ringkasan Kondisi Eksisting

SI-FEDORA adalah aplikasi Laravel 11 yang mendigitalkan proses fasilitasi/evaluasi dokumen perencanaan (RPJPD/RPJMD/RKPD) antara Bappeda Provinsi Maluku Utara dan 10 Kabupaten/Kota. Project berjalan **13 November 2025 – 24 Juli 2026** (184 commit), lalu tidak ada commit baru hingga tanggal analisis ini (±2 bulan idle — status keberlanjutan pasca periode ini **[UNKNOWN]**, perlu konfirmasi ke pemilik proses).

**Kesimpulan level tertinggi:**

1. **Fungsional matang, hampir seluruh alur bisnis inti (7 tahapan end-to-end) sudah berjalan digital** dan menggantikan proses manual surat-menyurat sesuai tujuan awal.
2. **Rekayasa perangkat lunak belum matang** — inkonsistensi skema database, kode orphan yang menunjuk kolom tak ada, 3 sistem notifikasi WhatsApp paralel yang belum dikonsolidasi, nyaris tanpa test otomatis untuk domain bisnis.
3. **Tata kelola & keberlanjutan operasional berisiko tinggi** — indikasi kuat proyek dikerjakan oleh 1 developer dominan (bus factor rendah), tanpa CI/CD, tanpa strategi backup/disaster recovery yang terverifikasi, dan tanpa dokumen governance formal.
4. **Sistem berdiri sendiri (silo)** — tidak ada integrasi ke sistem informasi pemerintah lain (mis. SIPD Kemendagri) meski regulasi yang dikutip sistem ini sendiri (Permendagri 70/2019 tentang SPBE) mengarah ke interoperabilitas data pemerintahan daerah.
5. Beberapa **dokumentasi tidak sinkron dengan kondisi nyata** (README menyebut TailwindCSS, nyatanya admin template Bootstrap/Sneat; `.env.example` menyebut sqlite, nyatanya PostgreSQL).

---

## 2. Profil Aplikasi

| Atribut | Keterangan |
|---|---|
| Nama Sistem | SI-FEDORA (Sistem Informasi Fasilitasi/Evaluasi Dokumen Rencana) |
| Pemilik Proses Bisnis | Bappeda Provinsi Maluku Utara, Bidang PERAN (evidence: `README.md`, `file-rancangan/alur.md`) |
| Pengguna Eksternal | 10 Bappeda Kabupaten/Kota se-Provinsi Maluku Utara (evidence: `database/seeders/MasterDataSeeder.php` — seluruh 10 kab/kota sudah di-seed) |
| Dasar Regulasi (dikutip literal dalam output dokumen sistem) | UU 25/2004 (SPPN), UU 23/2014 (Pemda), PP 12/2019, Permendagri 80/2015, **Permendagri 86/2017 ps.102** (dasar wajib fasilitasi Rancangan Perkada RKPD), **Permendagri 70/2019 (SPBE)**, Permendagri 10/2025, Inmendagri 2/2025, Kepmendagri 900.1.15.5-3406/2024 — evidence: template generate dokumen di `HasilFasilitasiDocumentService` |
| Periode Pengembangan Aktif | 13 Nov 2025 – 24 Jul 2026 (184 commit) |
| Status Aktivitas per Tanggal Analisis | Idle ±2 bulan — **[UNKNOWN]** apakah ini jeda sementara atau akhir siklus pengembangan |
| Tim Developer | Dominan 1 akun (`alriskhandy`, 186 commit gabungan variasi kapitalisasi nama) + 1 kontributor lain (`ildharahmelia`, 3 commit) — evidence: `git shortlog -sne` |
| Skala Kode | 34 model, 49 migrasi, 33 controller (~9.700 baris), 135 Blade (~27.000 baris), 210+ route |
| Role Pengguna | 7 role (superadmin, kaban, admin_peran, verifikator, fasilitator, pemohon, auditor) via `spatie/laravel-permission` |
| Lingkungan Deploy Produksi | **[UNKNOWN]** — tidak ada file IaC/Dockerfile/CI-CD di repo untuk memastikan topologi produksi; hanya `.env` lokal (pgsql, APP_ENV=local) yang bisa diverifikasi |

---

## 3. Analisis Per Aspek

### 3.1 Business & Governance

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Tujuan & sasaran | Tujuan jelas: digitalisasi alur fasilitasi/evaluasi dokrenda, tertulis eksplisit di README dan selaras dengan SOP manual asli (`714.pdf`) | `README.md` §Tujuan, `file-rancangan/714.pdf` | Tidak ada dokumen KPI/target keberhasilan terukur (mis. target waktu proses turun X%) | Sulit mengevaluasi keberhasilan proyek secara objektif | Tetapkan KPI operasional (rata-rata waktu proses, tingkat kelengkapan dokumen submit-pertama, dst) yang bisa ditarik dari data sistem | Sedang |
| Stakeholder & ownership teknis | Kepemilikan proses bisnis jelas (Bappeda Provinsi/Bidang PERAN); kepemilikan **teknis/maintenance** sangat terpusat | `git shortlog` — 1 kontributor dominan | Tidak ada evidence unit TI/vendor formal yang menangani maintenance pasca-deployment | **Bus factor rendah** — kontinuitas sistem bergantung pada 1 orang | Dokumentasikan runbook operasional + rencana knowledge transfer/backup personel | **Tinggi** |
| Tata kelola perubahan (change management) | Tidak ada proses formal yang terlihat dari repo (tidak ada CONTRIBUTING.md, tidak ada branch protection/PR template) | `.github/` kosong, tidak ada file governance | Perubahan sistem kemungkinan ad-hoc, tanpa review terstruktur | Risiko regresi tidak terdeteksi sebelum sampai ke pengguna | Terapkan minimal: PR review wajib, checklist rilis, changelog | Sedang |
| Kepatuhan regulasi terkait data pemerintah | Sistem mengutip Permendagri 70/2019 (SPBE) sebagai dasar hukum dokumennya sendiri | `HasilFasilitasiDocumentService` dasar hukum poin f | **[UNKNOWN]** — kepatuhan riil (interoperabilitas data, keamanan data pemerintah) terhadap SPBE belum diverifikasi; lihat juga temuan silo data di §3.4 | Potensi ketidaksesuaian dengan arah kebijakan SPBE yang justru dirujuk sistem sendiri | Lakukan asesmen kepatuhan SPBE formal oleh pihak berwenang (Diskominfo/BSSN terkait) | Sedang |
| Perlindungan data pribadi | Sistem menyimpan data pribadi (nama, no HP, email, foto profil) milik ASN kab/kota | `users` table, `profile-photos` storage | **[UNKNOWN]** — tidak ada evidence kebijakan retensi/penghapusan data, atau kepatuhan UU PDP (UU 27/2022) | Potensi risiko kepatuhan hukum bila terjadi kebocoran data | Asesmen kepatuhan UU PDP, khususnya penyimpanan no HP di `otp_codes` dan log aktivitas | Sedang |

### 3.2 Business Process

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Alur kerja end-to-end | 7 tahap (Permohonan→Verifikasi→Penetapan Jadwal→Pelaksanaan→Hasil Fasilitasi→Tindak Lanjut→Penetapan Perda) terimplementasi penuh, status per tahap dilacak di `permohonan_tahapan` | `Permohonan::getProgressSteps()`, `MasterTahapan`, seluruh controller tahapan | Alur "perpanjangan waktu" (di luar 7 tahap baku) menambah kompleksitas percabangan status yang tidak tervisualisasikan eksplisit di progress tracker utama | Pengguna berpotensi bingung status permohonan saat sedang dalam proses perpanjangan waktu | Tambahkan indikator status "menunggu perpanjangan waktu" di progress tracker utama, bukan hanya di halaman terpisah | Sedang |
| Aktor & kewenangan | 7 role terdefinisi granular via Spatie, masing-masing dashboard berbeda | `RolePermissionSeeder`, `DashboardController` | Assignment tim verifikator/fasilitator ke tiap permohonan bersifat **manual, tersentralisasi di 1 role** (`admin_peran`) tanpa mekanisme auto-assign atau SLA reminder khusus tahap ini | Berpotensi jadi bottleneck jika volume permohonan/kab-kota bertambah dan jumlah admin_peran terbatas — **[UNKNOWN]** jumlah user riil per role di produksi | Evaluasi kebutuhan auto-assign berbasis beban kerja, atau minimal reminder SLA untuk tahap assignment | Sedang |
| Approval & tanda tangan Kaban | Approval Kaban = update status (`status_draft` → `menunggu_persetujuan_kaban` → disetujui) + upload file PDF hasil TTD manual (`uploadDraftFinal`) | `ApprovalController::approve()`, `HasilFasilitasiController::uploadDraftFinal()` | **Tidak ada e-sign/digital signature asli** meski direncanakan di `desain.md` modul I3 ("TTD Digital atau Upload file TTD") — sistem hanya mencatat hasil TTD manual/fisik yang di-scan lalu diupload | Proses tanda tangan fisik tetap jadi titik lambat di luar sistem (tidak terukur SLA-nya oleh sistem); risiko file upload tertukar/salah tanpa verifikasi keaslian tanda tangan | Evaluasi kebutuhan integrasi e-sign resmi (mis. BSrE/tanda tangan elektronik tersertifikasi) sesuai arah digitalisasi pemerintah | Sedang |
| Redundansi proses (notifikasi) | Ditemukan pola **notifikasi ganda by-design**: kode baru (service terpusat) dijalankan berdampingan dengan "notifikasi legacy (database only) - backup" | Komentar eksplisit di `TindakLanjutController.php:201`, `PenetapanPerdaController.php:170`, `ApprovalController.php:417,520` | Transisi arsitektur notifikasi menuju service terpusat (`PermohonanNotificationService`) **belum dituntaskan** — kode lama sengaja dipertahankan sebagai fallback, bukan dihapus | Kompleksitas maintenance meningkat; potensi notifikasi terkirim dobel ke user pada beberapa event | Tuntaskan migrasi ke `PermohonanNotificationService` untuk seluruh event, baru hapus kode legacy — selaras dengan temuan §3.6 (3 service WhatsApp paralel) | **Tinggi** |
| Bottleneck operasional riil | — | — | **[UNKNOWN]** — memerlukan data volume transaksi & waktu proses riil dari database produksi, tidak bisa disimpulkan dari kode statis | — | Analisis lanjutan berbasis data produksi (rata-rata durasi per tahap, tahap dengan antrian terpanjang) | Rendah (butuh data dulu) |

### 3.3 Functional & Application

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Cakupan modul vs rencana (`desain.md` A–N) | 12 dari 14 modul rencana awal terimplementasi penuh (A–L minus sebagian L), 1 partial (M), 1 belum ada (N) | Lihat rincian tabel §3.3.1 di bawah | Modul O (mobile/PWA), P (search global terpusat), Q (timeline/calendar view, version control dokumen), dan **API eksternal** belum ada sama sekali | Skalabilitas kebutuhan masa depan (integrasi, akses mobile native) belum tersedia fondasinya | Prioritaskan API Resource layer (sudah di-roadmap-kan di `redesign-database.md` Fase 2) sebelum modul lain, karena jadi fondasi modul O/Q ke depan | Sedang |
| **Kesesuaian fitur vs kebutuhan riil regulasi** | Form "Konsistensi & Keselarasan" dan "Rekomendasi" diimplementasikan sebagai **catatan bebas (free-text)** | `hasil_fasilitasi_form`/`hasil_fasilitasi_rekomendasi` tabel — hanya kolom `catatan` | Dokumen resmi `FORMAT_RKPD_2026.md` (Permendagri) mensyaratkan **8 form terstruktur multi-kolom** (kode program, indikator, target, satuan, PD penanggung jawab, dst) — fitur yang "ada" **belum memenuhi kebutuhan format resmi** | Output hasil fasilitasi berisiko tidak match format yang diminta regulasi jika suatu saat diminta versi terstruktur, bukan naratif | Evaluasi apakah free-text sudah cukup untuk kebutuhan riil pengguna, atau perlu form terstruktur sesuai FORM 1–8 RKPD | Sedang |
| Error handling | Notifikasi service pakai try-catch non-blocking (kegagalan kirim WA tidak menggagalkan proses utama) — praktik baik | Pola try-catch + `Log::error()` di `PermohonanNotificationService`, `FonteService` | Exception handling global (`bootstrap/app.php withExceptions()`) **kosong/default** — belum ada custom handling untuk error page user-friendly, atau reporting error ke channel monitoring | User awam berpotensi melihat error page teknis Laravel mentah bila terjadi exception tak tertangani | Tambahkan custom exception rendering minimal untuk error umum (403/404/500) yang ramah pengguna non-teknis | Rendah |
| Validasi & business rules | Validasi dilakukan, tapi tersebar inline di controller, bukan Form Request terpusat | Hanya 2 file di `app/Http/Requests` dari 33 controller | Konsistensi aturan validasi (ukuran file, tipe file) antar-modul upload **tidak terjamin seragam** | Celah kualitas data & keamanan upload berbeda-beda per modul | Ekstraksi validasi upload ke Form Request bersama/terstandardisasi | Sedang |
| 3 TODO eksplisit belum dikerjakan | Notifikasi admin saat perpanjangan waktu diajukan; cek assignment verifikator/fasilitator; generate PDF laporan verifikasi | `PerpanjanganWaktuController.php:141`, `PermohonanDokumenController.php:346`, `LaporanVerifikasiController.php:285` | Fitur yang disebut "ada" (laporan verifikasi) sebenarnya belum lengkap (PDF belum bisa di-generate) | Admin PERAN tidak mendapat notifikasi otomatis saat pemohon ajukan perpanjangan waktu — berpotensi terlewat | Selesaikan 3 TODO ini — effort kecil, dampak langsung ke kelengkapan fungsi | **Tinggi** (effort rendah, dampak langsung) |

**§3.3.1 — Rincian cakupan modul A–N:**

| Modul Rencana | Status | Bukti |
|---|---|---|
| A. Autentikasi & Profil | ✅ Implemented | Breeze + `PhoneVerificationController` (OTP WA) |
| B. Manajemen User & Kab/Kota | ✅ Implemented | `UserController`, `KabupatenKotaController` |
| C. Master Data | ✅ Implemented | 5 controller Master* |
| D. Penjadwalan & Surat Pemberitahuan | ✅ Implemented | `JadwalFasilitasiController`, `SuratPemberitahuanController` |
| E. Permohonan (Kab/Kota) | ✅ Implemented | `PermohonanController`, `PermohonanDokumenController` |
| F. Verifikasi | ✅ Implemented | `VerifikasiController`, `LaporanVerifikasiController` |
| G. Evaluasi (Pokja) | ✅ Implemented | `HasilFasilitasiController` |
| H. Admin PERAN | ✅ Implemented | `AdminPeranController`, `TimAssignmentController` |
| I. Kepala Badan (Kaban) | ⚠️ Implemented tanpa e-sign asli | `ApprovalController` (lihat §3.2) |
| J. Pelaksanaan Fasilitasi | ✅ Implemented | `PelaksanaanFasilitasiController`, `UndanganPelaksanaanController` |
| K. Tindak Lanjut & Penetapan | ✅ Implemented | `TindakLanjutController`, `PenetapanPerdaController` |
| L. Notifikasi & Komunikasi | ⚠️ Partial — WA jalan tapi terfragmentasi (§3.6), email minim | `NotifikasiController` |
| M. Laporan & Monitoring | ⚠️ Partial — dashboard ada, laporan periodik otomatis tidak ditemukan | — |
| N. Pengaturan Sistem | ❌ Belum ada | Tidak ada `SettingController` |

### 3.4 Data & Integration

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Sumber data | 100% input manual (form + upload dokumen) oleh pengguna internal sistem | Seluruh controller berbasis `Request` form/file | Tidak ada data tarikan otomatis dari sistem lain yang relevan (mis. data RKPD dari SIPD Kemendagri) untuk validasi silang | Potensi human-error re-entry data yang sebenarnya sudah ada di sistem nasional lain | Kaji kelayakan integrasi baca-saja (read-only) ke SIPD jika API-nya tersedia untuk kab/kota | Rendah (dependency eksternal) |
| Struktur & kualitas skema | 34 model/49 migrasi, mayoritas konsisten, tapi ada inkonsistensi penamaan FK & kolom orphan | Rincian lengkap di §3.4.1 | Lihat §3.4.1 | Lihat §3.4.1 | Lihat §3.4.1 | **Tinggi** |
| Integrasi antar sistem / API | **Tidak ada** `routes/api.php` yang dipakai, tidak ada endpoint API eksternal sama sekali | `bootstrap/app.php` hanya register `web.php` | Interoperabilitas = 0; sistem murni monolith Blade | Tidak bisa diintegrasikan ke sistem lain (mis. dashboard eksekutif provinsi, SIPD) tanpa membangun API dari nol | Bangun API Resource layer bertahap (sudah direncanakan di `redesign-database.md` Fase 2, belum dieksekusi) | Sedang |
| Data pemerintahan silo | Sistem mengutip Permendagri 70/2019 (SPBE) tapi tidak terhubung sistem SPBE/SIPD manapun | Lihat §3.1 | Ironi kebijakan: regulasi acuan sistem mengarah ke interoperabilitas, implementasi tetap silo | Duplikasi entri data di level provinsi vs nasional | Sama seperti baris "Sumber data" di atas | Rendah |
| Kualitas/duplikasi data riil | — | — | **[UNKNOWN]** — perlu akses database produksi untuk audit duplikasi/kualitas data, di luar cakupan pembacaan kode statis | — | Audit data terpisah dengan akses read-only ke DB produksi | Rendah (butuh akses data dulu) |

**§3.4.1 — Rincian inkonsistensi skema database:**

1. **FK `kabupaten_kota` tidak konsisten**: seluruh tabel pakai `kabupaten_kota_id`, kecuali `permohonan` yang pakai `kab_kota_id` — menyebar ke ≥36 lokasi kode (10 controller, model, service, view). *Evidence: `grep -rn "kab_kota_id" app` = 36 match.*
2. **Kode orphan menunjuk kolom/tabel tak ada**:
   - `App\Models\PermohonanDokumen` berisi class kedua `Evaluasi` → tabel `evaluasi` **tidak pernah dibuat migrasinya**. Referensi `User::evaluasi()` atau `RunWorkflowTest::Evaluasi::create()` akan SQL error jika dieksekusi.
   - `PermohonanTahapan::koordinatorId()` menunjuk kolom `koordinator_id` yang **tidak ada** di tabel `permohonan_tahapan`.
   - `TimFasilitasiAssignment` punya fillable/cast/scope (`peran`, `ditugaskan_oleh`, `ditugaskan_pada`, `scopeByPeran`, dst) yang **tidak ada** di skema tabel aslinya (`id, permohonan_id, user_id, created_at` saja).
3. **Pelanggaran satu-file-satu-class**: `PermohonanDokumen.php` menyimpan 2 class.
4. **Status enum tersebar sebagai string literal** di 6+ tabel tanpa lapisan type-safety (belum ada PHP backed enum).
5. **Kolom deprecated masih load-bearing**: `persyaratan_dokumen_id` masih dipakai untuk validasi aktif padahal migrasi ke `master_kelengkapan_verifikasi` belum tuntas.

> Rencana perbaikan (Fase 1 & roadmap Fase 2+) sudah terdokumentasi lengkap di `file-rancangan/redesign-database.md` — **dicek ulang saat penyusunan dokumen ini dan dipastikan belum dieksekusi sama sekali** di kode saat ini.

### 3.5 UI/UX

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Konsistensi layout | Layout aktif adalah `layouts/app.blade.php` (admin template Sneat/Materio "vertical-menu-template-free", Boxicons), dipakai konsisten di 91 file Blade | `grep "@extends('layouts.app')"` = 91 match | Ada **2 file layout orphan tak terpakai**: `layouts/dashboard..php` (nama file tidak lazim, berisi header template serupa) dan `layouts/navBoostrap.blade.php` (typo "Boostrap", 0 referensi) — sisa eksperimen/iterasi lama yang tidak dibersihkan | Membingungkan developer baru yang menyangka file-file ini aktif dipakai; menambah beban maintenance/kognitif tanpa manfaat | Hapus `dashboard..php` dan `navBoostrap.blade.php` jika terkonfirmasi tak terpakai | Rendah (housekeeping) |
| View scaffolding sisa default | `resources/views/welcome.blade.php` (starter page default Laravel) dan `resources/views/index.html` (file HTML statis) **tidak direferensikan route manapun** — route `/` sudah dioverride langsung ke `auth.login`/dashboard | `routes/web.php` baris 56–58; `grep` tidak menemukan referensi ke `welcome`/`index.html` di route | File orphan menambah kebingungan struktur project | Rendah — tidak berdampak fungsional, murni kerapian | Hapus jika terkonfirmasi benar-benar tak dipakai | Rendah |
| Responsiveness dasar | Viewport meta responsive ada di layout utama | `layouts/app.blade.php` head | Modul "Mobile-Friendly" resmi (O1/O2 di `desain.md`) belum diimplementasikan sebagai fitur terpisah (push notification mobile, quick approval mobile) | Pengalaman di perangkat mobile bergantung sepenuhnya pada responsiveness bawaan template, belum dioptimasi khusus | Uji UX riil di perangkat mobile sebelum klaim "mobile-friendly" | Sedang |
| Usability, navigasi, accessibility, user journey friction | — | — | **[UNKNOWN]** — memerlukan pengujian langsung di browser oleh manusia (walkthrough per role), tidak bisa disimpulkan dari pembacaan kode statis saja | — | Lakukan usability testing terstruktur per role (7 role) + audit accessibility dasar (kontras warna, label form, keyboard navigation) | Sedang |

### 3.6 Technology & Architecture

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Backend & arsitektur | Laravel 11 monolith MVC, tanpa layer Service/Action yang konsisten | `composer.json`, struktur `app/Http/Controllers` | Controller gemuk: `HasilFasilitasiController` 1.579 baris, `PermohonanController` 723, `ApprovalController` 591, `TimAssignmentController` 641 — logic bisnis+query+file+notifikasi campur di 1 layer | Sulit di-test unit, sulit dipelihara jangka panjang, risiko bug meningkat tiap fitur baru ditambah | Ekstraksi bertahap ke Service/Action class (pola `HasilFasilitasiDocumentService`/`PermohonanNotificationService` sudah ada sebagai referensi) | Sedang |
| Database | PostgreSQL (`DB_CONNECTION=pgsql` di `.env` riil) | `.env` lokal | `.env.example` masih default `sqlite` bawaan Laravel — developer baru yang copy apa adanya salah konfigurasi | Onboarding developer baru lambat/error | Update `.env.example` sesuai stack riil | Rendah (effort kecil) |
| Frontend | **Bootstrap admin template (Sneat/Materio)** via `layouts/app.blade.php` + Boxicons; Alpine.js minor | Evidence §3.5 | `README.md` menyatakan "Blade + TailwindCSS" — **tidak akurat**; Tailwind ada di `package.json` tapi bukan sistem desain utama dashboard | Dokumentasi menyesatkan tim baru soal cara styling halaman baru | Perbaiki README sesuai stack riil | Rendah (effort kecil) |
| Duplikasi layanan WhatsApp | **3 implementasi WhatsApp paralel**: `FonteService` (Fonnte — standar aktif: OTP, notifikasi permohonan, assignment tim, jadwal), `WhatsAppService` (Twilio — aktif hanya utk 1 alur: surat pemberitahuan via `SendSuratPemberitahuanJob`), `WahaService` (WAHA — orphan total, 0 referensi) | `grep` penggunaan tiap service (lihat detail §3.6.1) | Kredensial Twilio (`TWILIO_SID`/`AUTH_TOKEN`) **sudah tidak ada** di `.env.example` — indikasi migrasi ke Fonnte belum dituntaskan, jalur Twilio berisiko diam-diam berhenti berfungsi tanpa disadari | Dua vendor pihak ketiga harus dikelola untuk fungsi sama; biaya & kompleksitas operasional ganda; potensi notifikasi surat pemberitahuan berhenti tanpa alert | Konsolidasi ke `FonteService`, migrasikan alur surat pemberitahuan, hapus `WhatsAppService` & `WahaService` beserta channel/notification Twilio | **Tinggi** |
| Dokumen generation | 2 service Word: `HasilFasilitasiDocumentService` (aktif, `novay/laravel-word-template`) vs `HasilFasilitasiDocumentServiceBackup` (sisa sebelum migrasi library) | `app/Services/` | File backup tidak dihapus, hanya dibiarkan di codebase | Kebingungan developer baru mana yang aktif; risiko tidak sengaja dipanggil lagi | Hapus `HasilFasilitasiDocumentServiceBackup.php` (riwayat tetap ada di git log) | Rendah |
| Rich text editor | TinyMCE dimuat dari **CDN Tiny Cloud v6** (butuh API key, versi tidak di-pin — otomatis update ke minor/patch terbaru) | `hasil-fasilitasi/create.blade.php` | Folder `file-rancangan/utilities/tinymce` (v5.8.1 self-hosted) **tidak dipakai** dan sudah dianalisis lengkap terpisah — downgrade jika dipakai apa adanya | Versi CDN unpinned bisa berubah perilaku tanpa perubahan kode (risiko regresi silent pada fitur generate Word/PDF yang bergantung `valid_elements` ketat) | Pin versi CDN TinyMCE ke versi eksak; putuskan nasib folder self-hosted (Opsi A/B sudah dianalisis) | Sedang |
| Deployment & infrastruktur | **[UNKNOWN]** — tidak ada Dockerfile/IaC/CI-CD di repo | `find` tidak menemukan Dockerfile/docker-compose/`.github` workflow | Topologi produksi (server, load balancer, strategi scaling) tidak terverifikasi dari repo | Tidak bisa menilai kesiapan skala/deployment tanpa info tambahan | Minta dokumentasi infrastruktur produksi dari pihak yang mengelola hosting | Sedang |

**§3.6.1 — Detail pemakaian tiap service WhatsApp:**

| Service | Provider | Status Pakai | Dipakai di |
|---|---|---|---|
| `FonteService` | Fonnte API | ✅ Aktif — standar de facto | `PhoneVerificationController` (OTP), `PermohonanNotificationService`, `TimAssignmentController`, `JadwalFasilitasiController` |
| `WhatsAppService` | Twilio | ⚠️ Aktif tapi terisolasi 1 alur | `SuratPemberitahuanNotification` → `SendSuratPemberitahuanJob` → `SuratPemberitahuanController` |
| `WahaService` | WAHA (self-hosted WA gateway) | ❌ Orphan — 0 referensi di luar file itu sendiri | — |

### 3.7 Security, Performance & Reliability

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Authentication | Laravel Breeze (hashing standar) + verifikasi nomor HP wajib via OTP WhatsApp | `verified.phone` middleware wajib di semua route terproteksi | Rate limiting (`throttle`) untuk endpoint kirim OTP **tidak ditemukan** di `routes/web.php` meski direncanakan (`throttle:3,1`) di dokumen desain OTP | Rawan abuse/spam pengiriman OTP (biaya API Fonnte + potensi disalahgunakan) | Terapkan `throttle` middleware pada route kirim OTP | **Tinggi** |
| Authorization (RBAC) | Role middleware Spatie konsisten di hampir semua group route | `routes/web.php` — `role:` middleware di 30+ group | Hanya 1 `Policy` class (`PerpanjanganWaktuPolicy`) dari 33 controller; otorisasi granular (mis. "verifikator hanya boleh proses permohonan yang di-assign ke dia") sebagian besar dicek manual inline — konsisten dengan `TODO: implement jika perlu` di `PermohonanDokumenController.php:346` | Celah otorisasi berpotensi tidak konsisten antar-controller (sebagian granular, sebagian belum) | Audit menyeluruh otorisasi object-level per controller, ekstraksi ke Policy class | Sedang |
| Audit trail | `spatie/laravel-activitylog` terpasang di model kunci | `LogsActivity` trait, mis. di `Permohonan` | — | — | — | — |
| Perlindungan konfigurasi | `APP_DEBUG=true` di `.env` lokal | `.env` lokal | **[UNKNOWN]** untuk environment produksi — tidak bisa diverifikasi dari repo ini | Jika `APP_DEBUG=true` di produksi: kebocoran stack trace/informasi sensitif ke publik saat error | Pastikan dan verifikasi `APP_DEBUG=false` di server produksi | **Tinggi** (potensi dampak besar bila belum) |
| Validasi upload file | Validasi dilakukan tapi tersebar inline per controller | Lihat §3.3 | Tidak ada aturan tipe/ukuran file terpusat & konsisten di semua modul upload (permohonan, undangan, perpanjangan waktu, penetapan perda) | Celah keamanan upload (mis. tipe file tidak seragam divalidasi) bervariasi antar modul | Sentralisasi lewat Form Request bersama | Sedang |
| Permukaan API | Tidak ada `routes/api.php` dipakai | — | — | Permukaan serangan API tidak relevan saat ini (positif) | — | — |
| Performance — caching | Cache store default: `database` (bukan Redis/Memcached) | `config/cache.php` `CACHE_STORE` | Untuk skala 10 kab/kota saat ini kemungkinan cukup, tapi berpotensi jadi bottleneck bila query dashboard makin berat | **[UNKNOWN]** — perlu profiling/APM riil untuk memastikan, tidak bisa dipastikan dari baca kode | Pertimbangkan Redis jika ada keluhan performa nyata; tidak mendesak tanpa data | Rendah (butuh data) |
| Performance — monitoring/APM | Tidak ada tool APM (Sentry/New Relic/Telescope) terpasang | `composer.json` — tidak ada paket monitoring | Tidak ada visibilitas performa/error real-time di produksi selain log file lokal | Masalah performa/error baru diketahui setelah dilaporkan user, bukan proaktif | Pertimbangkan integrasi tool error-tracking ringan (mis. Sentry) | Sedang |
| Reliability — backup & disaster recovery | **Tidak ditemukan mekanisme backup database otomatis maupun dokumentasi DR** | `find` tidak menemukan script/command/package backup (`spatie/laravel-backup` dkk tidak ada di `composer.json`); command scheduler hanya OTP cleanup & reminder deadline | Sistem menyimpan dokumen legal (Perda/Perkada final, surat rekomendasi resmi) dan 308MB file upload **tanpa strategi backup terverifikasi** | **Risiko kehilangan data permanen** jika terjadi kegagalan server/storage — dampak tinggi karena sifat dokumen legal/kenegaraan | Implementasikan backup otomatis terjadwal (DB + `storage/app`) dengan retensi jelas, uji proses restore secara berkala | **Tinggi** |
| Reliability — availability | Health check endpoint bawaan Laravel 11 terdaftar (`/up`) | `bootstrap/app.php` `health: '/up'` | Tidak ada evidence endpoint ini dipantau oleh uptime monitoring eksternal | Downtime berpotensi tidak terdeteksi cepat | Hubungkan `/up` ke uptime monitoring (mis. UptimeRobot/Better Uptime) | Sedang |

### 3.8 Operation & Sustainability

| Aspek | Kondisi Eksisting | Evidence | Gap/Temuan | Risiko | Rekomendasi | Prioritas |
|---|---|---|---|---|---|---|
| Monitoring & logging | `opcodesio/log-viewer` terpasang (UI baca log lokal) | `composer.json` | Tidak ada log aggregation terpusat/eksternal; log lokal sudah 12MB dengan 629 baris `ERROR` (entri terakhir 22 Jul 2026) belum diaudit isinya | Pola error berulang berpotensi tidak terdeteksi karena hanya bisa dibaca manual di server | Audit isi log secara berkala (sesi terpisah); pertimbangkan log rotation & aggregation | Sedang |
| Dokumentasi | README ada, tapi sebagian klaim tidak sinkron dengan kode riil (lihat §3.6); tidak ada CONTRIBUTING/runbook/API doc | `README.md`, struktur repo | Developer baru tidak punya panduan setup/deploy yang akurat | Onboarding lambat, potensi salah konfigurasi | Perbarui README; tambahkan runbook deployment minimal | Sedang |
| Maintenance / CI-CD | Tidak ada pipeline CI/CD (`.github` kosong), tidak ada test otomatis dijalankan di pipeline manapun | `find .github` kosong | Tidak ada jaring pengaman otomatis sebelum kode sampai ke produksi | Regresi baru terdeteksi manual/setelah dilaporkan user | Setup CI minimal (lint + test) sebelum menambah fitur baru | Sedang |
| Test otomatis | Hanya scaffolding default Breeze (Auth, Password, Profile test) + 2 `ExampleTest` kosong | `tests/Feature/Auth/*`, `tests/Unit/ExampleTest.php` | **Tidak ada satupun test untuk domain bisnis inti** (Permohonan, Verifikasi, HasilFasilitasi, Approval, PerpanjanganWaktu) meski logic-nya kompleks (workflow bertahap, deadline, perhitungan `getEffectiveDeadline()`) | Perubahan kode berisiko regresi silent pada alur bisnis kritis | Tambah test minimal untuk state machine status permohonan & logic deadline/perpanjangan waktu | **Tinggi** |
| Ketergantungan vendor/developer | Bus factor rendah (1 developer dominan) | Lihat §3.1 | — (sudah dibahas di Governance) | — | — | (lihat §3.1) |
| Keberlanjutan operasional | Idle commit ±2 bulan pada saat analisis | `git log -1` = 24 Jul 2026 vs tanggal analisis 21 Sep 2026 | **[UNKNOWN]** — apakah proyek dianggap "selesai/stabil" atau terhenti karena kendala non-teknis (anggaran, personel, dsb) | Tidak bisa direncanakan roadmap lanjutan tanpa kepastian status keberlanjutan | Konfirmasi status proyek ke pemilik proses sebelum menyusun roadmap jangka panjang | **Tinggi** (blocker untuk §8) |

---

## 4. Temuan & Gap (Konsolidasi)

Ringkasan lintas-aspek, dikelompokkan menurut rantai **proses bisnis → aplikasi → data → teknologi → pengguna** sesuai prinsip metodologi:

1. **Proses bisnis**: transisi arsitektur notifikasi belum tuntas (kode lama "backup" masih hidup berdampingan di 3 controller); assignment tim tersentralisasi manual tanpa SLA; approval Kaban belum pakai e-sign asli.
2. **Aplikasi**: 2 dari 14 modul rencana belum ada (N: Pengaturan Sistem; sebagian L/M partial); fitur "Konsistensi & Keselarasan"/"Rekomendasi" ada tapi belum match format resmi regulasi (free-text vs form terstruktur); 3 TODO eksplisit belum selesai.
3. **Data**: inkonsistensi FK (`kab_kota_id` vs `kabupaten_kota_id`), kode orphan menunjuk kolom/tabel tak ada (`Evaluasi`, `koordinator_id`, field `TimFasilitasiAssignment`), status enum tanpa type-safety.
4. **Teknologi**: 3 service WhatsApp paralel (1 orphan total), 2 service dokumen Word (1 backup tak terpakai), controller gemuk tanpa layer Service konsisten, tidak ada API, tidak ada CI/CD, tidak ada backup/DR terverifikasi, dokumentasi tidak sinkron dengan stack riil.
5. **Pengguna**: file layout/view orphan berpotensi membingungkan developer; usability/accessibility riil belum teruji langsung (perlu asesmen terpisah).

---

## 5. Risiko/Dampak

| # | Risiko | Sumber Temuan | Dampak Jika Terjadi | Estimasi Severity |
|---|---|---|---|---|
| R1 | Kehilangan data permanen (tidak ada backup/DR terverifikasi) | §3.7 | Kehilangan dokumen legal Perda/Perkada, riwayat permohonan seluruh kab/kota — dampak ke proses pemerintahan riil | **Kritis** |
| R2 | Bus factor rendah (1 developer dominan) tanpa governance/dokumentasi memadai | §3.1, §3.8 | Sistem tidak bisa dipelihara/dikembangkan jika developer utama tidak tersedia | **Kritis** |
| R3 | `APP_DEBUG` berpotensi aktif di produksi | §3.7 | Kebocoran informasi teknis sensitif ke publik | **Tinggi** (kondisional — perlu verifikasi env produksi) |
| R4 | Jalur notifikasi surat pemberitahuan (Twilio) berhenti diam-diam | §3.6 | Kab/kota tidak menerima notifikasi surat pemberitahuan jadwal fasilitasi tanpa ada yang sadar | **Tinggi** |
| R5 | Tidak ada rate limiting OTP | §3.7 | Abuse pengiriman OTP, biaya API membengkak, potensi DoS ringan ke endpoint auth | **Sedang-Tinggi** |
| R6 | Regresi silent pada alur bisnis kritis (tanpa test otomatis) | §3.8 | Bug pada logic deadline/status permohonan baru diketahui setelah merugikan pengguna riil (kab/kota) | **Sedang-Tinggi** |
| R7 | SQL error jika kode orphan (`Evaluasi`, `koordinator_id`) tidak sengaja terpanggil | §3.4.1 | Downtime/error tak terduga di fitur terkait | **Sedang** |
| R8 | Kompleksitas maintenance meningkat (3 WA service, controller gemuk) | §3.6, §3.8 | Waktu pengembangan fitur baru makin lambat, bug makin sulit dilacak | **Sedang** |
| R9 | Fitur hasil fasilitasi tidak match format resmi regulasi | §3.3 | Dokumen hasil fasilitasi berpotensi ditolak/perlu revisi manual jika diperiksa terhadap format RKPD resmi | **Sedang** |
| R10 | Keberlanjutan proyek tidak jelas pasca-idle 2 bulan | §3.8 | Tidak bisa merencanakan roadmap tanpa kepastian status | **Sedang** (blocker perencanaan, bukan risiko teknis langsung) |

---

## 6. Prioritas Perbaikan

Skoring kualitatif — **Impact** (dampak ke bisnis/pengguna), **Urgency** (seberapa mendesak), **Risk** (§5), **Effort** (estimasi usaha), **Dependency** (prasyarat item lain):

| # | Item Perbaikan | Impact | Urgency | Risk | Effort | Dependency | Prioritas Akhir |
|---|---|---|---|---|---|---|---|
| 1 | Verifikasi & pastikan `APP_DEBUG=false` + backup/DR terjadwal di produksi | Kritis | Tinggi | R1, R3 | Rendah–Sedang | — | **P0 — Segera** |
| 2 | Konfirmasi status keberlanjutan proyek & mitigasi bus-factor (dokumentasi/knowledge transfer) | Kritis | Tinggi | R2 | Rendah (dokumentasi) | — | **P0 — Segera** |
| 3 | Tambahkan `throttle` rate limiting pada endpoint OTP | Sedang-Tinggi | Tinggi | R5 | Rendah | — | **P1** |
| 4 | Tuntaskan konsolidasi notifikasi WhatsApp (Fonnte sebagai standar, migrasikan surat pemberitahuan, hapus kode legacy/orphan) | Tinggi | Sedang | R4, R8 | Sedang | Perlu koordinasi ganti kredensial | **P1** |
| 5 | Eksekusi Fase 1 `redesign-database.md` (bersihkan kode orphan, standarisasi `kab_kota_id`) | Sedang | Sedang | R7 | Sedang | Rencana sudah lengkap | **P1** |
| 6 | Tambah test otomatis untuk domain bisnis inti (status permohonan, deadline/perpanjangan waktu, approval) | Sedang-Tinggi | Sedang | R6 | Sedang-Tinggi | — | **P1** |
| 7 | Selesaikan 3 TODO eksplisit di kode | Sedang | Sedang | R9 (sebagian) | Rendah | — | **P1** (effort rendah, cepat selesai) |
| 8 | Perbaiki README & `.env.example` agar sinkron dengan stack riil | Rendah | Rendah | — | Rendah | — | **P2** |
| 9 | Housekeeping: hapus file/layout orphan (`WahaService`, `HasilFasilitasiDocumentServiceBackup`, `dashboard..php`, `navBoostrap.blade.php`, `welcome.blade.php`, `index.html`) | Rendah | Rendah | R8 (sebagian) | Rendah | — | **P2** |
| 10 | Ekstraksi controller gemuk ke Service/Action class | Sedang | Rendah | R8 | Tinggi | Sebaiknya setelah ada test (#6) | **P2** |
| 11 | Evaluasi form terstruktur sesuai FORM 1–8 RKPD resmi (vs free-text saat ini) | Sedang | Rendah | R9 | Sedang-Tinggi | Perlu konfirmasi kebutuhan riil pengguna | **P2** |
| 12 | Setup CI/CD minimal (lint + test) | Sedang | Rendah | R6 (tidak langsung) | Sedang | Sebaiknya setelah ada test (#6) | **P2** |
| 13 | Bangun API Resource layer & integrasi monitoring/APM | Sedang | Rendah | R8 | Tinggi | Fondasi untuk modul O/Q & integrasi eksternal | **P3** |
| 14 | Evaluasi e-sign digital untuk approval Kaban | Sedang | Rendah | — | Tinggi (dependency eksternal: BSrE) | — | **P3** |

---

## 7. Rekomendasi

**Segera (P0) — sebelum aktivitas pengembangan lain dilanjutkan:**
- Verifikasi konfigurasi produksi (`APP_DEBUG=false`) dan implementasikan backup database + storage terjadwal dengan uji restore berkala.
- Konfirmasi ke pemilik proses (Bappeda Provinsi) apakah proyek berlanjut, dan jika ya, mitigasi risiko bus-factor lewat dokumentasi teknis menyeluruh dan/atau penambahan personel.

**Jangka Pendek (P1) — dampak tinggi, effort rendah–sedang:**
- Tambahkan rate limiting OTP.
- Konsolidasikan sistem notifikasi WhatsApp ke satu provider.
- Eksekusi Fase 1 pembersihan skema database yang sudah direncanakan.
- Tambahkan test otomatis untuk logic bisnis kritis.
- Selesaikan 3 TODO eksplisit yang tersisa.

**Jangka Menengah (P2) — kualitas & housekeeping:**
- Sinkronkan dokumentasi (README, `.env.example`) dengan kondisi riil.
- Bersihkan seluruh kode/file orphan yang teridentifikasi.
- Mulai ekstraksi controller gemuk ke Service/Action class, didahului oleh test coverage.
- Evaluasi kebutuhan form terstruktur sesuai regulasi resmi vs implementasi free-text saat ini.
- Setup CI/CD minimal.

**Jangka Panjang (P3) — fondasi pengembangan lanjutan:**
- Bangun API Resource layer sebagai fondasi modul mobile/PWA, search terpusat, dan potensi integrasi ke SIPD/sistem pemerintah lain.
- Evaluasi kelayakan e-sign digital untuk proses approval Kaban.
- Pertimbangkan tool monitoring/APM untuk visibilitas performa & error proaktif.

---

## 8. Roadmap Pengembangan

> **Catatan penting**: roadmap ini **bersyarat** pada konfirmasi keberlanjutan proyek (lihat P0 #2 di §6) — item "[UNKNOWN] status keberlanjutan" di §3.8 adalah blocker utama sebelum roadmap jangka menengah–panjang bisa dieksekusi dengan yakin.

| Fase | Rentang Waktu (indikatif) | Fokus | Item Terkait |
|---|---|---|---|
| **Fase 0 — Stabilisasi Kritis** | 0–2 minggu | Menutup risiko kritis (P0): verifikasi konfigurasi produksi, backup/DR, konfirmasi keberlanjutan proyek | §6 #1, #2 |
| **Fase 1 — Perbaikan Fondasi** | 2–6 minggu | Keamanan dasar (rate limit), konsolidasi notifikasi, pembersihan skema database, mulai test coverage domain inti, tuntaskan TODO | §6 #3–#7 |
| **Fase 2 — Kualitas & Housekeeping** | 6–12 minggu | Sinkronisasi dokumentasi, bersih-bersih kode orphan, mulai refactor controller gemuk (setelah test coverage cukup), evaluasi form terstruktur regulasi, CI/CD dasar | §6 #8–#12 |
| **Fase 3 — Fondasi Pengembangan Lanjutan** | 3–6 bulan+ | API Resource layer, evaluasi e-sign digital, monitoring/APM, potensi modul baru (Pengaturan Sistem, search terpusat, mobile) | §6 #13–#14, modul N/O/P/Q |

---

*Dokumen ini disusun mengikuti metodologi di `docs/ANALYST_SYSTEM.md`, evidence-based dari pembacaan kode/konfigurasi/riwayat git per 21 September 2026. Item bertanda [UNKNOWN] memerlukan asesmen lanjutan (akses data produksi, konfirmasi stakeholder, atau pengujian langsung) dan sengaja tidak diasumsikan. Validasi ulang seluruh temuan terhadap kode terbaru sebelum eksekusi, karena project ini masih berpotensi aktif berubah.*
