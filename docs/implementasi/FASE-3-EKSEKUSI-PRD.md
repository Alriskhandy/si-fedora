# Fase 3 — Eksekusi PRD

> **Status: 🔄 kode PRD #6, #5, #2, #3 selesai (25 Okt 2026), belum dirilis.** Semua keputusan terbuka K1–K10 sudah dijawab. PRD #1 (UI/UX) ditunda sampai seluruh fitur PRD lain selesai (K10). Detail per butir di "Catatan Pelaksanaan".

**Indikatif:** Minggu 10–16 · **Butir PRD:** #6 → #5 → #2 → #3 → #1 · **PRD #4 ditunda** (format hasil fasilitasi baru belum tersedia)

## Tujuan

Mengerjakan seluruh butir [`PRD.md`](../produk/PRD.md) di atas fondasi Fase 0–2.

## Prasyarat

- [x] Fase 2 selesai di kode: akses berbasis permission, seeder idempoten (CI ditunda ke Fase 5)
- [x] Keputusan stakeholder no. 5–8 terjawab (24 Okt 2026)
- [x] Konfirmasi turunan (24 Okt 2026): masukan OPD hanya pada urusan pemerintahan, batas ubah sampai diajukan ke Kaban, tanpa penugasan OPD, aturan role pengguna
- [x] Keputusan teknis pemilik proyek (24 Okt 2026): role OPD termasuk tim fasilitator, dengan pembatasan input hanya pada bagian urusan pemerintahan; selebihnya sama seperti fasilitator. Masukan OPD ikut tercetak di dokumen hasil; kunci "sampai diajukan ke Kaban" berlaku untuk OPD **dan** fasilitator
- [x] Keputusan terbuka K1–K10 dijawab (25 Okt 2026) — lihat [Keputusan Terbuka](#keputusan-terbuka-sebelum-lanjut)
- [ ] Desain UI/UX (referensi visual atau mockup) — **ditunda** (K10): dibuat setelah semua fitur PRD selesai; memblokir PRD #1

## Keputusan Stakeholder yang Berlaku

| # | Keputusan (24 Okt 2026) | Tafsiran untuk implementasi |
|---|---|---|
| 5 | OPD yang mendapat akun adalah OPD **provinsi / mitra / teknis** yang **ditentukan Admin** untuk berpartisipasi memberi masukan terhadap dokumen perencanaan **di tahap hasil fasilitasi**. *Konfirmasi:* masukan **hanya pada urusan pemerintahan**; boleh diubah **sampai hasil fasilitasi diajukan ke Kaban**; **tanpa penugasan OPD** — OPD memberi masukan pada semua permohonan, terbatas pada urusan pemerintahan bidangnya; OPD menjadi **master data** untuk pembuatan akun oleh Admin. *Klarifikasi 25 Okt 2026:* semua OPD adalah **OPD di lingkungan Provinsi** | Master OPD (tanpa kolom jenis) dengan daftar urusan bidangnya; akun ber-role `opd` terhubung ke satu OPD. "Ditentukan Admin" = Admin mendaftarkan OPD dan membuatkan akunnya. OPD melihat semua permohonan; masukan per `hasil_fasilitasi_urusan` hanya untuk urusan bidang OPD-nya |
| 6 | "Tanggal hasil" = **perubahan tanggal selesai dan riwayatnya pada setiap tahapan permohonan**, untuk mengakomodasi penyesuaian tanggal selesai secara manual oleh Admin | PRD #5 bukan lagi kolom tanggal di `hasil_fasilitasi`, melainkan kolom tanggal selesai per tahapan yang bisa disesuaikan Admin, ditambah tabel riwayat perubahan. Tidak ada larangan ubah setelah disetujui Kaban; kontrolnya lewat riwayat & alasan wajib |
| 7 | Admin **tidak dapat menambah role baru** (hanya Superadmin); Admin **hanya dapat mengelola role selain superadmin**. *Konfirmasi:* Admin tidak bisa menambah pengguna dengan role `superadmin`, `kaban`, `admin_peran`, begitu juga mengubah rolenya; akses penuh hanya Superadmin. Di halaman Role & Hak Akses dan Katalog, Admin tidak melihat teks superadmin; checklist hanya menampilkan nama hak akses | Admin mengatur hak akses semua role kecuali `superadmin`. Di manajemen pengguna, role `superadmin`/`kaban`/`admin_peran` hanya bisa diberikan atau diubah Superadmin; Admin tetap bisa mengubah data (nama, email, kata sandi) akun `kaban`/`admin_peran` tanpa mengubah rolenya |
| Teknis | *Keputusan teknis pemilik proyek:* OPD / pengguna ber-role `opd` **termasuk tim fasilitator** (fasilitator dengan pembatasan input masukan hanya pada bagian **urusan pemerintahan**); selebihnya **sama seperti role fasilitator**. *Konfirmasi:* masukan OPD **ikut tercetak** di dokumen hasil fasilitasi seperti masukan fasilitator; kunci ubah masukan setelah diajukan ke Kaban **berlaku juga untuk fasilitator** | Masukan OPD memakai struktur input fasilitator yang sudah ada (`hasil_fasilitasi_urusan`, kolom `user_id`), **bukan** tabel terpisah. Hak akses `opd` = hak akses `fasilitator`, kecuali: input hanya urusan (tanpa sistematika/bab, form, rekomendasi) dan hanya urusan bidang OPD-nya; lihat permohonan lewat `permohonan.view-all` karena OPD tanpa penugasan. Masukan urusan dari siapa pun (OPD maupun fasilitator) terkunci selama hasil menunggu persetujuan atau sudah disetujui Kaban, dan terbuka lagi bila ditolak Kaban |
| 8 | Strategi backup & uji restore **ditunda**, dikembangkan nanti | Backup otomatis/DR tetap di backlog Fase 5. Backup manual di runbook tetap dianjurkan sebelum rilis yang menjalankan migration |

## Keputusan Terbuka Sebelum Lanjut

Didata dan dijawab 25 Okt 2026. K3: tanggal selesai data lama diisi dari `updated_at`. *Diubah 26 Okt 2026:* tidak ada penanda "perkiraan" maupun konfirmasi manual; tanggal selesai diisi sistem dan setiap perubahan oleh Admin dicatat di riwayat.

| # | Pertanyaan | Butir PRD | Menghambat? | Usulan default | Keputusan |
|---|---|---|---|---|---|
| K1 | **Siapa yang boleh melihat riwayat perubahan tanggal selesai/batas waktu tahapan?** | #5 | Ya | Semua yang boleh melihat permohonan, termasuk pemohon, agar perubahan tanggal transparan bagi kab/kota | Sesuai usulan default *Diubah 26 Okt 2026:* panel tanggal selesai & riwayat hanya untuk Admin PERAN dan Superadmin |
| K2 | **Aturan tanggal selesai yang diubah Admin:** cukup "tidak di masa depan dan tidak sebelum permohonan dibuat", atau wajib urut (tidak lebih awal dari tanggal selesai tahapan sebelumnya)? | #5 | Ya | Wajib urut terhadap tahapan sebelum dan sesudahnya yang sudah selesai, agar progress tracker tidak menampilkan urutan tanggal yang mustahil | Sesuai Usulan |
| K3 | **Data lama:** tanggal selesai tahapan yang sudah selesai diisi dari waktu terakhir baris diubah (`updated_at`), yang bisa tidak akurat. Diisi begitu, atau dikosongkan sampai Admin mengisinya? | #5 | Ya | Diisi dari `updated_at` (tampilan tidak kosong), lalu Admin mengoreksi yang keliru lewat fitur ini; koreksi tercatat di riwayat | sesuai usulan, tidak perlu catat koreksi di riwayat |
| K4 | **Apakah mengubah tanggal selesai ikut menggeser batas waktu tahapan berikutnya?** | #5 | Tidak | Tidak; batas waktu tetap diatur terpisah lewat fitur ubah batas waktu yang sudah ada | sesuai usulan|
| K5 | **Kunci saat diajukan ke Kaban untuk bagian selain urusan** (sistematika/bab, form, rekomendasi): ikut dikunci, atau hanya bagian urusan? | #2 | Ya | Semua bagian ikut dikunci, agar dokumen tidak berubah selama dinilai Kaban; terbuka lagi bila Kaban menolak | sesuai usulan |
| K6 | **Cakupan unduhan OPD:** semua dokumen permohonan (ikut hak lihat semua permohonan) atau hanya dokumen hasil fasilitasi? | #2 | Tidak | Sama seperti fasilitator: semua dokumen pada permohonan yang boleh dilihat | sesuai usulan |
| K7 | **OPD yang belum diberi urusan bidang di master OPD:** tidak bisa memberi masukan sama sekali, atau boleh pada semua urusan? | #2 | Tidak | Tidak bisa memberi masukan; Admin wajib melengkapi urusan bidang OPD | sesuai usulan |
| K8 | **Penanda sumber di dokumen cetak:** perlukah nama OPD dicantumkan per masukan di Word/PDF, atau cukup di layar? | #2, #3 | Tidak | Cukup di layar; format cetak tetap seperti sekarang sampai format baru hasil fasilitasi (PRD #4) | cukup di layar, tampil nama dan dari opd mana |
| K9 | **Halaman daftar seluruh masukan OPD** (filter kab/kota, tahun, jenis dokumen, OPD) dibuat di Fase 3? | #3 | Tidak | Tidak; cukup penanda sumber, filter, dan badge di halaman approval/hasil | sesuai usulan |
| K10 | **Referensi visual atau mockup UI/UX**: siapa yang menyediakan dan kapan? | #1 | Ya | — (prasyarat; tanpa ini redesign tidak dimulai) | di skip, akan di buat setelah semua fitur PRD ini selesai |

Di luar Fase 3: pertanyaan penghapusan penugasan tim tercatat di [Fase 5](FASE-5-BACKLOG.md#penghapusan-penugasan-tim-kebutuhan-baru-24-okt-2026). Pertanyaan itu tidak menghambat Fase 3, karena OPD memakai cabang sementara.

## Pemetaan Fitur Terdampak

Status: ✅ sudah disesuaikan · 🔧 perlu diubah saat butir PRD dikerjakan · ➕ fitur/komponen baru · ❓ perlu konfirmasi.

### Keputusan no. 5 — Akun OPD (PRD #2, #3)

| Fitur / komponen | Dampak | Status |
|---|---|---|
| Master data OPD | Tabel `opd` sesuai kamus data OPD (kode OPD, kode wilayah, nama, singkatan, jenis, rumpun urusan, status, tanggal pemutakhiran, urusan diampu, OPD induk) + `opd_urusan` (urusan bidang OPD); `OpdController`, menu Master Data → OPD, permission `master.opd.manage` | ✅ |
| Manajemen pengguna (`UserController`, `StoreUserRequest`/`UpdateUserRequest`, form pengguna) | Role `opd` bisa diberikan Admin; field OPD wajib (OPD aktif) bila role `opd`; kolom `users.opd_id` | ✅ |
| Katalog permission & role (`config/permissions.php`) | Role `opd` = hak akses fasilitator kecuali `permohonan.view-all` (bukan `view-assigned`) dan `hasil-fasilitasi.input-urusan` (bukan `hasil-fasilitasi.input`). Permission baru `hasil-fasilitasi.input-urusan` juga dimiliki fasilitator | ✅ |
| `permission:sync` | Role baru di katalog langsung mendapat seluruh pemetaan default-nya (sebelumnya hanya permission baru) | ✅ |
| Hak lihat permohonan (`PermohonanPolicy::view`, scope `Permohonan::visibleTo`) | OPD melihat semua permohonan lewat `permohonan.view-all`; tidak perlu cabang baru | ✅ |
| Arsip & unduh berkas (`ArsipController`, `BerkasController`) | Ikut hak lihat permohonan → OPD bisa melihat arsip dan mengunduh dokumen semua permohonan | ✅ (otomatis) — ❓ K6 |
| Progress tracker (`permohonan/show`, `Permohonan::getProgressSteps()`) | Tampil untuk OPD; aksi tetap dijaga permission masing-masing | ✅ |
| Halaman hasil fasilitasi (`tahapan/hasil.blade.php`, `hasil-fasilitasi/create`, `HasilFasilitasiController`) | Untuk anggota terbatas urusan hanya tab Urusan yang tampil, dengan pilihan urusan bidang OPD-nya; `isTimMember` dan filter `index` punya cabang sementara untuk OPD; menu "Input Hasil" di sidebar tampil untuk OPD | ✅ |
| Masukan OPD | Disimpan di `hasil_fasilitasi_urusan` seperti masukan fasilitator (`user_id` = akun OPD; sumber OPD terbaca dari `users.opd_id`). Tabel `masukan_opd` **tidak dibuat** | ✅ |
| Kunci hasil fasilitasi selama dinilai/disetujui Kaban (`HasilFasilitasi::isLocked()`, 12 aksi tambah/ubah/hapus + `store`) | Berlaku untuk **semua bagian** (K5) dan **semua anggota tim**, termasuk koordinator: respons 423, form dan tombol disembunyikan, banner penjelasan. Terbuka lagi bila Kaban menolak | ✅ |
| Dashboard OPD | Memakai dashboard fasilitator (`pokjaDashboard`) dengan sumber data permohonan yang sudah masuk tahap Hasil Fasilitasi | ✅ |
| Halaman approval Kaban (`approval/show`, `approval/index`) — PRD #3 | Panel "Masukan Urusan Pemerintahan" dengan nama pengirim + asal OPD (K8), jumlah masukan, filter "Hanya OPD"; badge jumlah masukan OPD di daftar approval | ✅ |
| Dokumen Word/PDF hasil fasilitasi (`HasilFasilitasiDocumentService`, `hasil-fasilitasi/pdf`) | Masukan OPD **ikut tercetak** pada bagian urusan, sama seperti masukan fasilitator. Tidak perlu perubahan kode generate, karena keduanya tersimpan di tabel yang sama | ✅ (dikonfirmasi; tanpa perubahan kode) |
| Notifikasi ke OPD | Tidak dibuat di Fase 3 (§8 no. 4). Notifikasi tim saat ini berbasis penugasan sehingga OPD tidak menerimanya; diselesaikan bersama penghapusan penugasan tim dan 2.10 | — |
| Asesmen UU PDP (Fase 5, 5.4) | Akun OPD menambah subjek data pribadi; disarankan sebelum akun OPD aktif | — |

### Keputusan no. 6 — Tanggal selesai tahapan (PRD #5)

| Fitur / komponen | Dampak | Status |
|---|---|---|
| Tabel `permohonan_tahapan` | Kolom `tanggal_selesai`, diisi dari `updated_at` untuk baris berstatus `selesai` (K3). Kolom `tanggal_selesai_perkiraan` sempat ada lalu dihapus (26 Okt) | ✅ |
| Penyelesaian tahapan di 8 controller | `tanggal_selesai` diisi/dikosongkan otomatis lewat event `saving` model `PermohonanTahapan`; ke-8 controller tidak diubah (semuanya memakai `updateOrCreate`) | ✅ |
| Progress tracker (`Permohonan::getProgressSteps()`) | Tanggal tahap selesai dibaca dari `tanggal_selesai` (fallback `updated_at`) | ✅ |
| Riwayat tanggal | Tabel `permohonan_tahapan_riwayat`, model `PermohonanTahapanRiwayat` | ✅ |
| Ubah batas waktu tahapan (`PermohonanController::updateDeadline`) | Ikut menulis riwayat jenis `deadline` | ✅ |
| Form ubah tanggal selesai | Panel "Tanggal Selesai Tahapan" di halaman detail permohonan (bukan halaman tahapan, karena semua tahapan tampil di sana); modal untuk pemegang `tahapan.update-tanggal-selesai`; aturan K2 di `UpdateTanggalSelesaiTahapanRequest` | ✅ |
| Riwayat di halaman detail permohonan | Panel tanggal selesai & riwayat hanya tampil untuk pemegang `tahapan.update-tanggal-selesai` (Admin PERAN, Superadmin) — K1 diubah 26 Okt 2026 | ✅ |
| Tanggal selesai di halaman lain | Inventaris: hanya halaman tahapan Pelaksanaan ("telah diselesaikan pada") yang memakai `updated_at`; kini memakai `tanggal_selesai`. Belum ada laporan durasi | ✅ |
| Tanggal pada dokumen hasil fasilitasi (temuan C4) | **Tidak lagi** bagian PRD #5. Tetap memakai tanggal generate; rujukan kolom tidak ada `hasil_fasilitasi.tanggal_dibuat` dicatat sebagai bug di Fase 5 | — |

### Keputusan no. 7 — Batas wewenang Admin (PRD #6 & manajemen pengguna)

| Fitur / komponen | Dampak | Status |
|---|---|---|
| `RolePolicy` | Role terlindungi tinggal `superadmin`; Admin boleh mengatur role `admin_peran` | ✅ |
| Permission tingkat pengelola | `role.manage`, `role.manage-protected`, `user.manage-superadmin` hanya bisa diberikan/dicabut Superadmin, agar Admin tidak bisa menaikkan wewenangnya sendiri atau mengunci dirinya keluar | ✅ |
| Tampilan Role & Hak Akses dan Katalog bagi Admin | Role superadmin dan permission tingkat pengelola tidak ditampilkan; tidak ada teks "superadmin" (diuji) | ✅ |
| Checklist hak akses | Hanya label hak akses (mis. "Kelola penugasan tim"), tanpa nama permission | ✅ |
| Tambah & hapus role | Hanya Superadmin; role katalog tidak bisa dihapus | ✅ |
| Pemberian role ke pengguna (`User::PROTECTED_ROLES`, `StoreUserRequest`, `UpdateUserRequest`) | Role `superadmin`/`kaban`/`admin_peran` hanya bisa diberikan atau diubah Superadmin (sebelumnya: tidak bisa dibuat siapa pun lewat form tambah, tetapi bisa diberikan Admin lewat form ubah). Form ubah menampilkan role terkunci untuk akun tersebut | ✅ |

### Keputusan no. 8 — Backup ditunda

| Fitur / komponen | Dampak | Status |
|---|---|---|
| Backlog Fase 5 "Backup & DR" | Tetap di backlog, tanpa jadwal | ✅ |
| Runbook §2 | Backup manual sebelum rilis tetap dianjurkan; lokasi penyimpanan belum ditentukan | ✅ |
| Kriteria rilis Fase 3 | Rilis yang menjalankan migration (PRD #2, #5) tetap disertai backup manual | — |

## Urutan & Alasan

1. **PRD #6** dulu, agar role OPD dibuat dan diatur lewat mekanisme permission yang sudah final. ✅
2. **Fondasi PRD #2** (master OPD, role & akun OPD) — diminta lebih awal. ✅
3. **PRD #5**: kecil, independen, keputusan sudah jelas sepenuhnya.
4. **Sisa PRD #2** (masukan OPD), lalu **#3** yang memakai data masukan dari #2.
5. **PRD #1** terakhir, agar redesign mencakup halaman baru dari #2, #3, #5, #6 dan view sudah bersih dari Fase 1–2.

---

## PRD #6 — Manajemen Permission untuk Admin & Superadmin ✅

**Desain (sudah diimplementasikan):**
- Halaman **Role & Hak Akses**: daftar role, lalu checklist permission per role yang dikelompokkan per modul sesuai katalog. Nama permission ditampilkan dengan label bahasa Indonesia dari katalog.
- **Katalog bersifat read-only di UI.** Fitur create/destroy permission dihapus.
- Akses: `role.manage` (Admin & Superadmin) dan `role.manage-protected` (Superadmin).
- Batas wewenang Admin PERAN (keputusan no. 7):
  - tidak boleh mengubah role `superadmin`;
  - tidak boleh menambah atau menghapus role;
  - tidak boleh memberi/mencabut permission tingkat pengelola (`role.manage`, `role.manage-protected`, `user.manage-superadmin`).

  Superadmin tidak dibatasi, kecuali role katalog tidak bisa dihapus.
- Setiap perubahan dicatat di `activity_log` (role, permission ditambah/dicabut, pelaku).

**Test:** `RoleControllerTest` (16 test).

---

## PRD #5 — Admin Mengatur Tanggal Selesai Tahapan

**Kondisi awal:**
- Tidak ada kolom tanggal selesai. Progress tracker memakai `permohonan_tahapan.updated_at` untuk tahap berstatus `selesai`, sehingga tanggalnya bergeser setiap kali baris diubah (mis. saat batas waktu diubah).
- Batas waktu (`permohonan_tahapan.deadline`) sudah bisa diubah Admin (`tahapan.update-deadline`), tetapi riwayatnya hanya ada di activity log umum.
- Tabel audit `permohonan_tahapan_log` dulu ada tetapi tidak pernah dipakai dan sudah dihapus (`2026_06_11_000000_drop_unused_tables`).

**Desain** (keputusan no. 6):
1. Migration: kolom `permohonan_tahapan.tanggal_selesai` (`timestamp`, nullable), backfill dari `updated_at` untuk baris berstatus `selesai`.
2. Model `PermohonanTahapan`: saat status berubah menjadi `selesai` dan `tanggal_selesai` kosong, isi `now()`. Saat status keluar dari `selesai` (mis. revisi), kosongkan. Dengan begitu 8 controller yang menyelesaikan tahapan tidak perlu diubah.
3. Tabel `permohonan_tahapan_riwayat`: `permohonan_tahapan_id`, `jenis` (`tanggal_selesai`/`deadline`), `nilai_lama`, `nilai_baru`, `alasan`, `user_id`, `created_at`. Model `PermohonanTahapanRiwayat`.
4. Permission baru `tahapan.update-tanggal-selesai` (Admin PERAN, Superadmin). Route `PUT permohonan/{permohonan}/tahapan/{tahapan}/tanggal-selesai`.
5. Validasi: hanya untuk tahapan berstatus `selesai`; tanggal tidak boleh di masa depan dan tidak sebelum permohonan dibuat; alasan wajib (min. 10 karakter).
6. `updateDeadline` ikut menulis riwayat jenis `deadline`.
7. `getProgressSteps()` memakai `tanggal_selesai`.
8. Halaman tahapan: tombol "Ubah tanggal selesai" pada tahap selesai + panel riwayat per tahapan.
9. Tidak dikunci setelah Kaban menyetujui (keputusan tidak membatasi); setiap perubahan tercatat di riwayat dan activity log.

**Test:** Admin mengubah tanggal selesai → tersimpan, riwayat berisi nilai lama/baru/alasan, progress tracker menampilkan tanggal baru; tahap belum selesai → ditolak; tanggal masa depan / tanpa alasan → error validasi; role tanpa permission → 403; menyelesaikan tahapan mengisi `tanggal_selesai` otomatis; ubah batas waktu menulis riwayat `deadline`.

---

## PRD #2 — Role & Akun OPD

**Hak akses dari PRD:** (a) memberi masukan pada hasil fasilitasi, (b) melihat proses permohonan fasilitasi, (c) mengunduh dokumen fasilitasi.

### Sudah dikerjakan (24 Okt 2026)
- **Master OPD** (`opd`): OPD lingkungan Provinsi, struktur sesuai kamus data OPD (lihat catatan pelaksanaan 25 Okt); urusan bidang OPD di `opd_urusan`. Dikelola Admin (`master.opd.manage`). OPD yang masih punya akun tidak bisa dihapus, cukup dinonaktifkan.
- **Akun:** kolom `users.opd_id`. Pengguna ber-role `opd` wajib terhubung ke satu OPD aktif; satu OPD boleh punya lebih dari satu akun.
- **Role `opd`** dengan `permohonan.view-all` → (b) melihat semua permohonan dan prosesnya; (c) unduh dokumen mengikuti hak lihat permohonan.

### Sisa pekerjaan

Keputusan teknis: **OPD termasuk tim fasilitator**, dibatasi pada input urusan pemerintahan; selebihnya sama seperti fasilitator.

- **Hak akses role `opd`** = hak akses `fasilitator` dengan dua perbedaan:
  - `hasil-fasilitasi.input-urusan` (baru) menggantikan `hasil-fasilitasi.input`: hanya route/aksi urusan (`storeUrusan`, `updateUrusan`, `deleteUrusan`), tanpa sistematika/bab, form, dan rekomendasi;
  - `permohonan.view-all` menggantikan `permohonan.view-assigned`, karena OPD tidak memakai penugasan (konfirmasi 4).
- **Masukan disimpan di `hasil_fasilitasi_urusan`** seperti masukan fasilitator. Tidak ada tabel `masukan_opd`.
- **Aturan masukan OPD:**
  - `master_urusan_id` harus termasuk urusan bidang OPD akun tersebut (`opd_urusan`);
  - ubah/hapus hanya masukan miliknya (aturan pemilik yang sudah dipakai fasilitator, `canManageItem`).
- **Kunci masukan urusan (OPD dan fasilitator):** selama hasil fasilitasi diajukan ke atau disetujui Kaban (`hasil_fasilitasi.status_draft` bernilai `menunggu_persetujuan_kaban` atau `disetujui_kaban`), tambah/ubah/hapus masukan urusan ditolak untuk semua anggota tim, termasuk koordinator. Bila Kaban menolak, kunci terbuka lagi untuk revisi. Kunci **tidak** memakai `tanggal_diajukan_kaban`, karena kolom itu tidak dikosongkan saat Kaban menolak sehingga masukan akan terkunci permanen. Kunci ini hanya untuk bagian urusan; bagian sistematika/bab, form, dan rekomendasi tidak berubah perilakunya.
- **Dokumen hasil:** masukan OPD ikut tercetak di bagian urusan dokumen Word/PDF, sama seperti masukan fasilitator. Tidak ada perubahan pada generate dokumen.
- **Wewenang koordinator tidak berlaku untuk OPD.** Generate dokumen dan unggah draft final tetap milik koordinator/Admin, dan ajukan ke Kaban milik Admin. OPD setara anggota tim biasa, sehingga `hasil-fasilitasi.submit-kaban` secara efektif tidak membuka aksi baru.
- **Cabang sementara di `HasilFasilitasiController`:** pemeriksaan anggota tim (`isTimMember`, filter `index`, unduh Word/PDF, query di `tahapan/hasil.blade.php`) berbasis penugasan. Pemegang `hasil-fasilitasi.input-urusan` diperlakukan sebagai anggota tim untuk semua permohonan. Cabang ini dihapus saat [penugasan tim dihapus](FASE-5-BACKLOG.md#penghapusan-penugasan-tim-kebutuhan-baru-24-okt-2026).
- **Dashboard & sidebar OPD:** sama seperti fasilitator, tanpa menu yang membutuhkan penugasan.
- Notifikasi ke OPD **tidak** dibuat di fase ini (§8 no. 4).

**Test:** (sudah) master OPD, akun OPD wajib OPD aktif, role baru mendapat pemetaan default saat sync. (sisa) OPD membuat masukan urusan bidangnya → tersimpan di `hasil_fasilitasi_urusan` dan tersanitasi; urusan di luar bidangnya → 403; aksi sistematika/form/rekomendasi → 403; tambah/ubah/hapus masukan urusan saat menunggu persetujuan / sudah disetujui Kaban → ditolak, baik oleh OPD maupun fasilitator; setelah ditolak Kaban → boleh lagi; OPD tidak bisa mengubah masukan pengguna lain; masukan OPD muncul di dokumen Word/PDF hasil; unduh Word/PDF hasil fasilitasi berhasil; OPD tidak bisa mengakses aksi verifikasi/approval.

---

## PRD #3 — Lihat Masukan pada Akun Kaban

**Catatan:** kolom `hasil_fasilitasi.keterangan_kaban` adalah catatan **dari** Kaban saat menyetujui/menolak, bukan masukan **untuk** Kaban, sehingga tidak dipakai di sini.

**Desain:**
- Masukan OPD adalah bagian dari masukan urusan hasil fasilitasi (keputusan teknis), sehingga Kaban sudah bisa melihatnya lewat `hasil-fasilitasi.view`. Tidak perlu permission `masukan-opd.view-all`.
- Di halaman approval Kaban (`pages/fasilitasi/approval/show.blade.php`) dan halaman hasil fasilitasi, setiap masukan urusan diberi **penanda sumber**: nama pengirim dan nama OPD untuk akun OPD, "Tim Fasilitasi" untuk fasilitator. Sediakan filter "hanya masukan OPD".
- Badge jumlah masukan OPD pada daftar approval.
- Halaman daftar seluruh masukan OPD (filter kab/kota, tahun, jenis dokumen, OPD) bersifat opsional.

**Test:** Kaban melihat masukan OPD beserta nama OPD pada permohonan yang sedang di-approve; filter "hanya masukan OPD" bekerja; konten berisi skrip tidak tereksekusi.

---

## PRD #1 — Pembaruan Tampilan / UI-UX

**Batasan:** template tetap Bootstrap/Sneat kecuali ada persetujuan mengganti stack (perubahan dependensi butuh persetujuan). Redesign tidak mengubah logika bisnis.

**Langkah:**
1. **Audit:** inventaris halaman per role (7 role + OPD) dan alur utama, lalu catat masalah UX dari pengguna (Admin PERAN, Kaban, pemohon).
2. **Desain:** mockup halaman kunci (dashboard per role, daftar & detail permohonan, hasil fasilitasi, approval) yang disetujui stakeholder.
3. **Komponen:** ekstrak Blade component untuk elemen berulang (kartu status, tabel, badge status, progress tracker, form upload, panel masukan, panel riwayat tanggal) di `resources/views/components/`.
4. **Implementasi per role**, dimulai dari role dengan pengguna terbanyak (pemohon), lalu Admin PERAN, Kaban, Tim, OPD. Gabungkan item 3.3 (badge "menunggu perpanjangan waktu" di progress tracker).
5. **Responsif & aksesibilitas:** uji di lebar ponsel; kontras warna; label form.

**Test:** smoke test, yaitu setiap route GET utama per role mengembalikan 200 (melengkapi test (d) Fase 2). Tidak ada test baru untuk perubahan yang murni visual.

---

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Admin salah mengatur permission sehingga role kehilangan akses | Tombol "Default" per role; activity log; permission tingkat pengelola hanya bisa diubah Superadmin |
| Tanggal selesai diubah tanpa jejak | Alasan wajib + tabel riwayat + activity log |
| Masukan OPD tidak bisa dibedakan dari masukan fasilitator | Sumber terbaca dari `users.opd_id`; penanda sumber di halaman hasil & approval (PRD #3) |
| Fasilitator terbiasa mengubah masukan urusan setelah hasil diajukan ke Kaban | Kunci baru diumumkan ke tim sebelum rilis; revisi tetap bisa lewat alur penolakan Kaban yang sudah ada (status `ditolak_kaban` membuka kunci) |
| Cabang sementara "OPD dianggap anggota tim" tertinggal setelah penugasan dihapus | Dipusatkan di satu helper `HasilFasilitasiController` + dicatat di Fase 5 |
| Rilis dengan migration tanpa strategi backup otomatis (keputusan no. 8) | Backup manual sesuai runbook §2 sebelum rilis PRD #2 dan #5 |
| Redesign memecah alur | Smoke test per role, ditambah uji penerimaan pengguna (UAT) per role sebelum rilis |

## Kriteria Selesai Fase 3

- [x] PRD #6 (kode; belum dirilis): Admin & Superadmin mengatur pemetaan role ↔ permission sesuai batas wewenang keputusan no. 7; perubahan berdampak langsung dan tercatat
- [x] PRD #5 (kode; belum dirilis): Admin dapat menyesuaikan tanggal selesai setiap tahapan; riwayat perubahan tersimpan dan tampil per tahapan
- [x] Fondasi PRD #2 (kode; belum dirilis): master OPD, role `opd`, akun OPD dibuat Admin
- [x] PRD #2 (kode; belum dirilis): akun OPD bekerja seperti fasilitator dengan input terbatas pada urusan pemerintahan bidangnya; hasil fasilitasi terkunci selama dinilai/disetujui Kaban; masukan OPD tercetak di dokumen hasil
- [x] PRD #3 (kode; belum dirilis): Kaban melihat masukan OPD (dengan nama dan asal OPD) di halaman approval
- [ ] PRD #1: halaman utama seluruh role memakai tampilan baru; UAT per role lulus — ditunda (K10)
- [ ] Rilis bertahap per butir PRD, masing-masing dengan backup manual dan `permission:sync`

---

## Catatan Pelaksanaan

### PRD #6 — 24 Oktober 2026

- Batas wewenang dikumpulkan di satu tempat: [`RolePolicy`](../../app/Policies/RolePolicy.php) (`PROTECTED_ROLES`, `PROTECTED_PERMISSIONS`).
- Awalnya diimplementasikan dengan usulan default (role `admin_peran` juga terlindungi), lalu diselaraskan dengan keputusan no. 7 pada hari yang sama: `PROTECTED_ROLES` = `superadmin` saja. Karena Admin kini bisa mengubah role-nya sendiri, `user.manage-superadmin` ditambahkan ke `PROTECTED_PERMISSIONS` agar Admin tidak bisa memberi dirinya hak mengelola akun superadmin.
- Katalog: `role.manage` dimiliki Superadmin & Admin PERAN; permission baru `role.manage-protected` (Superadmin).
- Halaman **Role & Hak Akses**: checklist per modul dengan label katalog; permission terkunci tampil nonaktif dan dipertahankan saat Admin menyimpan; tombol **Default** mengembalikan role katalog ke pemetaan `config/permissions.php`.
- Nama role tidak bisa diubah setelah dibuat (kode memeriksa nama role untuk notifikasi & dashboard). Role katalog tidak bisa dihapus, juga oleh Superadmin.
- Halaman permission menjadi katalog read-only; route create/store/destroy permission dan view `permissions/create` dihapus.
- Activity log: `Menambah role`, `Mengubah hak akses role`, `Mengembalikan hak akses role ke default`, `Menghapus role`, dengan properti `ditambah`/`dicabut`. Penyimpanan tanpa perubahan tidak dicatat.
- Cache permission dibersihkan otomatis oleh `syncPermissions` Spatie; perubahan langsung berlaku (diuji).
- **Rilis:** lihat [`RUNBOOK.md` §5](../operasional/RUNBOOK.md#5-command-sekali-jalan-per-rilis); `role.manage` untuk Admin PERAN diberikan Superadmin lewat UI setelah `permission:sync`.

### Konfirmasi lanjutan & fondasi PRD #2 — 24 Oktober 2026

- **Aturan role pengguna:** konstanta `User::NON_CREATABLE_ROLES`/`NON_EDITABLE_ROLES` diganti `User::PROTECTED_ROLES` (`superadmin`, `kaban`, `admin_peran`) dengan helper `nonAssignableRolesFor()` dan `isRoleLockedFor()`. Superadmin kini bisa **membuat** pengguna ber-role `kaban`/`admin_peran` (sebelumnya tertutup untuk semua). Admin tetap bisa mengubah nama/email/kata sandi akun `kaban`/`admin_peran`; role-nya tampil terkunci.
- **Tampilan Role & Hak Akses bagi Admin:** role superadmin disembunyikan dari daftar, permission tingkat pengelola tidak muncul di checklist maupun katalog, badge role di katalog tidak memuat superadmin (`RolePolicy::hiddenRolesFor`). Checklist dan katalog hanya menampilkan label hak akses; menu "Katalog Permission" menjadi "Katalog Hak Akses".
- **Master OPD & akun OPD:** lihat bagian PRD #2. Akun OPD sementara memakai dashboard default.
- **`permission:sync`:** role yang baru ditambahkan ke katalog langsung mendapat seluruh pemetaan default-nya (sebelumnya hanya menerima permission yang juga baru, sehingga role `opd` akan kosong di server).
- **Penugasan tim:** kebutuhan perubahan dicatat di [Fase 5 — Penghapusan penugasan tim](FASE-5-BACKLOG.md#penghapusan-penugasan-tim-kebutuhan-baru-24-okt-2026), sesuai arahan agar tidak mengganggu pengembangan yang berjalan.
- **Test baru:** `OpdControllerTest`, `SyncPermissionsTest`, tambahan di `UserControllerTest` dan `RoleControllerTest`.

### Keputusan teknis: OPD termasuk tim fasilitator — 24 Oktober 2026

- Desain PRD #2 dan #3 disesuaikan: tabel `masukan_opd` dan permission `masukan-opd.create`/`masukan-opd.view-all` **dibatalkan**; masukan OPD memakai `hasil_fasilitasi_urusan` milik alur fasilitator, dengan permission baru `hasil-fasilitasi.input-urusan`.
- Kode yang sudah ada (master OPD, role `opd` dengan `permohonan.view-all`, akun OPD) tetap berlaku dan tidak perlu diubah. Pemetaan role `opd` ke permission fasilitator lainnya dilakukan saat sisa PRD #2 dikerjakan.
- Dikonfirmasi pemilik proyek (24 Okt 2026): (1) masukan OPD ikut tercetak di dokumen Word/PDF hasil fasilitasi seperti masukan fasilitator; (2) kunci "sampai diajukan ke Kaban" berlaku juga untuk masukan urusan fasilitator — perubahan perilaku bagi fasilitator yang selama ini tidak dikunci.

### PRD #5, sisa PRD #2, dan PRD #3 — 25 Oktober 2026

**PRD #5 — tanggal selesai tahapan**
- Migration `add_tanggal_selesai_to_permohonan_tahapan_table` (kolom + backfill perkiraan) dan `create_permohonan_tahapan_riwayat_table`.
- `PermohonanTahapan`: event `saving` mengisi `tanggal_selesai` saat status menjadi `selesai` dan mengosongkannya saat keluar dari `selesai`; relasi `riwayat()`.
- `TanggalSelesaiTahapanController::update` + `UpdateTanggalSelesaiTahapanRequest`: hanya tahapan selesai; tidak di masa depan; tidak sebelum permohonan dibuat; urut terhadap tahapan sebelum/sesudah yang sudah selesai (K2); alasan wajib. Tahapan milik permohonan lain → 404. Tanggal sama → tidak ada perubahan.
- Riwayat: perubahan tanggal selesai dan batas waktu. Koreksi pertama atas tanggal perkiraan tidak dicatat (K3). Semua perubahan tanggal selesai tetap masuk activity log.
- Permission baru `tahapan.update-tanggal-selesai` (Admin PERAN, Superadmin).
- Format tanggal panel dan pesan validasi memakai locale `id` secara lokal, karena `app.locale` masih `en`.

**Sisa PRD #2 — OPD sebagai fasilitator terbatas urusan**
- Route input dipecah: halaman input + aksi urusan memakai `hasil-fasilitasi.input-urusan` (fasilitator & OPD); sistematika/form/rekomendasi/simpan/ajukan/generate tetap `hasil-fasilitasi.input` (fasilitator).
- `HasilFasilitasiController`: `isUrusanOnlyMember()` (punya `input-urusan` tanpa `input`), `allowedUrusanIds()` (urusan bidang OPD; tanpa urusan → tidak bisa input, K7), cabang sementara di `isTimMember` dan `index`.
- **Bug diperbaiki:** `submit()` memanggil `HasilFasilitasi::canEdit()` yang tidak ada (selalu error 500). Kini `canEdit()` = kebalikan `isLocked()`.
- Kunci K5: `HasilFasilitasi::isLocked()` (status `menunggu_persetujuan_kaban`/`disetujui_kaban`) di semua aksi tambah/ubah/hapus dan `store`; UI menyembunyikan form & tombol.
- OPD memakai dashboard fasilitator; menu "Input Hasil" tampil untuk OPD.

**PRD #3 — masukan OPD untuk Kaban**
- Partial `hasil-fasilitasi/partials/pengirim` (nama + "OPD: nama OPD") dipakai di halaman input, detail hasil, dan approval (K8); baris yang ditambahkan lewat AJAX ikut menampilkan OPD.
- Halaman approval: panel masukan urusan dengan filter "Hanya OPD"; daftar approval: badge jumlah masukan OPD. Tidak ada halaman daftar masukan terpisah (K9).

**Rilis:** `php artisan migrate`, lalu `php artisan permission:sync` (lihat runbook). Lingkungan yang sudah punya role `opd` dari rilis fondasi PRD #2 hanya menerima permission baru (`hasil-fasilitasi.input-urusan`). Hak akses fasilitator lain untuk role `opd` (`hasil-fasilitasi.view`, `hasil-fasilitasi.submit-kaban`, `pelaksanaan.upload-dokumen`) perlu dicentang Admin lewat **Role & Hak Akses → opd**, atau tekan **Default** pada role `opd`.

**Test baru:** `TanggalSelesaiTahapanTest` (14), `HasilFasilitasiOpdTest` (15).

### Klarifikasi OPD lingkungan Provinsi — 25 Oktober 2026

- Pemilik proyek memastikan semua OPD adalah OPD di lingkungan Provinsi, sehingga **jenis OPD (provinsi/mitra/teknis) dihapus**.
- Migration baru `drop_jenis_from_opd_table` (bukan mengubah `create_opd_table` yang sudah dijalankan). Konstanta `Opd::JENIS`, accessor `jenis_label`, aturan validasi, field form, dan kolom di daftar OPD dihapus. Pilihan OPD di form pengguna hanya menampilkan nama OPD.

### Struktur tabel OPD sesuai kamus data — 25 Oktober 2026

Migration `restructure_opd_table` (sudah diuji rollback). Pemetaan kamus data → kolom:

| Kamus data | Kolom | Aturan |
|---|---|---|
| id_opd | `id` | Primary key bawaan; tidak diganti nama agar FK `users.opd_id` dan `opd_urusan.opd_id` tetap |
| kode_opd | `kode_opd` | Wajib, unik, format `82.XX.XX`, diawali kode wilayah |
| kode_wilayah | `kode_wilayah` | Wajib, 2 digit, bawaan `82` |
| nama_opd | `nama_opd` (dulu `nama`) | Wajib, unik, maks 150 |
| singkatan | `singkatan` | Wajib, maks 20 |
| jenis_opd | `jenis_opd` | Wajib, dari `Opd::JENIS_OPD` |
| rumpun_urusan | `rumpun_urusan` | Wajib, dari `Opd::RUMPUN_URUSAN` |
| status | `status` (dulu `is_active`) | Wajib, `Aktif`/`Tidak Aktif`; akun OPD hanya bisa dihubungkan ke OPD Aktif |
| tanggal_update | `tanggal_update` | Wajib, tanggal; form mengisi tanggal hari ini sebagai bawaan |
| kode_urusan | `kode_urusan` | Opsional, format `X.XX` |
| urusan_pemerintahan | `urusan_pemerintahan` | Opsional, teks (lebih dari satu dipisah `;`) |
| induk_opd | `induk_opd` | Opsional, `kode_opd` yang terdaftar dan bukan dirinya sendiri; relasi `Opd::induk()` |

- Kolom wajib tanpa nilai bawaan (`kode_opd`, `jenis_opd`, `rumpun_urusan`) nullable di database agar migrasi aman bila sudah ada data, dan diwajibkan lewat `OpdRequest`.
- **Daftar referensi belum diberikan**, sehingga dipakai daftar bawaan berbasis Permendagri 90/2019, yang mudah diubah di `app/Models/Opd.php`:
  - jenis OPD: Sekretariat, Inspektorat, Dinas, Badan, Biro, Satuan, Rumah Sakit, UPTD;
  - rumpun urusan: mengikuti kelompok urusan 1–8 di master urusan (lihat catatan berikut); "Kekhususan" dihapus karena kamus data urusan hanya memuat kelompok 1–8.
- `kode_urusan`/`urusan_pemerintahan` adalah data nomenklatur urusan yang **diampu** OPD. Hak memberi masukan pada hasil fasilitasi tetap ditentukan oleh pilihan "Urusan yang Dapat Diberi Masukan" (`opd_urusan` → `master_urusan`).

### Struktur tabel urusan sesuai kamus data — 25 Oktober 2026

Migration `restructure_master_urusan_table` (sudah diuji rollback; data 32 urusan dipertahankan). Pemetaan:

| Kamus data | Kolom | Aturan |
|---|---|---|
| id_urusan | `id` | Primary key bawaan; FK `hasil_fasilitasi_urusan` dan `opd_urusan` tetap |
| kode_urusan | `kode_urusan` | Wajib, unik, `X.XX` dengan X = kelompok 1–8 |
| kode_kelompok, nama_kelompok, rumpun_urusan | kolom sama | Wajib; **diisi otomatis** dari digit pertama kode urusan (`MasterUrusan::KELOMPOK`) |
| nama_urusan | `nama_urusan` | Wajib, maks 150 |
| kode_opd_pengampu | `kode_opd_pengampu` | Opsional; tiap kode (dipisah `;`) berformat `82.XX.XX` dan terdaftar di master OPD |
| opd_pengampu | `opd_pengampu` | **Diisi otomatis** dari singkatan OPD pada kode OPD pengampu (dihitung saat urusan disimpan) |
| status, tanggal_update | kolom sama | Wajib; Aktif/Tidak Aktif; tanggal pemutakhiran (bawaan hari ini) |

- **Data lama:** kode urusan diisi dari kategori + urutan. Urutan 1–32 ternyata sama dengan nomor bidang resmi (Pendidikan 1.01 … Kearsipan 2.24 … Transmigrasi 3.32), sehingga kolom `kategori` dan `urutan` dihapus tanpa mengubah urutan tampil.
- **Pengurutan** di semua tempat (halaman input & detail hasil, approval, dokumen Word/PDF) kini memakai `kode_urusan`; tampilan "1. Pendidikan" menjadi "1.01 Pendidikan". Urutan bagian urusan di dokumen hasil tidak berubah untuk data yang ada.
- Urusan **Tidak Aktif** tidak lagi ditawarkan di pilihan urusan halaman input hasil fasilitasi dan form OPD; masukan lama tetap tampil.
- Urusan yang sudah dipakai masukan hasil fasilitasi tidak bisa dihapus (sebelumnya bisa dan menghapus masukannya lewat cascade).
- `MasterUrusanSeeder` ditulis ulang: idempoten berdasarkan `kode_urusan`.
- Daftar rumpun OPD kini diambil dari `MasterUrusan::rumpunList()`, sehingga sama dengan rumpun urusan.

### Seeder dari CSV & penanda urusan hasil fasilitasi — 25 Oktober 2026

- **Seeder CSV:** `OpdSeeder` (`docs/master-opd.csv`, 46 OPD) dan `MasterUrusanSeeder` (`docs/master-urusan.csv`, 44 urusan) membaca CSV lewat trait `Database\Seeders\BacaCsv`. Baris dicocokkan lewat `kode_opd` / `kode_urusan`: baris baru ditambahkan; baris yang sudah ada **hanya diisi kolom yang masih kosong** (nama, status, dan nilai lain yang sudah ada tidak ditimpa). Kolom `id_*` di CSV diabaikan. `OpdSeeder` dijalankan sebelum `MasterUrusanSeeder` (juga di `DatabaseSeeder`) agar singkatan OPD pengampu terisi.
- **Penanda `master_urusan.tampil_hasil_fasilitasi`:** hanya urusan berstatus Aktif **dan** bertanda tampil yang muncul di bagian urusan hasil fasilitasi serta pilihan urusan masukan di form OPD (scope `MasterUrusan::untukHasilFasilitasi()`). Bawaan aktif untuk kelompok 1–3 (wajib dasar, wajib non dasar, pilihan); unsur kelompok 4–8 bawaan tidak tampil. Admin mengubahnya lewat tombol di daftar urusan atau switch di form.
- **Format kode disesuaikan dengan data CSV:** kode OPD bisa 4 segmen (`82.01.01.01` untuk Biro/UPTD; kolom `kode_opd`/`induk_opd` diperlebar ke 20); kode urusan diampu OPD bisa lebih dari satu (`1.01; 2.22`); kode OPD pengampu urusan menerima kedua format.
- **Hasil di DB lokal:** 46 OPD ditambah; 12 urusan ditambah (kelompok 4–8) dan 31 dilengkapi OPD pengampunya; 32 urusan tampil di hasil fasilitasi.
- **Nama urusan yang berbeda dengan CSV dan tidak ditimpa** (10): 1.05, 2.08, 2.12, 2.13, 2.14, 2.16, 2.17, 2.19, 3.25, 3.29 — data lama memakai "&"/singkatan, CSV memakai nomenklatur lengkap (mis. "Koperasi, UKM" vs "Koperasi, Usaha Kecil, dan Menengah"). Nama ini tercetak di dokumen hasil fasilitasi; ubah lewat form urusan bila ingin mengikuti CSV.
- **Catatan data CSV OPD:** Sekretariat Daerah, Biro, dan Sekretariat DPRD bertanda rumpun "Penunjang" dengan kode urusan 4.01/4.02 (kelompok 4 = Pendukung). Disimpan apa adanya; periksa bila rumpun yang benar adalah "Pendukung".

### Sinkronisasi urusan masukan OPD — 26 Oktober 2026

- Urusan yang dapat diberi masukan oleh akun OPD (`opd_urusan`) **tidak lagi dipilih satu per satu**. Sumbernya kolom `opd.kode_urusan` (urusan yang diampu OPD, kamus data):
  - `Opd` disimpan dan `kode_urusan` berubah → `Opd::sinkronkanUrusan()` menyamakan `opd_urusan`; daftar kode dirapikan menjadi `X.XX; X.XX`;
  - urusan baru ditambahkan atau `kode_urusan`-nya berubah di master urusan → OPD yang mencantumkan kode lama/baru disinkronkan ulang (sehingga urutan seeder OPD → urusan tetap menghasilkan data lengkap);
  - migration `sinkronkan_urusan_opd` mengisi data yang sudah ada (lokal: 53 pasangan untuk 46 OPD).
- Validasi: setiap kode urusan diampu wajib terdaftar di master urusan.
- Form OPD: checkbox urusan dihapus; urusan hasil sinkronisasi ditampilkan read-only (abu-abu = tidak tampil di hasil fasilitasi).
- Dipilih `opd.kode_urusan`, bukan `master_urusan.kode_opd_pengampu`: keduanya identik pada data CSV kecuali 9 Biro yang mencantumkan 4.01 di data OPD-nya; data OPD adalah catatan "urusan yang dimiliki OPD". `kode_opd_pengampu` tetap kolom keterangan di master urusan dan **tidak** disinkronkan dua arah.

### Kaban melihat masukan hasil fasilitasi — 26 Oktober 2026

Sebelumnya Kaban baru bisa melihat masukan setelah hasil diajukan (halaman approval): di halaman tahapan hasil hanya muncul info "sedang diproses", tombol "Lihat Detail" hanya ada di kartu batas waktu yang tidak tampil untuk Kaban, dan daftar hasil fasilitasi kosong karena difilter penugasan tim.

- Halaman tahapan hasil: kartu **Masukan Hasil Fasilitasi** untuk pemegang `approval.approve` (jumlah per bagian, jumlah dari OPD, tombol **Lihat Masukan**; tombol **Review & Setujui** bila menunggu persetujuan).
- Sidebar Kaban: menu **Masukan Hasil Fasilitasi** → daftar hasil fasilitasi.
- `HasilFasilitasiController::index`: pemegang `permohonan.view-all` (Admin, Kaban, OPD) melihat semua hasil; tim lain tetap sesuai penugasan.
- `show()`: bila hasil belum dibuat, semua yang bukan anggota tim (termasuk Kaban) diarahkan ke halaman tahapan hasil, bukan ke halaman input (sebelumnya Kaban mendapat 403).
- Perbaikan ikutan: tombol input di daftar hasil kini memakai `hasil-fasilitasi.input-urusan`, sehingga akun OPD bisa membuka halaman input dari daftar.
- Halaman detail hasil sudah read-only untuk Kaban (aksi hanya untuk Admin/koordinator). Tab **Konsistensi & Keselarasan** dan **Rekomendasi** (sebelumnya hanya Admin) kini juga tampil untuk Kaban; anggota tim lain tetap melihat Sistematika dan Urusan saja. Panel masukan urusan di halaman approval diberi tautan "Lihat semua bagian masukan". Test: `KabanMasukanHasilTest`.

### Panel tanggal selesai hanya untuk Admin — 26 Oktober 2026

- Keputusan K1 diubah: panel **Tanggal Selesai Tahapan** beserta riwayat perubahan tanggal di halaman detail permohonan hanya tampil untuk pemegang `tahapan.update-tanggal-selesai` (Admin PERAN, Superadmin). Pemohon, Kaban, tim, OPD, dan auditor tidak lagi melihatnya.
- `PermohonanController::show` hanya memuat relasi riwayat untuk pengguna tersebut; partial di-include dengan `@can`.
- Tanggal selesai tetap tampil bagi semua pengguna di progress tracker (tanpa riwayat). Test: `TanggalSelesaiTahapanTest` (panel tersembunyi untuk Kaban, pemohon, auditor).

### Tanpa alasan & tanggal di Riwayat Tahapan — 26 Oktober 2026

- **Alasan perubahan tanggal selesai dihapus**: form ubah hanya berisi tanggal; `UpdateTanggalSelesaiTahapanRequest` tidak lagi mewajibkan alasan; `permohonan_tahapan_riwayat.alasan` dibuat nullable (migration `make_alasan_nullable_on_permohonan_tahapan_riwayat_table`). Riwayat perubahan batas waktu tetap menyimpan keterangannya; di panel ditampilkan sebagai "Keterangan" bila ada. Jejak perubahan tanggal selesai tetap di riwayat tahapan dan activity log.
- **Tanggal yang diubah Admin kini tampil di semua tempat**: accessor `PermohonanTahapan::tanggal_selesai_tampil` (tanggal_selesai, fallback `updated_at` untuk data lama) dipakai di kartu **Riwayat Tahapan** halaman detail permohonan (sebelumnya `updated_at`), progress tracker, halaman tahapan Pelaksanaan, dan "Waktu Pelaksanaan" di detail hasil fasilitasi.
- Saat Admin mengubah tanggal, **jam** dari tanggal selesai lama dipertahankan (sebelumnya selalu 00:00), sehingga format "tanggal, jam" tetap bermakna.

### Perbaikan UX panel tanggal selesai — 26 Oktober 2026

- Panel kini menampilkan **semua tahapan** (status, tanggal mulai, tanggal selesai, durasi hari), bukan hanya tahapan selesai.
- **Ubah langsung di baris** (tanpa modal). Pilihan tanggal dibatasi ke rentang sah (`min`/`max`: setelah tahapan selesai sebelumnya dan tanggal permohonan dibuat, sebelum tahapan selesai sesudahnya dan hari ini), dan rentangnya ditampilkan di bawah input, sehingga Admin jarang menemui error "harus urut". Bila validasi gagal, baris yang sama terbuka kembali dengan pesan error dan nilai yang tadi diisi.
- ~~Tombol **Konfirmasi** untuk tanggal "Perkiraan"~~ — dihapus bersama penanda perkiraan (lihat catatan berikut).
- Ringkasan di kepala panel: jumlah tahapan selesai.
- Riwayat dipisah **per tahapan** dan bisa dibuka-tutup (tombol jumlah riwayat di setiap baris).
- Setelah simpan, halaman kembali ke panel (`#tanggal-selesai-tahapan`).

### Penanda perkiraan & konfirmasi dihapus — 26 Oktober 2026

- Arahan pemilik proyek: tanggal selesai **otomatis dari sistem** dan **dapat diubah Admin**, tanpa penanda "Perkiraan" dan tanpa konfirmasi manual.
- Migration `drop_tanggal_selesai_perkiraan_from_permohonan_tahapan_table`; model, controller, dan panel tidak lagi mengenal perkiraan. Setiap perubahan tanggal selesai oleh Admin (termasuk pada data lama) kini dicatat di riwayat tahapan.
- Test perkiraan/konfirmasi dihapus dari `TanggalSelesaiTahapanTest`.

### Kolom riwayat di panel dihapus — 26 Oktober 2026

- Kolom "Riwayat" dan baris riwayat yang bisa dibuka-tutup dihapus dari panel tanggal selesai (kini berjudul "Kelola Tanggal"). Panel berisi tahapan, mulai, selesai (ubah di baris), dan durasi.
- Pencatatan tetap berjalan sebagai jejak audit: setiap perubahan tanggal selesai dan batas waktu masih ditulis ke `permohonan_tahapan_riwayat` dan activity log, tetapi tidak ditampilkan di halaman mana pun. `PermohonanController::show` tidak lagi memuat relasi riwayat.


### Perbaikan UX halaman input hasil fasilitasi — 26 Oktober 2026

Di luar PRD #1 (UI/UX tetap ditunda). Halaman `hasil-fasilitasi.create` dirapikan untuk semua pengguna (fasilitator, koordinator, OPD):

- Kartu "Informasi Permohonan" dan "Tim Fasilitasi" diganti satu baris ringkasan (Kab/Kota, dokumen, pelaksanaan, batas waktu, status). Kartu Tim dihapus karena penugasan tim akan dihapus (FASE-5).
- Alur dokumen dikumpulkan dalam satu kartu 3 langkah (draft dokumen → draft final → persetujuan Kaban), hanya untuk Admin/Koordinator; pengguna lain cukup melihat tombol "Lihat Draft".
- Tab menampilkan jumlah masukan dan penanda bila ada masukan milik pengguna; tab aktif diingat lewat `#tab-…`.
- Form tambah dilipat di balik tombol "Tambah Masukan"; TinyMCE dimuat saat form/modal dibuka; isian tersimpan otomatis sebagai draf di browser.
- Tab Urusan dikelompokkan per urusan; OPD melihat daftar urusan bidangnya yang belum diberi masukan (klik untuk langsung menambah). Pilihan urusan bisa dicari.
- Saringan "Semua / Masukan Saya / Dari OPD" + per bab/urusan; kolom "Oleh" seragam (nama, badge OPD, penanda "Anda").
- Aksi baris dipindah ke menu Ubah/Hapus; konfirmasi & notifikasi memakai SweetAlert; satu modal ubah untuk keempat bagian, kini bisa mengganti **bab** (sistematika) dan **urusan** (urusan; OPD tetap dibatasi urusan bidangnya — `updateSistematika`/`updateUrusan` menerima `master_bab_id`/`master_urusan_id` opsional).
- Saat terkunci, form dan aksi tidak dirender dan banner menjelaskan kapan kunci terbuka.
- Teknis: breadcrumb diarahkan ke `hasil-fasilitasi.index`, query tahapan dipindah dari Blade ke controller, "Waktu Pelaksanaan" memakai `tanggal_selesai_tampil`, header responsif, `console.log` dihapus, `HasilFasilitasi::statusLabel()` ditambahkan.
- Test: `HasilFasilitasiInputPageTest` (9 test).
- **Halaman detail (`show`) disesuaikan dengan pola yang sama:** ringkasan satu baris (kartu Tim dihapus), kartu dokumen 3 langkah untuk Admin/Koordinator, tab dengan jumlah masukan, urusan dikelompokkan, saringan, kolom "Oleh" seragam, preview PDF dimuat saat dibuka. Halaman tetap hanya-baca (tanpa aksi Ubah/Hapus). Tombol baru di header: **Input Masukan** (fasilitator/OPD selama belum terkunci) dan **Review & Setujui** (Kaban saat menunggu persetujuan). Daftar masukan, saringan, skrip daftar, gaya, dan modal preview kini partial bersama (`daftar-*`, `gaya`, `preview-pdf`). `show()` tidak lagi memuat info tim; `$showExtendedTabs` dan `$bisaInput` dihitung di controller. Test: 3 test tambahan di `HasilFasilitasiInputPageTest`.
