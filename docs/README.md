# Dokumentasi SI-FEDORA

Mulai dari tabel di bawah sesuai kebutuhan Anda.

| Saya ingin… | Baca |
|---|---|
| Memahami cara kerja kode dan aturan mengubahnya | [Panduan Pengembang](pengembang/PANDUAN-PENGEMBANG.md) |
| Mencari route, controller, middleware, atau request yang menangani suatu fitur | [Referensi HTTP](pengembang/REFERENSI-HTTP.md) |
| Mencari command, policy, service, provider, atau cast | [Referensi Komponen](pengembang/REFERENSI-KOMPONEN.md) |
| Mencari file view suatu halaman, atau membuat halaman baru | [Referensi View](pengembang/REFERENSI-VIEW.md) |
| Deploy, rollback, backup, atau menjalankan command rilis | [Runbook](operasional/RUNBOOK.md) |
| Tahu siapa boleh melakukan apa | [Jenis Pengguna](produk/JENIS-PENGGUNA.md) |
| Tahu fitur apa yang akan dikembangkan | [PRD](produk/PRD.md) |
| Melihat rencana kerja dan progresnya | [Plan Implementasi](implementasi/README.md) |
| Memahami latar belakang keputusan perbaikan | [Rekomendasi Pengembangan](analisis/REKOMENDASI-PENGEMBANGAN.md) |

## Struktur

```
docs/
├── README.md                       ← halaman ini
├── pengembang/
│   ├── PANDUAN-PENGEMBANG.md         hidup  — mekanisme inti & cara mengubahnya
│   ├── REFERENSI-HTTP.md             hidup  — indeks route, controller, middleware, form request
│   ├── REFERENSI-KOMPONEN.md         hidup  — indeks command, policy, provider, service, cast
│   └── REFERENSI-VIEW.md             hidup  — struktur resources/views, layout, aturan view
├── operasional/RUNBOOK.md            hidup  — prosedur server
├── produk/
│   ├── PRD.md                        hidup  — kebutuhan pengembangan dari stakeholder
│   └── JENIS-PENGGUNA.md             hidup  — ringkasan hak akses per role
├── implementasi/                     hidup  — plan per fase (0–6) + catatan pelaksanaan
└── analisis/
    ├── ANALISIS-KONDISI-EKSISTING.md arsip  — snapshot 21 Sep 2026
    ├── REKOMENDASI-PENGEMBANGAN.md   arsip  — snapshot 17–23 Okt 2026 + keputusan stakeholder
    └── ANALISIS-MASUKAN-DINAMIS.md   arsip  — snapshot 27 Okt 2026: format masukan & dokumen hasil berbasis data
```

- **Hidup** = harus selalu sesuai kondisi kode saat ini.
- **Arsip** = rekaman pada tanggal tertentu. Tidak diperbarui isinya, hanya diberi catatan status di bagian atas.

## Aturan Perawatan

Perbarui dokumen **di commit yang sama** dengan perubahan kodenya:

| Bila Anda mengubah… | Perbarui |
|---|---|
| `config/permissions.php` | `produk/JENIS-PENGGUNA.md` (dan snapshot route, lihat Panduan Pengembang §2) |
| Prosedur deploy, command sekali jalan, scheduler, atau env | `operasional/RUNBOOK.md` |
| Mekanisme inti (otorisasi, penyimpanan berkas, sanitasi, notifikasi, test) | `pengembang/PANDUAN-PENGEMBANG.md` |
| Menambah/menghapus file route, controller, middleware, atau form request, atau mengubah docblock kelasnya | `pengembang/REFERENSI-HTTP.md` |
| Menambah/menghapus command, policy, provider, service, cast, atau komponen view | `pengembang/REFERENSI-KOMPONEN.md` |
| Menambah folder di `resources/views`, mengubah layout/partial, atau memindahkan view | `pengembang/REFERENSI-VIEW.md` |
| Menyelesaikan item rencana | Status di `implementasi/README.md` dan "Catatan Pelaksanaan" di dokumen fasenya |
| Keputusan stakeholder baru | `analisis/REKOMENDASI-PENGEMBANGAN.md` §8 |

Gaya penulisan:

- Bahasa Indonesia. Nama file, kelas, kolom, dan command ditulis dalam `backtick`.
- Rujuk lokasi kode dengan nama file atau kelas, bukan nomor baris. Nomor baris cepat usang.
- Gunakan tautan relatif antar-dokumen.
- Tandai hal yang belum diketahui dengan **[UNKNOWN]**.

> Catatan: folder `docs/` saat ini tercantum di `.gitignore`, sehingga tidak ikut ke repository. Bila dokumentasi ini dipakai bersama tim, hapus baris `/docs` dari `.gitignore`.
