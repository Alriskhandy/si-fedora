# Runbook Operasional — SI-FEDORA

Panduan operasional untuk developer/admin server. Bagian bertanda **[UNKNOWN]** belum terdokumentasi di repo dan perlu dilengkapi oleh penanggung jawab server.

## 1. Lingkungan

| Komponen | Nilai |
|---|---|
| Framework | Laravel 12, PHP 8.3 |
| Database | PostgreSQL |
| Penyimpanan dokumen | Disk `dokumen` → `storage/app/private` (privat, diakses lewat route `berkas.show` dan route unduh per modul) |
| Penyimpanan publik | Disk `public` → `storage/app/public`, hanya foto profil (`profile-photos/`) |
| Notifikasi WhatsApp | Fonnte (`FONNTE_TOKEN`) |
| Server, domain, cara akses (SSH/panel) | **[UNKNOWN]** |

## 2. Backup Manual (wajib sebelum setiap rilis)

```bash
# Database
pg_dump -h <host> -U <user> -Fc <database> > sifedora_$(date +%Y%m%d_%H%M).dump

# Berkas
tar czf sifedora_storage_$(date +%Y%m%d_%H%M).tar.gz storage/app public/sk-tim
```

Simpan hasil backup **di luar server aplikasi**. Lokasi penyimpanan backup: **[UNKNOWN]**. Strategi backup otomatis & uji restore ditunda (keputusan §8 no. 8, backlog Fase 5); sampai saat itu, backup manual ini wajib sebelum rilis yang menjalankan migration.

Restore database: `pg_restore -h <host> -U <user> -d <database> --clean <file>.dump`.

## 3. Deploy

