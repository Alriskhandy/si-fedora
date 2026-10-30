# Fase 4 — Konsistensi Data

> **Status: ✅ kode selesai, belum dirilis.** A (24 & 27 Okt 2026), B dan C (27 Okt 2026). Lihat catatan pelaksanaan tiap butir. Langkah deploy di [RUNBOOK §5](../operasional/RUNBOOK.md#5-command-sekali-jalan-per-rilis).

**Indikatif:** setelah Fase 3 · **Item:** 2.6, 2.7 · **Menutup:** temuan §2 no. 5–6

## Tujuan

Menjalankan Fase 1 [`file-rancangan/redesign-database.md`](../../file-rancangan/redesign-database.md) dan menambah type-safety status, tanpa perubahan perilaku.

## Prasyarat

- [ ] Fase 3 selesai (agar rename kolom tidak bertabrakan dengan fitur PRD yang sedang dikerjakan)
- [ ] Test (a)–(e) Fase 2 hijau sebagai jaring pengaman
- [ ] Backup DB sebelum rilis migration

---

### A. Bersihkan kode orphan *(item 2.6, bagian 1)* — ✅ selesai 24 Okt 2026

> Dikerjakan saat perapian model. Selain butir di bawah, juga dihapus: relasi `Permohonan::createdBy/updatedBy/verifikator`, `User::permohonanCreated/permohonanAsVerifikator`, `PersyaratanDokumen::permohonanDokumen`, aksesor `PermohonanDokumen::file_url`, dan command `RunWorkflowTest`. Relasi `KabupatenKota::permohonan` diperbaiki ke `kab_kota_id`.

Mengikuti `redesign-database.md` §Fase 1 butir 1:
1. Hapus class `Evaluasi` di `app/Models/PermohonanDokumen.php` (±baris 69–128), `User::evaluasi()`, dan referensi di `app/Console/Commands/RunWorkflowTest.php`.
2. Hapus relasi `koordinator` di `app/Models/PermohonanTahapan.php:42` (kolom `koordinator_id` tidak ada). Cek pemakaiannya dulu di view/controller.
3. Bersihkan `TimFasilitasiAssignment`: hapus fillable/cast/scope yang merujuk kolom yang tidak ada (`peran`, `ditugaskan_oleh`, `ditugaskan_pada`, `scopeByPeran/Ketua/Anggota`).

Setiap penghapusan didahului grep di `app/`, `routes/`, `resources/views/`. Bila ternyata dipakai, hentikan dan laporkan, karena mungkin yang dibutuhkan adalah kolom baru, bukan penghapusan kode.

#### Lanjutan 27 Okt 2026 — audit model terhadap skema

Audit otomatis seluruh model (`$fillable`, cast, dan foreign key relasi dibandingkan dengan kolom di database) menemukan field fiktif lain di luar daftar di atas. Semuanya sudah dibersihkan:

| Model | Field yang tidak ada di tabel | Dampak sebelum diperbaiki | Tindakan |
|---|---|---|---|
| `KabupatenKota` | `provinsi`, `kepala_daerah`, `email`, `telepon`, `alamat` | **Bug:** tambah dan ubah Kab/Kota di master selalu gagal (500, `column "kepala_daerah" does not exist`), karena controller mengirim kolom tersebut meski form tidak punya field-nya | Dihapus dari model dan dari validasi/penyimpanan `KabupatenKotaController`; test `KabupatenKotaControllerTest` ditambahkan |
| `MasterTahapan` | `kode`, `deskripsi` | Tidak ada (controller hanya menyimpan nama & urutan) | Dihapus dari fillable |
| `PelaksanaanCatatan` | `catatan`, `peserta` (+cast array), `hasil_pembahasan`, `dokumentasi` | Tidak ada (model tidak dipakai selain relasi) | Fillable diselaraskan dengan kolom tabel (`berita_acara_file`, `notulensi_file`, `dokumentasi_file`, `absensi_file`, `keterangan`) |
| `PersyaratanDokumen` | `jenis_dokumen_id`, `is_required` | Tidak ada (model tidak dipakai) | Diselaraskan: `is_wajib` (+cast), `template_file` |
| `TindakLanjut` | `jenis_tindak_lanjut`, `file_tindak_lanjut`, `is_submitted`, `submitted_at`, `batas_waktu`, `catatan_admin` | Blok "Jenis Tindak Lanjut" di `tahapan/tindak-lanjut.blade.php` tidak pernah tampil | Dihapus dari model; blok view yang mati dihapus |
| `User` | `last_login_at` | `updateLastLogin()` akan error bila dipanggil, tetapi tidak dipanggil di mana pun | Field, cast, dan method dihapus |

Bila di kemudian hari data kontak Kab/Kota (kepala daerah, email, telepon, alamat) dibutuhkan, yang diperlukan adalah migration kolom baru beserta field form-nya.

### B. Rename `kab_kota_id` → `kabupaten_kota_id` *(item 2.6, bagian 2)* — ✅ selesai 27 Okt 2026

1. Migration `rename_kab_kota_id_on_permohonan`: `renameColumn` di `up()` dan kebalikannya di `down()`. Index/FK ikut diperiksa.
2. `app/Models/Permohonan.php`: `$fillable`, accessor `getKabupatenKotaIdAttribute()` (dihapus karena tidak diperlukan lagi), relasi `kabupatenKota()`, dan scope.
3. Ganti semua referensi (±38, dicek dengan `grep -rn kab_kota_id app resources database`), termasuk `scopeVisibleTo` dan Policy dari Fase 1 serta kode PRD dari Fase 3.
4. Factory `PermohonanFactory` dan seeder.

**Verifikasi:** `grep -rn kab_kota_id app resources database/seeders database/factories` → 0 (di luar migration lama); seluruh test hijau.

> **Catatan pelaksanaan (27 Okt 2026):**
> - Migration `2026_10_27_003223_rename_kab_kota_id_on_permohonan_table` mengganti kolom, index `permohonan_kabupaten_kota_id_tahun_index`, dan constraint FK `permohonan_kabupaten_kota_id_foreign` (rename constraint khusus PostgreSQL). `down()` diuji dengan rollback lalu migrate ulang.
> - 46 referensi diganti di 20 file (model, policy, 9 controller, service notifikasi, view tahapan hasil, factory, 6 test). Accessor penyambung `Permohonan::getKabupatenKotaIdAttribute()` dihapus. Relasi `Permohonan::kabupatenKota()` dan `KabupatenKota::permohonan()` kini memakai foreign key bawaan.
> - Payload notifikasi baru memakai kunci `kabupaten_kota_id`; data notifikasi lama yang tersimpan tetap memakai kunci lama (tidak dibaca kode mana pun).
> - Verifikasi: grep → 0 di luar migration lama; seluruh test hijau.

### C. PHP backed Enum untuk status *(item 2.7)* — ✅ selesai 27 Okt 2026 (status utama)

1. Buat `app/Enums/StatusPermohonan` (`belum`, `proses`, `revisi`, `selesai`), `StatusDokumen` (`menunggu`, `diterima`, `ditolak`), dan enum status lain sesuai inventaris string status di controller/view.
2. Cast di model (`casts()`), tanpa perubahan DB.
3. Ganti string literal status di controller, query, dan view secara bertahap per modul. Label tampilan disediakan lewat method `label()` di enum.

**Test:** test yang ada tetap hijau; tambahkan unit test kecil untuk `label()` bila logikanya tidak trivial.

> **Catatan pelaksanaan (27 Okt 2026):**
> - Enum di `app/Enums`: `StatusPermohonan` (dipakai `permohonan.status_akhir` **dan** `permohonan_tahapan.status`, karena nilainya sama), `StatusHasilFasilitasi`, `StatusJadwal`, `StatusDokumen`, `StatusVerifikasiDokumen`. Label/badge yang sebelumnya tersebar di model (`Permohonan::getStatusLabelAttribute`, `JadwalFasilitasi::getStatusLabelAttribute`, `HasilFasilitasi::statusLabel`, konstanta `HasilFasilitasi::STATUS_TERKUNCI` dan `JadwalFasilitasi::STATUS_*`) dipindah ke enum.
> - Perbandingan string di 6 file PHP dan 14 view diganti kasus enum; `match`, `strtoupper`/`ucfirst`, indeks array, dan data dashboard memakai `->value`. Aturan pemakaian dicatat di [REFERENSI-KOMPONEN §Enums](../pengembang/REFERENSI-KOMPONEN.md#enums).
> - **Bug yang terungkap:** `JadwalFasilitasiController::update` membandingkan status lama dengan `'published'`. Dengan enum perbandingan itu akan selalu benar dan notifikasi WhatsApp terkirim ulang setiap kali jadwal published disimpan; diperbaiki dengan menyimpan `->value` di data lama. Ditutup test.
> - Test baru `StatusEnumTest` (13 test): halaman permohonan & tahapan untuk keempat status, dashboard keenam role, halaman jadwal, dan notifikasi publish jadwal. Test lama yang membandingkan string diubah ke kasus enum.
> - **Sebelum rilis:** cek nilai `hasil_fasilitasi.status_draft` di server (tidak ada check constraint; nilai di luar enum akan error). Lihat runbook.
> - **Sisa (belum di-cast):** `dokumen_verifikasi_detail.status`, `laporan_verifikasi.status_kelengkapan`, `surat_pemberitahuan.status`, `undangan_pelaksanaan.status`. Bisa dikerjakan dengan pola yang sama bila modulnya disentuh.

---

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Rename kolom memecah query raw atau view yang terlewat grep | Grep di `resources/views` dan query `DB::raw`/`selectRaw`; uji manual alur permohonan di staging |
| Enum cast memecah perbandingan string (`== 'proses'`) | Konversi per modul dengan test; cari pola `status_akhir ==` dan `->where('status` |

## Kriteria Selesai Fase 4

- [x] Tidak ada class, relasi, atau field yang merujuk kolom yang tidak ada *(diverifikasi audit model 27 Okt 2026)*
- [x] `kab_kota_id` tidak lagi dipakai di kode aplikasi
- [x] Status utama memakai enum
- [ ] Seluruh test hijau; rilis dengan backup
