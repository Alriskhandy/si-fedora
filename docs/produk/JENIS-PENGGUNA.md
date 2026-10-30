# Jenis Pengguna SI-FEDORA

> **Sumber kebenaran:** [`config/permissions.php`](../../config/permissions.php) (pemetaan *default* role → permission).
> Tabel ini adalah ringkasan untuk dibaca manusia. Bila katalog berubah, perbarui tabel ini di commit yang sama.
> Di server berjalan, Admin/Superadmin dapat mengubah pemetaan lewat UI (PRD #6), sehingga hak akses aktual bisa berbeda dari default di bawah.
>
> Terakhir diselaraskan dengan katalog: **24 Oktober 2026**.

| Jenis Pengguna | Hak Akses |
|---|---|
| **Superadmin** (`superadmin`) | **Sistem:** atur hak akses semua role (termasuk superadmin), tambah & hapus role, Log Viewer, activity log, unggah & hapus modul pengguna. **Pengguna:** kelola pengguna termasuk memberi/mengubah role superadmin, Kepala Badan, dan Admin PERAN, ekspor data pengguna, kelola penugasan tim. **Master data:** seluruhnya, termasuk format dokumen hasil fasilitasi. **Proses:** lihat semua permohonan, ubah batas waktu dan tanggal selesai tahapan (beserta riwayatnya), unggah dokumen & selesaikan tahapan pelaksanaan, akses & kelola semua hasil fasilitasi, unggah draft final & ajukan ke Kaban, proses perpanjangan waktu. |
| **Kepala Badan** (`kaban`) | Lihat semua permohonan; tetapkan jadwal fasilitasi; lihat & unduh hasil fasilitasi; **lihat masukan hasil fasilitasi kapan saja** (ringkasan di halaman tahapan hasil, menu "Masukan Hasil Fasilitasi", read-only); **setujui/tolak hasil fasilitasi** sambil meninjau masukan urusan beserta asal OPD-nya; kelola surat penyampaian hasil; lihat activity log. |
| **Admin PERAN** (`admin_peran`) | **Sistem:** atur hak akses semua role selain superadmin (tidak dapat menambah/menghapus role, tidak dapat memberi/mencabut hak akses tingkat pengelola). **Pengguna:** kelola pengguna kecuali akun superadmin; tidak dapat memberi role superadmin/Kepala Badan/Admin PERAN maupun mengubah role akun yang sudah memilikinya; buat akun OPD; kelola penugasan tim. **Master data:** seluruhnya, termasuk OPD dan format dokumen hasil fasilitasi. **Proses:** lihat semua permohonan, ubah batas waktu dan tanggal selesai tahapan (beserta riwayatnya), kelola jadwal fasilitasi, surat pemberitahuan, dan undangan pelaksanaan, kelola laporan verifikasi, unggah dokumen & selesaikan tahapan pelaksanaan, akses & kelola semua hasil fasilitasi, unggah draft final & ajukan ke Kaban, proses perpanjangan waktu. Lihat activity log. |
| **Verifikator** (`verifikator`) | Lihat permohonan yang **ditugaskan**; verifikasi kelengkapan dokumen; unggah dokumen pelaksanaan; lihat & unduh hasil fasilitasi. |
| **Fasilitator / Tim Pokja** (`fasilitator`) | Lihat permohonan yang **ditugaskan**; **input hasil fasilitasi** (semua bagian; terkunci selama dinilai/disetujui Kaban); unggah draft final & ajukan ke Kaban; unggah dokumen pelaksanaan; lihat & unduh hasil fasilitasi. |
| **Pemohon (Kabupaten/Kota)** (`pemohon`) | Lihat permohonan **kab/kota sendiri**; buat, ajukan & hapus permohonan (hanya milik akunnya); unggah dokumen permohonan; lihat undangan pelaksanaan; ajukan & lihat perpanjangan waktu; unggah tindak lanjut; unggah penetapan PERDA. |
| **Auditor / Monitoring** (`auditor`) | Akses baca: lihat semua permohonan dan activity log. |
| **OPD** (`opd`) | OPD di lingkungan Provinsi; akun dibuat Admin dan terhubung ke master OPD. **Termasuk tim fasilitator:** hak akses sama seperti Fasilitator, kecuali melihat **semua** permohonan (tanpa penugasan) dan input masukan **hanya pada urusan pemerintahan bidang OPD-nya**. Masukan ikut tercetak di dokumen hasil fasilitasi dan terkunci selama hasil dinilai/disetujui Kaban. |

## Berlaku untuk semua pengguna yang login

Lihat jadwal fasilitasi, arsip (dibatasi sesuai hak lihat permohonan), modul pengguna, profil, dan notifikasi.

## Pembatasan berbasis data (bukan sekadar permission)

| Aturan | Diterapkan di |
|---|---|
| Pemohon hanya melihat permohonan/arsip kab/kota-nya | `PermohonanPolicy::view`, scope `Permohonan::visibleTo` |
| Verifikator/fasilitator hanya melihat permohonan sesuai penugasan aktif (kab/kota, tahun, jenis dokumen) | `PermohonanPolicy::view`, scope `Permohonan::visibleTo` |
| Mengajukan/menghapus permohonan hanya oleh akun pembuatnya | `PermohonanPolicy::update` |
| Akun superadmin hanya dapat dikelola superadmin | `UserPolicy::manage` |
| Role superadmin, Kepala Badan, dan Admin PERAN hanya dapat diberikan atau diubah superadmin | `StoreUserRequest`, `UpdateUserRequest` (`User::nonAssignableRolesFor`, `User::isRoleLockedFor`) |
| Akun ber-role OPD wajib terhubung ke OPD aktif | `StoreUserRequest`, `UpdateUserRequest` |
| OPD hanya memberi masukan pada urusan yang diampunya (kode urusan diampu di master OPD, disinkronkan ke `opd_urusan`) dan yang ditandai tampil di hasil fasilitasi; tanpa urusan diampu tidak bisa memberi masukan | `HasilFasilitasiController::allowedUrusanIds` |
| Isi hasil fasilitasi terkunci selama dinilai/disetujui Kaban, terbuka lagi bila ditolak | `HasilFasilitasi::isLocked` |
| Role `superadmin` dan permission tingkat pengelola (`role.manage`, `role.manage-protected`, `user.manage-superadmin`) hanya dapat diatur superadmin dan tidak ditampilkan kepada Admin; role katalog tidak dapat dihapus | `RolePolicy` |
| Unduh dokumen mengikuti hak lihat permohonannya | `BerkasController` → `PermohonanPolicy::downloadDokumen` |
