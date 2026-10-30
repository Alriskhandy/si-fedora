# Fase 1 — Tutup Celah Akses

> **Status: ✅ kode selesai 24 Okt 2026 — belum dirilis.** Uptime monitoring belum dikonfigurasi.
> Hasil: `PermohonanPolicy` + scope `visibleTo`; dokumen di disk privat `dokumen` via `BerkasController`; provider WA selain Fonnte dihapus; 3 TODO tuntas; runbook dibuat.
> Bagian "Urutan Pengerjaan" di bawah adalah **rencana awal**. Penyimpangan (mis. disk `dokumen` alih-alih `local`, tanpa fallback, blok notifikasi legacy dipertahankan) ada di **[Catatan Pelaksanaan](#catatan-pelaksanaan-24-okt-2026)** — itu yang berlaku.

**Indikatif:** Minggu 3–5 · **Item:** 1.2, 1.5, 2.5, 3.1, 5.1, 5.2 · **Menutup:** A1, A4, temuan §2 no. 3–4 & 7

## Tujuan

- Dokumen hanya bisa diunduh oleh pengguna yang berhak.
- Kab/kota hanya melihat arsip miliknya (keputusan §8 no. 1).
- Fonnte menjadi satu-satunya provider WhatsApp (keputusan §8 no. 4).
- Ada runbook dan monitoring dasar sebelum perubahan besar di Fase 2.

## Prasyarat

- [ ] Fase 0 selesai (test terisolasi & hijau, upload memakai `FileUploader`)
- [ ] Backup DB dan `storage/app` sebelum rilis item 1.2

## Urutan Pengerjaan

`1.5 → 1.2 → 2.5 → 3.1`, dengan `5.1` dan `5.2` berjalan paralel. Policy (1.5) dibuat lebih dulu karena route unduh (1.2) memakainya.

---

### A. Policy object-level `Permohonan` *(item 1.5, menutup A4 & TODO `PermohonanDokumenController:346`)*

**File:** `app/Policies/PermohonanPolicy.php` (baru), `app/Models/Permohonan.php`, `ArsipController`, `PermohonanController`, `PermohonanDokumenController`

Aturan `view` (diturunkan dari keputusan §8 no. 1 dan role yang ada):

| Role | Boleh melihat |
|---|---|
| superadmin, admin_peran, kaban, auditor | Semua permohonan |
| pemohon | Hanya `permohonan.kab_kota_id == user.kabupaten_kota_id` |
| verifikator, fasilitator | Hanya kab/kota yang di-assign aktif (`User::isAssignedToKabkota()`, tabel `user_kabkota_assignments`) |

Langkah:
1. Buat `PermohonanPolicy` dengan `view`, `update`, `downloadDokumen`. Untuk Fase 1 boleh memakai pengecekan role; konversi ke permission dilakukan di Fase 2 (2.3).
2. Tambahkan local scope `Permohonan::scopeVisibleTo(Builder $query, User $user)` dengan aturan yang sama, untuk query daftar.
3. `ArsipController`:
   - `index()`: hitungan per jenis dokumen memakai `visibleTo`.
   - `listByJenis()`: query dan `tahunList` memakai `visibleTo`.
   - `show()`: `$this->authorize('view', $permohonan)`.
4. `PermohonanController::show()` dan endpoint dokumen: `authorize('view'|'update')`.
5. Hapus TODO di `PermohonanDokumenController:346` dan ganti dengan pemanggilan policy.

> Catatan: urutan pengerjaan `1.5 → 1.2 → …` di atas memakai nomor item rekomendasi. Langkah di dokumen ini diberi huruf (A–E) agar tidak tertukar.

**Test (feature):** pemohon kab/kota A membuka arsip/permohonan kab/kota B → 403 dan tidak muncul di daftar; verifikator yang tidak di-assign → 403; auditor → 200.

---

### B. Pindahkan dokumen ke storage privat *(item 1.2, menutup A1)*

**File:** `app/Support/FileUploader.php` (dari Fase 0), `app/Http/Controllers/BerkasController.php` (baru), command baru `app/Console/Commands/PindahkanBerkasKePrivat.php`, ±54 tautan `asset('storage/…')`/`Storage::url()` di view

**Prinsip desain:** path relatif file **tidak diubah**. File hanya dipindah dari `storage/app/public/<path>` ke `storage/app/private/<path>`, sehingga kolom path di database tidak perlu dimigrasi.

Langkah:
1. **Route unduh.** Buat satu `BerkasController` dengan peta *whitelist* jenis berkas → (model, kolom path, kolom nama asli, ability policy). Contoh: `permohonan-dokumen.file_path` → `PermohonanPolicy::downloadDokumen`. Route: `GET /berkas/{jenis}/{id}` → `Storage::disk('local')->download($path, $namaAsli)`. Jangan menerima path mentah dari request.
2. **Upload baru.** Ubah disk default di `FileUploader` menjadi `local`. Pengecualian: foto profil tetap di `public`.
3. **Fallback transisi.** Selama migrasi, `BerkasController` mencari file di disk `local` dulu, lalu `public`. Fallback ini dihapus setelah langkah 5.
4. **View.** Ganti seluruh `asset('storage/'.$x)` / `Storage::url($x)` untuk dokumen dengan `route('berkas.unduh', [...])`. Kerjakan per modul (verifikasi, arsip, hasil fasilitasi, jadwal, undangan, surat, tindak lanjut, penetapan perda, modul).
5. **Migrasi file lama.** Command `php artisan berkas:pindah-ke-privat {--dry-run}` memindahkan semua file yang direferensikan kolom path (kecuali `foto_profile`), **termasuk SK Tim di `public/sk-tim`** (kolom `user_kabkota_assignments.file_sk`; disimpan langsung di web root oleh `TimAssignmentController`, lihat catatan Fase 0). Command ini juga mencatat file yang hilang, dan bisa dijalankan ulang. Sediakan opsi `--rollback` untuk memindahkan balik.
6. Setelah migrasi terverifikasi, hapus fallback disk `public` di `BerkasController`.
7. Kolom nama asli: tambahkan kolom `*_nama_asli` (nullable) pada tabel yang belum punya `file_name` (daftar dari Fase 0 item 0.3). Untuk data lama, isi dari nama file yang ada.

**Test:** tamu → redirect login; pemohon kab/kota lain → 403; pemilik → 200 dengan header `Content-Disposition` berisi nama asli; `jenis` di luar whitelist → 404.

**Verifikasi di server:** `curl -I {APP_URL}/storage/<path-dokumen-lama>` → 404.

---

### C. Hapus provider WhatsApp selain Fonnte *(item 2.5, keputusan §8 no. 4)*

**File:** `app/Services/WhatsAppService.php` (Twilio), `app/Services/WahaService.php`, `app/Notifications/Channels/WhatsAppChannel.php`, `app/Notifications/SuratPemberitahuanNotification.php`, `app/Notifications/UndanganPelaksanaanNotification.php`, `app/Jobs/SendSuratPemberitahuanJob.php`, `app/Providers/AppServiceProvider.php`, `config/services.php`, `composer.json`

1. Pastikan channel `whatsapp` (dipakai `SuratPemberitahuanNotification::via()`) mengirim lewat `FonteService`, atau ganti pengiriman di Job agar langsung memanggil `FonteService`. Format pesan tidak diubah.
2. Hapus `WhatsAppService`, `WahaService`, binding Twilio di `AppServiceProvider::register()`, dan blok `twilio`/`waha` di `config/services.php`.
3. `composer remove twilio/sdk` (sudah disetujui).
4. **Blok "notifikasi legacy (database only) – backup"** di 5 lokasi (`ApprovalController:417,520`, `TindakLanjutController:201`, `PenetapanPerdaController:170`, `HasilFasilitasiController:1557`): sebelum dihapus, **verifikasi** bahwa `PermohonanNotificationService` sudah membuat notifikasi database untuk penerima yang sama. Bila ya, hapus blok legacy (menghilangkan notifikasi ganda). Bila belum, pindahkan penerima yang kurang ke service lalu hapus blok legacy.
5. Hapus variabel `TWILIO_*`/`WAHA_*` dari `.env.example`.

Perbaikan notifikasi lainnya (queue, retry, template) **tidak** termasuk fase ini (lihat 2.10 di Fase 5).

**Test:** `Http::fake()` pada endpoint Fonnte. Kirim surat pemberitahuan → satu request ke Fonnte; approval Kaban → notifikasi database tidak ganda.

---

### D. Tuntaskan TODO *(item 3.1)*

| TODO | Lokasi | Penyelesaian |
|---|---|---|
| Notifikasi ke Admin PERAN saat perpanjangan waktu diajukan | `PerpanjanganWaktuController:141` | Tambah method di `PermohonanNotificationService` (database + Fonnte) |
| Cek assignment | `PermohonanDokumenController:346` | Selesai di langkah A |
| PDF laporan verifikasi | `LaporanVerifikasiController:285` | Generate dengan engine PDF yang sudah dipakai `hasil-fasilitasi/pdf.blade.php`; unduh lewat `BerkasController` |

---

### E. Runbook *(item 5.1)* & monitoring *(item 5.2)*

- **Runbook** di `docs/operasional/RUNBOOK.md`: deploy, rollback, cara menjalankan scheduler (`schedule:run` di cron), command sekali jalan dari fase ini (`richtext:sanitize`, `berkas:pindah-ke-privat`), rotasi `FONNTE_TOKEN`, serta langkah backup manual sebelum rilis. Bagian seeder permission ditambahkan di Fase 2.
- **Uptime:** daftarkan endpoint `/up` ke layanan uptime monitoring dengan alert ke WA/email admin.

---

## Catatan Pelaksanaan (24 Okt 2026)

- **A (Policy)** — `PermohonanPolicy::view` dan scope `Permohonan::visibleTo()` adalah pemindahan aturan `PermohonanController::authorizeView()`/`index()` yang sudah ada, ditambah satu perubahan yang disengaja: pemohon kini boleh melihat permohonan **kab/kota-nya** (bukan hanya buatan akunnya), sesuai keputusan §8 no. 1. Dipakai di `PermohonanController`, `PermohonanDokumenController` (TODO tuntas), `ArsipController`, dan `BerkasController`.
- **B (Storage privat)** — Penyimpangan dari rencana:
  - Tidak memakai disk `local`, tetapi disk baru **`dokumen`** (root `storage/app/private`). `'public'` diganti `'dokumen'` secara mekanis di 15 file (69 titik), dan `storage_path('app/public/…')` diganti `storage_path('app/private/…')`. `ProfileController` tetap di disk `public`.
  - **Tanpa fallback disk public.** Fallback hanya menolong route baru, tidak menolong route unduh lama per modul. Sebagai gantinya, `berkas:pindah-ke-privat` dijalankan saat maintenance mode, sebelum aplikasi dibuka (lihat `docs/operasional/RUNBOOK.md`).
  - Command memindahkan **seluruh isi** `storage/app/public` kecuali `profile-photos/`, plus `public/sk-tim/*` → `sk-tim/*`. Ia tidak membaca kolom DB, sehingga file yatim pun ikut keluar dari folder publik. Tersedia `--dry-run` dan `--rollback`. Dry-run di lokal: 125 berkas + 8 SK Tim.
  - Kolom `*_nama_asli` (langkah 7) **belum** ditambahkan. `BerkasController` memakai `file_name` bila ada, selain itu nama file di disk.
  - `laporan_verifikasi` tidak punya kolom `file_laporan`, jadi tautan laporan verifikasi di view adalah kode mati (tidak pernah tampil). Tautannya tetap diarahkan ke route baru.
  - Route `berkas.show` menampilkan inline; tambah `?unduh=1` untuk mengunduh.
- **C (Provider WA)** — Seluruh jalur Twilio ternyata sudah mati: dispatch Job di-comment, `UndanganPelaksanaanNotification` tidak dipakai, dan Job membaca kolom `phone` yang tidak ada. Jadi kode cukup dihapus, tanpa migrasi ke Fonnte. `twilio/sdk` dihapus. Pesan sukses `SuratPemberitahuanController::send()` diperbaiki: sebelumnya mengklaim tidak ada penerima, padahal tidak ada pengiriman sama sekali. **Blok "legacy backup" tidak dihapus**, karena sebagian blok itu satu-satunya jalur notifikasi untuk penerima tertentu. Contoh di `ApprovalController`:
  - approve: service mengirim ke admin_peran, tim, pemohon; legacy juga ke superadmin;
  - reject: service hanya ke admin_peran; legacy ke admin_peran, superadmin, dan tim.

  Menghapusnya berarti mengubah penerima, sehingga dipindah ke item 2.10.
- **D (TODO)** — Notifikasi perpanjangan waktu ke Admin PERAN (database + Fonnte) lewat `PermohonanNotificationService::notifyPerpanjanganWaktuDiajukan()`. PDF laporan verifikasi memakai DomPDF (`resources/views/laporan-verifikasi/pdf.blade.php`).
- **E** — `docs/operasional/RUNBOOK.md` dibuat; bagian yang tidak ada di repo ditandai [UNKNOWN]. Uptime monitoring adalah layanan eksternal dan **belum dikonfigurasi**.
- `composer remove` melaporkan 2 advisory keamanan pada 1 paket yang sudah ada sebelumnya (`composer audit`).

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Ada tautan file di view yang terlewat → link rusak setelah file dipindah | Fallback disk `public` di `BerkasController`; grep `storage/` di `resources/views` harus 0 (kecuali foto profil) sebelum fallback dihapus |
| File di DB tidak ada di disk | Mode `--dry-run` melaporkan file hilang sebelum eksekusi |
| Pemohon kehilangan akses ke arsip yang selama ini dipakai | Sosialisasi perubahan kebijakan; aturan sudah diputuskan stakeholder |
| Pesan surat pemberitahuan berubah format saat pindah ke Fonnte | Test membandingkan isi pesan sebelum dan sesudah |

## Kriteria Selesai Fase 1

- [ ] Tidak ada URL `/storage/` untuk dokumen; semua unduhan lewat `BerkasController` dan policy
- [ ] Arsip dan permohonan difilter sesuai role dan kab/kota (dibuktikan test)
- [ ] Hanya `FonteService` yang tersisa; `twilio/sdk` terhapus; tidak ada notifikasi database ganda
- [ ] 3 TODO tuntas
- [ ] `docs/operasional/RUNBOOK.md` tersedia; uptime monitoring aktif
- [ ] Rilis ke server, dengan backup sebelum rilis dan command migrasi file dijalankan sesuai runbook