```bash
php artisan down
git pull                          # atau mekanisme deploy yang dipakai: [UNKNOWN]
composer install --no-dev --optimize-autoloader
php artisan migrate --force
# Command sekali jalan untuk rilis ini (lihat §5)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## 4. Rollback

1. `php artisan down`
2. Kembalikan kode ke tag/commit sebelumnya.
3. Bila rilis menjalankan migration: `php artisan migrate:rollback --step=<n>`, atau restore backup DB.
4. Bila rilis menjalankan command pemindahan berkas: jalankan kebalikannya (lihat §5).
5. Bersihkan cache (`php artisan optimize:clear`), lalu `php artisan up`.

## 5. Command Sekali Jalan per Rilis

| Rilis | Command | Kebalikan |
|---|---|---|
| Fase 0 | `php artisan richtext:sanitize --dry-run`, periksa, lalu `php artisan richtext:sanitize` | Restore kolom dari backup DB |
| Fase 1 | `php artisan berkas:pindah-ke-privat --dry-run`, periksa, lalu `php artisan berkas:pindah-ke-privat` (dijalankan saat `php artisan down`, **sebelum** `up`) | `php artisan berkas:pindah-ke-privat --rollback`, hanya bersama rollback kode |
| Fase 2 | `php artisan permission:sync --reset` (**sekali**, pada rilis pertama Fase 2: mengganti 42 permission lama yang tidak pernah dipakai dengan katalog baru) | Restore tabel `permissions`, `role_has_permissions`, `model_has_permissions` dari backup |
| Fase 3 — PRD #6 | `php artisan permission:sync` (menambah `role.manage-protected` ke superadmin), lalu Superadmin membuka **Role & Hak Akses → admin_peran** dan mencentang "Atur hak akses role". Langkah manual ini perlu karena `permission:sync` tanpa `--reset` tidak memberikan permission lama ke role yang baru ditambahkan di katalog. | Superadmin menghapus centang yang sama |
| Fase 3 — Master OPD & aturan role | `php artisan migrate` (tabel `opd`, `opd_urusan`, kolom `users.opd_id`), lalu `php artisan permission:sync` (membuat role `opd` beserta pemetaan default-nya dan menambah `master.opd.manage` ke Superadmin & Admin PERAN) | `php artisan migrate:rollback --step=2` |
| Fase 3 — PRD #5, #2, #3 | `php artisan migrate` (kolom `permohonan_tahapan.tanggal_selesai` diisi dari `updated_at` untuk tahapan yang sudah selesai, tabel `permohonan_tahapan_riwayat`), lalu `php artisan permission:sync` (menambah `tahapan.update-tanggal-selesai` dan `hasil-fasilitasi.input-urusan`). Bila role `opd` sudah ada dari rilis sebelumnya, Admin menekan **Default** pada role `opd` di Role & Hak Akses agar hak akses fasilitator lainnya ikut diberikan. Umumkan ke tim bahwa hasil fasilitasi kini terkunci selama dinilai/disetujui Kaban | `php artisan migrate:rollback --step=2` |
| Fase 3 — Master OPD & urusan (kamus data) | `php artisan migrate` (struktur OPD & urusan, penanda `tampil_hasil_fasilitasi`, sinkronisasi awal urusan masukan OPD), lalu `php artisan db:seed --class=OpdSeeder` **kemudian** `php artisan db:seed --class=MasterUrusanSeeder`. Kedua seeder membaca `database/seeders/docs/*.csv`, hanya menambah baris baru dan mengisi kolom yang kosong (aman dijalankan ulang) | `php artisan migrate:rollback --step=4` (data OPD/urusan hasil seeder tetap ada; hapus manual bila perlu) |
| Fase 4 — Konsistensi data (B, C) | **Sebelum deploy**, pastikan tidak ada nilai status di luar enum (cast enum akan error 500 bila menemukannya). Kolom `hasil_fasilitasi.status_draft` tidak dijaga check constraint, jadi wajib dicek: `SELECT DISTINCT status_draft FROM hasil_fasilitasi;` → hanya boleh `NULL`, `draft`, `diajukan`, `menunggu_persetujuan_kaban`, `disetujui_kaban`, `ditolak_kaban`. Perbaiki nilai lain (mis. string kosong → `NULL`) sebelum lanjut. Lalu `php artisan migrate` (rename `permohonan.kab_kota_id` → `kabupaten_kota_id` beserta index & FK) | `php artisan migrate:rollback --step=1` (hanya bersama rollback kode, karena kode baru memakai nama kolom baru) |
| Fase 6 — Tahap 1 (format dokumen dinamis) | `php artisan migrate` (tabel `format_dokumen`, `format_blok`, kolom `hasil_fasilitasi.format_dokumen_id`) → `php artisan db:seed --class=FormatDokumenSeeder` (format awal aktif untuk setiap jenis dokumen; aman diulang) → `php artisan hasil-fasilitasi:isi-format --dry-run`, periksa, lalu tanpa `--dry-run` → `php artisan permission:sync` (menambah `master.format-dokumen.manage` ke Superadmin & Admin PERAN). Uji: koordinator membuat ulang draft satu permohonan dan membandingkan isinya dengan draft sebelumnya | `php artisan migrate:rollback --step=1` bersama rollback kode |
| Fase 6 — Tahap 2 (bagian masukan dinamis) | Setelah langkah Tahap 1. `php artisan migrate` (tabel `bagian_masukan`, `hasil_fasilitasi_masukan`; empat bagian awal dibuat untuk setiap format; blok dihubungkan ke bagian) → `php artisan hasil-fasilitasi:migrasi-masukan --dry-run` → bandingkan kolom "Total baris" dengan "Akan disalin" → jalankan tanpa `--dry-run` (harus exit 0; bila ada baris tanpa format, jalankan `hasil-fasilitasi:isi-format` dulu) → uji halaman input, detail, approval Kaban, dan buat ulang satu draft. Tabel masukan lama **tidak** dihapus pada rilis ini | `php artisan migrate:rollback --step=1` bersama rollback kode (tabel lama masih utuh) |
| Fase 6 — Tahap 3 (kolom tambahan & pengisi per bagian) | `php artisan migrate` (kolom `kolom_tambahan`, `role_pengisi`, `tampil_untuk`; nilai awal meniru perilaku lama; role pemegang `hasil-fasilitasi.input-urusan`/`hasil-fasilitasi.input` mendapat `hasil-fasilitasi.input-masukan`/`hasil-fasilitasi.kelola-draft`) → **lalu** `php artisan permission:sync` (menghapus dua permission lama). Urutan ini penting: sync lebih dulu akan menghapus permission lama sebelum haknya dipindahkan. Uji: fasilitator dan OPD membuka halaman input; OPD hanya melihat tab Urusan | `php artisan migrate:rollback --step=1` (memulihkan permission lama dari yang baru) lalu deploy kode lama dan `permission:sync` |
| Setiap rilis berikutnya | `php artisan permission:sync` (tanpa `--reset`: hanya menambah permission baru ke role default, tidak mencabut perubahan yang dibuat lewat UI) | — |

Cache permission Spatie otomatis dibersihkan oleh `permission:sync`. Bila mengubah pemetaan langsung di DB, jalankan `php artisan permission:cache-reset`.

Setelah pemindahan berkas Fase 1, verifikasi bahwa dokumen tidak lagi bisa diakses publik:

```bash
curl -I https://<domain>/storage/permohonan_dokumen/<id>/<nama-file>.pdf   # harus 404
curl -I https://<domain>/sk-tim/<nama-file>.pdf                            # harus 404
```

## 6. Scheduler & Queue

Scheduler (didefinisikan di `bootstrap/app.php`):

| Command | Jadwal |
|---|---|
| `otp:cleanup` | Setiap jam |
| `permohonan:remind-deadline` | Setiap hari 08:00 |

Cron di server:

```cron
* * * * * cd /path/ke/si-fedora && php artisan schedule:run >> /dev/null 2>&1
```

Queue: notifikasi WhatsApp saat ini dikirim **sinkron** (lihat item 2.10). Worker queue belum dibutuhkan; status cron di server: **[UNKNOWN]**.

## 7. Rotasi Token Fonnte

1. Buat token baru di dashboard Fonnte.
2. Ganti `FONNTE_TOKEN` di `.env` server.
3. `php artisan config:cache`.
4. Uji dengan mengirim notifikasi (mis. reminder) ke nomor admin, lalu cek `storage/logs`.
5. Cabut token lama di dashboard Fonnte.

## 8. Monitoring

- **Uptime:** endpoint `GET /up` mengembalikan 200 bila aplikasi berjalan. Daftarkan ke layanan uptime monitoring dengan alert ke WA/email admin. Status: **belum dikonfigurasi** (layanan dan penerima alert perlu ditentukan).
- **Log:** `storage/logs/laravel.log`; Log Viewer khusus superadmin.
