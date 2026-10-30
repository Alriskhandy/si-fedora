# Plan Implementasi — SI-FEDORA

Rencana eksekusi teknis untuk roadmap di [`REKOMENDASI-PENGEMBANGAN.md` §6](../analisis/REKOMENDASI-PENGEMBANGAN.md#6-roadmap-bertahap). Nomor item (1.1, 2.3, C1, dst.) merujuk ke dokumen tersebut; butir PRD merujuk ke [`PRD.md`](../produk/PRD.md).

## Daftar Fase

| Fase | Dokumen | Indikatif | Isi | Gerbang masuk |
|---|---|---|---|---|
| 0 | [Pengamanan](FASE-0-PENGAMANAN.md) | Minggu 1–2 | 1.1, 1.3, 1.4, 1.6, 2.1 | Backup DB & file dibuat manual |
| 1 | [Tutup Celah Akses](FASE-1-TUTUP-CELAH-AKSES.md) | Minggu 3–5 | 1.2, 1.5, 2.5, 3.1, 5.1, 5.2 | Fase 0 selesai |
| 2 | [Fondasi PRD](FASE-2-FONDASI-PRD.md) | Minggu 6–9 | 2.2, 2.3, 2.4, 2.9 | Fase 1 selesai |
| 3 | [Eksekusi PRD](FASE-3-EKSEKUSI-PRD.md) | Minggu 10–16 | PRD #6 → #5 → #2 → #3 → #1 | Fase 2 selesai **dan** keputusan §8 no. 5–7 sudah dijawab (✅ 24 Okt 2026) |
| 4 | [Konsistensi Data](FASE-4-KONSISTENSI-DATA.md) | Setelah PRD | 2.6, 2.7 | Fase 3 selesai |
| 5 | [Backlog](FASE-5-BACKLOG.md) | Menyesuaikan | 2.10, 3.2–3.7, 3.9, 4.1, 5.3, 5.4 | Per item, lihat dokumen |
| 6 | [Format Hasil Dinamis](FASE-6-FORMAT-HASIL-DINAMIS.md) | Tahap 1 kapan saja; Tahap 2–3 setelah format baru | PRD #4, 3.8, 2.8 | Tahap 1: tidak ada; Tahap 2: format baru dari stakeholder (D5) |

Fase 0–2 adalah **perbaikan wajib sebelum PRD**. Jangan memulai butir PRD sebelum kriteria selesai Fase 2 terpenuhi, karena PRD #2 (role OPD) dan #6 (manajemen permission) dibangun di atas otorisasi berbasis permission (2.3).

## Konvensi Kerja

- **Branch**: satu branch per item, dengan nama `fase-<n>/<nomor-item>-<slug>` (mis. `fase-0/1.1-isolasi-db-test`). Merge ke `main` lewat PR.
- **Satu item = satu PR.** Item besar (1.2, 2.3, PRD #1) boleh dipecah per modul, asalkan setiap PR bisa di-deploy sendiri.
- **Test dulu.** Setiap perubahan perilaku disertai test feature (PHPUnit, `php artisan make:test --phpunit`). Jalankan test yang terdampak dengan `php artisan test --compact --filter=…` sebelum PR.
- **Format kode**: `vendor/bin/pint --dirty --format agent` sebelum commit.
- **Migrasi**: setiap migration punya `down()` yang benar. Perubahan kolom wajib menyertakan semua atribut kolom yang sudah ada.
- **Dependensi**: penambahan atau penghapusan paket Composer/NPM butuh persetujuan. Yang sudah disetujui: penghapusan `twilio/sdk` (§8 no. 4).
- **Deploy**: setiap fase diakhiri satu rilis ke server, mengikuti runbook (dibuat di Fase 1, item 5.1). Sebelum rilis yang menyentuh data atau file, **ambil backup DB dan folder `storage/app`**.

## Definition of Done (berlaku untuk semua item)

- [ ] Kode sudah di-review dan di-merge lewat PR
- [ ] Test baru/terdampak lulus (`php artisan test`; CI ditunda, jalankan manual sebelum merge)
- [ ] Pint bersih
- [ ] Tidak ada nomor baris di dokumen ini yang dirujuk tanpa divalidasi ulang terhadap kode terbaru
- [ ] Langkah deploy khusus (command sekali jalan, seeder, cache reset) dicatat di PR dan di runbook

## Pelacak Status

| Fase | Status | Mulai | Selesai | Catatan |
|---|---|---|---|---|
| 0 | Kode selesai, belum dirilis | 24 Okt 2026 | | Lihat "Catatan Pelaksanaan" di dokumen Fase 0 |
| 1 | Kode selesai, belum dirilis | 24 Okt 2026 | | Lihat "Catatan Pelaksanaan" di dokumen Fase 1; uptime monitoring belum dikonfigurasi |
| 2 | Kode selesai, belum dirilis | 24 Okt 2026 | | Lihat "Catatan Pelaksanaan" di dokumen Fase 2; CI ditunda ke Fase 5 |
| 3 | Kode selesai kecuali PRD #1, belum dirilis | 24 Okt 2026 | | PRD #6, #5, #2, #3 kode selesai (25 Okt 2026). PRD #1 (UI/UX) ditunda sampai fitur PRD selesai (K10) |
| 4 | Kode selesai, belum dirilis | 24 Okt 2026 | | A, B, C selesai 27 Okt 2026. Bug yang ikut diperbaiki: tambah/ubah Kab/Kota (500) dan potensi kirim ulang WA saat jadwal published disimpan. Cek `status_draft` di server sebelum rilis (runbook §5). Sisa status non-utama belum di-cast |
| 5 | Belum mulai | | | Termasuk kebutuhan baru "Penghapusan Penugasan Tim" (24 Okt 2026), dicatat di bagian akhir dokumen Fase 5 |
| 6 | Tahap 1–3 kode selesai, belum dirilis | 27 Okt 2026 | | Format, bagian masukan, kolom tambahan, serta pengisi/pembaca per bagian diatur Admin di master "Format Dokumen Hasil". Permission input diganti `input-masukan` & `kelola-draft` (urutan deploy: migrate lalu permission:sync). Sisa: 2.8 (hapus tabel lama) setelah migrasi data di server terverifikasi |
