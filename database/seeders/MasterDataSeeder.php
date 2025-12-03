<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kabupaten/Kota - Semua Kab/Kota di Maluku Utara
        $kabupatenKota = [
            ['kode' => '8201', 'nama' => 'Kabupaten Halmahera Barat', 'jenis' => 'kabupaten'],
            ['kode' => '8202', 'nama' => 'Kabupaten Halmahera Tengah', 'jenis' => 'kabupaten'],
            ['kode' => '8203', 'nama' => 'Kabupaten Halmahera Utara', 'jenis' => 'kabupaten'],
            ['kode' => '8204', 'nama' => 'Kabupaten Halmahera Selatan', 'jenis' => 'kabupaten'],
            ['kode' => '8205', 'nama' => 'Kabupaten Kepulauan Sula', 'jenis' => 'kabupaten'],
            ['kode' => '8206', 'nama' => 'Kabupaten Halmahera Timur', 'jenis' => 'kabupaten'],
            ['kode' => '8207', 'nama' => 'Kabupaten Pulau Morotai', 'jenis' => 'kabupaten'],
            ['kode' => '8208', 'nama' => 'Kabupaten Pulau Taliabu', 'jenis' => 'kabupaten'],
            ['kode' => '8271', 'nama' => 'Kota Ternate', 'jenis' => 'kota'],
            ['kode' => '8272', 'nama' => 'Kota Tidore Kepulauan', 'jenis' => 'kota'],
        ];

        foreach ($kabupatenKota as $item) {
            DB::table('kabupaten_kota')->updateOrInsert(
                ['kode' => $item['kode']],
                [
                    'nama' => $item['nama'],
                    'jenis' => $item['jenis'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Jenis Dokumen
        $jenisDokumen = [
            ['kode' => 'RKPD', 'nama' => 'Rencana Kerja Pemerintah Daerah (RKPD)', 'deskripsi' => 'Dokumen perencanaan tahunan daerah'],
            ['kode' => 'RPJMD', 'nama' => 'Rencana Pembangunan Jangka Menengah Daerah (RPJMD)', 'deskripsi' => 'Dokumen perencanaan 5 tahunan'],
            ['kode' => 'RPD', 'nama' => 'Rencana Pembangunan Daerah (RPD)', 'deskripsi' => 'Dokumen perencanaan pembangunan daerah'],
        ];

        foreach ($jenisDokumen as $jenis) {
            DB::table('jenis_dokumen')->insert([
                'kode' => $jenis['kode'],
                'nama' => $jenis['nama'],
                'deskripsi' => $jenis['deskripsi'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Tahun Anggaran
        $tahunSekarang = date('Y');
        for ($i = 0; $i < 3; $i++) {
            DB::table('tahun_anggaran')->insert([
                'tahun' => $tahunSekarang + $i,
                'is_active' => $i === 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Persyaratan Dokumen untuk RKPD (contoh)
        $rkpdId = DB::table('jenis_dokumen')->where('kode', 'RKPD')->value('id');

        $persyaratan = [
            ['kode' => 'RKPD-01', 'nama' => 'Surat Permohonan Fasilitasi', 'is_wajib' => true, 'urutan' => 1],
            ['kode' => 'RKPD-02', 'nama' => 'Draft RKPD', 'is_wajib' => true, 'urutan' => 2],
            ['kode' => 'RKPD-03', 'nama' => 'Berita Acara Musrenbang', 'is_wajib' => true, 'urutan' => 3],
            ['kode' => 'RKPD-04', 'nama' => 'Dokumen RPJMD', 'is_wajib' => true, 'urutan' => 4],
            ['kode' => 'RKPD-05', 'nama' => 'Analisis Gambaran Umum Daerah', 'is_wajib' => true, 'urutan' => 5],
        ];

        foreach ($persyaratan as $item) {
            DB::table('persyaratan_dokumen')->insert([
                'jenis_dokumen_id' => $rkpdId,
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'is_wajib' => $item['is_wajib'],
                'urutan' => $item['urutan'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Tim Pokja
        DB::table('tim_pokja')->insert([
            [
                'nama' => 'Tim Pokja Perencanaan',
                'deskripsi' => 'Tim evaluasi dokumen perencanaan daerah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 6. Master Tahapan
        $tahapan = [
            ['nama_tahapan' => 'Permohonan', 'urutan' => 1],
            ['nama_tahapan' => 'Verifikasi', 'urutan' => 2],
            ['nama_tahapan' => 'Penetapan Jadwal', 'urutan' => 3],
            ['nama_tahapan' => 'Pelaksanaan', 'urutan' => 4],
            ['nama_tahapan' => 'Hasil Fasilitasi', 'urutan' => 5],
            ['nama_tahapan' => 'Penetapan PERDA/PERKADA', 'urutan' => 6],
        ];

        foreach ($tahapan as $item) {
            DB::table('master_tahapan')->insert([
                'nama_tahapan' => $item['nama_tahapan'],
                'urutan' => $item['urutan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 7. Master Urusan - Wajib Pelayanan Dasar
        $urusanWajibDasar = [
            'Pendidikan',
            'Kesehatan',
            'Pekerjaan Umum dan Penataan Ruang',
            'Perumahan Rakyat dan Kawasan Permukiman',
            'Ketenteraman, Ketertiban Umum & Perlindungan Masyarakat',
            'Sosial',
        ];

        foreach ($urusanWajibDasar as $index => $nama) {
            DB::table('master_urusan')->insert([
                'nama_urusan' => $nama,
                'kategori' => 'wajib_dasar',
                'urutan' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 8. Master Urusan - Wajib Non Pelayanan Dasar
        $urusanWajibNonDasar = [
            'Tenaga Kerja',
            'Pemberdayaan Perempuan & Perlindungan Anak',
            'Pangan',
            'Pertanahan',
            'Lingkungan Hidup',
            'Administrasi Kependudukan & Catatan Sipil',
            'Pemberdayaan Masyarakat & Desa',
            'Pengendalian Penduduk & Keluarga Berencana',
            'Perhubungan',
            'Komunikasi & Informatika',
            'Koperasi, UKM',
            'Penanaman Modal',
            'Kepemudaan & Olahraga',
            'Statistik',
            'Persandian',
            'Kebudayaan',
            'Perpustakaan',
            'Kearsipan',
        ];

        foreach ($urusanWajibNonDasar as $index => $nama) {
            DB::table('master_urusan')->insert([
                'nama_urusan' => $nama,
                'kategori' => 'wajib_non_dasar',
                'urutan' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Master Urusan - Pilihan
        $urusanPilihan = [
            'Kelautan & Perikanan',
            'Pariwisata',
            'Pertanian',
            'Kehutanan',
            'Energi & Sumber Daya Mineral',
            'Perdagangan',
            'Perindustrian',
            'Transmigrasi',
        ];

        foreach ($urusanPilihan as $index => $nama) {
            DB::table('master_urusan')->insert([
                'nama_urusan' => $nama,
                'kategori' => 'pilihan',
                'urutan' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 10. Master Kelengkapan Verifikasi (Persyaratan RKPD 2026)
        $kelengkapanVerifikasi = [
            [
                'nama_dokumen' => 'Surat Permohonan Fasilitasi dari Bupati/Walikota',
                'deskripsi' => 'Surat permohonan fasilitasi Rancangan Akhir RKPD Tahun 2026 dari Bupati/Walikota kepada Gubernur Maluku Utara',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Surat Pengantar Kepala BAPPEDA',
                'deskripsi' => 'Surat pengantar dari Kepala BAPPEDA Kabupaten/Kota kepada Kepala BAPPEDA Provinsi',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Laporan Evaluasi RKPD Semester II Tahun 2024',
                'deskripsi' => 'Laporan evaluasi hasil pelaksanaan RKPD Semester II Tahun 2024',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Dokumen Rancangan Akhir RKPD Tahun 2026',
                'deskripsi' => 'Draft dokumen RKPD Tahun 2026',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Berita Acara Musrenbang RKPD',
                'deskripsi' => 'Berita acara hasil Musrenbang RKPD',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Daftar Hadir Musrenbang RKPD',
                'deskripsi' => 'Daftar hadir peserta Musrenbang RKPD',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Bahan Analisis Pelengkap (Form E.35 dan E.36)',
                'deskripsi' => 'Form hasil Pengendalian dan Evaluasi Perumusan Kebijakan Perencanaan Pembangunan',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Dokumen Perda RPJMD / Perkada RPD',
                'deskripsi' => 'Dokumen Perda RPJMD Kabupaten/Kota Tahun berjalan atau Perkada RPD Kab-Kota',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Reviu APIP',
                'deskripsi' => 'Hasil reviu dari APIP (Aparat Pengawasan Intern Pemerintah)',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'Dokumen LKPJ Bupati/Walikota Tahun 2024',
                'deskripsi' => 'Laporan Keterangan Pertanggungjawaban (LKPJ) Bupati/Walikota Tahun 2024',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'FORM 1: Konsistensi Tujuan Dan Sasaran',
                'deskripsi' => 'Konsistensi Tujuan Dan Sasaran RPJMD Tahun Pelaksanaan 2026 Dan RKPD Tahun 2026',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'FORM 2: Konsistensi Program Dan Pagu Pendanaan',
                'deskripsi' => 'Konsistensi Program Dan Pagu Pendanaan RKPD Tahun 2026 Dan RPJMD Tahun Pelaksanaan 2025',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'FORM 3: Daftar Indikator Kinerja',
                'deskripsi' => 'Daftar Indikator Kinerja Penyelenggaraan Pemerintah Daerah RKPD Tahun 2026',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'FORM 4: Keselarasan Indikator Kinerja Makro',
                'deskripsi' => 'Daftar Keselarasan Pencapaian Indikator Kinerja Makro Pembangunan Provinsi dengan Kabupaten/Kota',
                'wajib' => true,
            ],
            [
                'nama_dokumen' => 'FORM 5: Tindak Lanjut Kebijakan Prioritas Nasional',
                'deskripsi' => 'Daftar Tindak Lanjut Dukungan Pemerintah Daerah Atas Kebijakan Prioritas Nasional Tahun 2026',
                'wajib' => true,
            ],
        ];

        foreach ($kelengkapanVerifikasi as $item) {
            DB::table('master_kelengkapan_verifikasi')->insert([
                'nama_dokumen' => $item['nama_dokumen'],
                'deskripsi' => $item['deskripsi'],
                'wajib' => $item['wajib'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Master data seeded successfully!\n";
        echo "- Tahapan: 6 items\n";
        echo "- Urusan Wajib Dasar: 6 items\n";
        echo "- Urusan Wajib Non Dasar: 18 items\n";
        echo "- Urusan Pilihan: 8 items\n";
        echo "- Kelengkapan Verifikasi: 15 items\n";
    }
}
