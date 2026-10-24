<?php

/*
|--------------------------------------------------------------------------
| Katalog Permission
|--------------------------------------------------------------------------
|
| Satu-satunya sumber nama permission. Nama tidak dibuat lewat UI.
| `roles` adalah pemetaan default role → permission yang dipakai saat
| instalasi baru atau saat permission baru pertama kali ditambahkan
| (lihat command `permission:sync`). Perubahan pemetaan di server berjalan
| dilakukan lewat UI manajemen permission, bukan dengan mengubah file ini.
|
*/

$adminSistem = ['superadmin', 'admin_peran'];

return [

    'roles' => ['superadmin', 'kaban', 'admin_peran', 'verifikator', 'fasilitator', 'pemohon', 'auditor'],

    'permissions' => [

        // Sistem
        'role.manage' => ['modul' => 'Sistem', 'label' => 'Kelola role & permission', 'roles' => ['superadmin']],
        'activity-log.view' => ['modul' => 'Sistem', 'label' => 'Lihat activity log', 'roles' => ['superadmin', 'kaban', 'admin_peran', 'auditor']],
        'log-viewer.view' => ['modul' => 'Sistem', 'label' => 'Buka Log Viewer', 'roles' => ['superadmin']],
        'modul.manage' => ['modul' => 'Sistem', 'label' => 'Unggah & hapus modul pengguna', 'roles' => ['superadmin']],

        // Pengguna
        'user.manage' => ['modul' => 'Pengguna', 'label' => 'Kelola pengguna', 'roles' => $adminSistem],
        'user.manage-superadmin' => ['modul' => 'Pengguna', 'label' => 'Kelola akun superadmin & beri role superadmin', 'roles' => ['superadmin']],
        'user.export' => ['modul' => 'Pengguna', 'label' => 'Ekspor data pengguna', 'roles' => ['superadmin']],
        'tim-assignment.manage' => ['modul' => 'Pengguna', 'label' => 'Kelola penugasan tim', 'roles' => $adminSistem],

        // Master data
        'master.kabkota.manage' => ['modul' => 'Master Data', 'label' => 'Kelola kabupaten/kota', 'roles' => $adminSistem],
        'master.jenis-dokumen.manage' => ['modul' => 'Master Data', 'label' => 'Kelola jenis dokumen', 'roles' => $adminSistem],
        'master.tahapan.manage' => ['modul' => 'Master Data', 'label' => 'Kelola tahapan', 'roles' => $adminSistem],
        'master.kelengkapan.manage' => ['modul' => 'Master Data', 'label' => 'Kelola kelengkapan verifikasi', 'roles' => $adminSistem],
        'master.bab.manage' => ['modul' => 'Master Data', 'label' => 'Kelola bab', 'roles' => $adminSistem],
        'master.urusan.manage' => ['modul' => 'Master Data', 'label' => 'Kelola urusan', 'roles' => $adminSistem],

        // Permohonan
        'permohonan.view-all' => ['modul' => 'Permohonan', 'label' => 'Lihat semua permohonan', 'roles' => ['superadmin', 'kaban', 'admin_peran', 'auditor']],
        'permohonan.view-own' => ['modul' => 'Permohonan', 'label' => 'Lihat permohonan kab/kota sendiri', 'roles' => ['pemohon']],
        'permohonan.view-assigned' => ['modul' => 'Permohonan', 'label' => 'Lihat permohonan yang ditugaskan', 'roles' => ['verifikator', 'fasilitator']],
        'permohonan.create' => ['modul' => 'Permohonan', 'label' => 'Buat, ajukan & hapus permohonan', 'roles' => ['pemohon']],
        'permohonan.upload-dokumen' => ['modul' => 'Permohonan', 'label' => 'Unggah dokumen permohonan', 'roles' => ['pemohon']],
        'permohonan.assign' => ['modul' => 'Permohonan', 'label' => 'Tugaskan tim ke permohonan', 'roles' => ['admin_peran']],
        'permohonan.verify' => ['modul' => 'Permohonan', 'label' => 'Verifikasi kelengkapan dokumen', 'roles' => ['verifikator']],
        'tahapan.update-deadline' => ['modul' => 'Permohonan', 'label' => 'Ubah batas waktu tahapan', 'roles' => $adminSistem],

        // Jadwal & surat
        'jadwal.manage' => ['modul' => 'Jadwal', 'label' => 'Kelola jadwal fasilitasi', 'roles' => ['admin_peran']],
        'penetapan-jadwal.manage' => ['modul' => 'Jadwal', 'label' => 'Tetapkan jadwal fasilitasi', 'roles' => ['kaban']],
        'surat-pemberitahuan.manage' => ['modul' => 'Jadwal', 'label' => 'Kelola surat pemberitahuan', 'roles' => ['admin_peran']],
        'undangan-pelaksanaan.manage' => ['modul' => 'Jadwal', 'label' => 'Kelola undangan pelaksanaan', 'roles' => ['admin_peran']],
        'undangan-pelaksanaan.view-own' => ['modul' => 'Jadwal', 'label' => 'Lihat undangan pelaksanaan kab/kota sendiri', 'roles' => ['pemohon']],

        // Verifikasi & pelaksanaan
        'laporan-verifikasi.manage' => ['modul' => 'Verifikasi', 'label' => 'Kelola laporan verifikasi', 'roles' => ['admin_peran']],
        'pelaksanaan.upload-dokumen' => ['modul' => 'Verifikasi', 'label' => 'Unggah & hapus dokumen pelaksanaan', 'roles' => ['superadmin', 'admin_peran', 'verifikator', 'fasilitator']],
        'pelaksanaan.complete' => ['modul' => 'Verifikasi', 'label' => 'Selesaikan tahapan pelaksanaan', 'roles' => $adminSistem],

        // Hasil fasilitasi
        'hasil-fasilitasi.view' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Lihat & unduh hasil fasilitasi', 'roles' => ['superadmin', 'kaban', 'admin_peran', 'verifikator', 'fasilitator']],
        'hasil-fasilitasi.manage-all' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Akses & kelola semua hasil fasilitasi', 'roles' => $adminSistem],
        'hasil-fasilitasi.input' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Input hasil fasilitasi', 'roles' => ['fasilitator']],
        'hasil-fasilitasi.submit-kaban' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Unggah draft final & ajukan ke Kaban', 'roles' => ['superadmin', 'admin_peran', 'fasilitator']],
        'validasi-hasil.manage' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Validasi hasil fasilitasi', 'roles' => ['admin_peran']],
        'approval.approve' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Setujui / tolak hasil fasilitasi', 'roles' => ['kaban']],
        'surat-penyampaian-hasil.manage' => ['modul' => 'Hasil Fasilitasi', 'label' => 'Kelola surat penyampaian hasil', 'roles' => ['kaban']],

        // Pasca fasilitasi
        'perpanjangan-waktu.view' => ['modul' => 'Pasca Fasilitasi', 'label' => 'Lihat perpanjangan waktu', 'roles' => ['superadmin', 'admin_peran', 'pemohon']],
        'perpanjangan-waktu.submit' => ['modul' => 'Pasca Fasilitasi', 'label' => 'Ajukan perpanjangan waktu', 'roles' => ['pemohon']],
        'perpanjangan-waktu.process' => ['modul' => 'Pasca Fasilitasi', 'label' => 'Proses perpanjangan waktu', 'roles' => $adminSistem],
        'tindak-lanjut.submit' => ['modul' => 'Pasca Fasilitasi', 'label' => 'Unggah tindak lanjut', 'roles' => ['pemohon']],
        'penetapan-perda.submit' => ['modul' => 'Pasca Fasilitasi', 'label' => 'Unggah penetapan PERDA', 'roles' => ['pemohon']],

    ],

];
