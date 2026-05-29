<?php

namespace App\Services;

use App\Models\Permohonan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;

class HasilFasilitasiDocumentService
{
    // F4 content width: 21cm − 1.9cm(kiri) − 2.5cm(kanan) = 16.6cm = 9412 twips
    private const CONTENT_WIDTH = 9412;
    private const NO_WIDTH      = 471;   //  5%
    private const BAB_WIDTH     = 2353;  // 25%
    private const CATATAN_WIDTH = 6588;  // 70%
    private const URUSAN_WIDTH  = 7059;  // 75%
    private const KET_WIDTH     = 1882;  // 20%

    private const BULAN_ID = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /**
     * Build dokumen DOCX dan simpan ke file. Mengembalikan filepath relatif storage.
     */
    public function generateDocx(
        Permohonan $permohonan,
        $sistematika,
        $urusan,
        $form        = null,
        $rekomendasi = null,
        $kelengkapan = null
    ): string {
        Log::info('HasilFasilitasi: mulai generate DOCX', [
            'permohonan_id' => $permohonan->id,
            'php_version'   => PHP_VERSION,
            'os'            => PHP_OS,
            'sys_temp_dir'  => sys_get_temp_dir(),
            'iconv_avail'   => function_exists('iconv'),
        ]);

        $phpWord      = $this->buildDocument($permohonan, $sistematika, $urusan, $form, $rekomendasi, $kelengkapan);
        $kabkota      = $permohonan->kabupatenKota->nama;
        $tahun        = $permohonan->tahun ?? date('Y');
        $jenisDokumen = ucwords(strtolower($permohonan->jenisDokumen->nama ?? 'Dokumen'));

        $safeKabkota = str_replace([' ', '/'], '_', $kabkota);
        $safeJenis   = str_replace(' ', '_', $jenisDokumen);
        $filename    = 'Draft_Lampiran_Hasil_Fasilitasi_' . $safeJenis . '_' . $safeKabkota . '_' . $tahun . '.docx';
        $filepath    = 'hasil-fasilitasi/' . $filename;
        $fullPath    = storage_path('app/public/' . $filepath);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        Log::info('HasilFasilitasi: menyimpan DOCX', [
            'path'         => $fullPath,
            'dir_writable' => is_writable(dirname($fullPath)),
        ]);

        try {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($fullPath);
        } catch (\Throwable $e) {
            Log::error('HasilFasilitasi: gagal menyimpan DOCX', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        Log::info('HasilFasilitasi: DOCX berhasil disimpan', [
            'path'      => $fullPath,
            'file_size' => file_exists($fullPath) ? filesize($fullPath) : null,
        ]);

        // Integrity check: pastikan file adalah ZIP valid dengan document.xml yang bisa di-parse
        $this->checkDocxIntegrity($fullPath);

        return $filepath;
    }

    /**
     * Build seluruh konten dokumen ke dalam PhpWord dan kembalikan AdvancedService.
     * Digunakan bersama oleh generateDocx() dan streamDocx().
     */
    private function buildDocument(
        Permohonan $permohonan,
        $sistematika,
        $urusan,
        $form        = null,
        $rekomendasi = null,
        $kelengkapan = null
    ): PhpWord {
        // Wajib: escape XML special chars (&, <, >) agar DOCX tidak corrupt
        Settings::setOutputEscapingEnabled(true);

        // Gunakan temp dir yang pasti writable di semua environment (lokal & production)
        $tempDir = storage_path('app/temp/phpword');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        Settings::setTempDir($tempDir);

        Log::debug('HasilFasilitasi: temp dir', [
            'path'     => $tempDir,
            'writable' => is_writable($tempDir),
        ]);

        $kabkota      = $permohonan->kabupatenKota->nama;
        $tahun        = $permohonan->tahun ?? date('Y');
        $jenisDokumen = ucwords(strtolower($permohonan->jenisDokumen->nama ?? 'Dokumen'));
        $jenisWilayah = ucfirst(strtolower($permohonan->kabupatenKota->jenis ?? 'Kabupaten'));
        $ranperkada   = strtolower($jenisWilayah) === 'kota' ? 'Ranperwal' : 'Ranperbup';
        $jabatanTtd   = strtolower($jenisWilayah) === 'kota' ? 'Walikota' : 'Bupati';
        $tanggal      = $this->tanggalIndonesia();

        $form        = $form        ?? collect();
        $rekomendasi = $rekomendasi ?? collect();
        $kelengkapan = $kelengkapan ?? collect();

        // ─── Inisialisasi library ─────────────────────────────────────────────
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        // F4 paper (21 cm × 33 cm), margin: kiri 1,9 – atas 2,1 – bawah 2,3 – kanan 2,5 cm
        $section = $phpWord->addSection([
            'pageSizeW'    => 11907, // 21 cm × 567 twips/cm
            'pageSizeH'    => 18711, // 33 cm × 567 twips/cm
            'marginTop'    => 1191,  // 2,1 cm
            'marginBottom' => 1304,  // 2,3 cm
            'marginLeft'   => 1077,  // 1,9 cm
            'marginRight'  => 1418,  // 2,5 cm
        ]);

        // ─── Font & paragraph styles ─────────────────────────────────────────
        $fN  = ['name' => 'Arial', 'size' => 12];               // normal
        $fB  = ['name' => 'Arial', 'size' => 12, 'bold' => true]; // bold
        $pL  = ['lineHeight' => 1.5, 'spaceAfter' => 0, 'spaceBefore' => 0, 'alignment' => 'both'];  // justify
        $pC  = ['lineHeight' => 1.5, 'spaceAfter' => 0, 'spaceBefore' => 0, 'alignment' => 'center']; // center
        $pIn = ['lineHeight' => 1.5, 'spaceAfter' => 0, 'spaceBefore' => 0, 'alignment' => 'both',   // justify + hanging indent
                'indentation' => ['left' => 440, 'firstLine' => -440]];

        // ─── 1. Header kiri atas ─────────────────────────────────────────────
        $section->addText('Lampiran', $fN, $pL);
        $section->addText('Nomor      :', $fN, $pL);
        $section->addText('Tanggal    : ' . $tanggal, $fN, $pL);
        $section->addTextBreak(1);

        // ─── 2. Judul (rata tengah, bold) ────────────────────────────────────
        $section->addText('HASIL FASILITASI', $fB, $pC);
        $section->addText(
            'RANCANGAN AKHIR ' . strtoupper($jenisDokumen) . ' ' . strtoupper($jenisWilayah) . ' ' . strtoupper($kabkota),
            $fB, $pC
        );
        $section->addText('TAHUN ' . $tahun, $fB, $pC);
        $section->addTextBreak(1);

        // ─── 3. I. PENDAHULUAN ───────────────────────────────────────────────
        $section->addText('I.   PENDAHULUAN', $fN, $pL);
        $section->addText(
            'a.   Bahwa Sesuai amanat pasal 102 dalam Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian Dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah Dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, Dan Rencana Kerja Pemerintah Daerah disebutkan bahwa Gubernur dan Bupati/Walikota menyampaikan Rancangan Perkada RKPD Provinsi dan Kabupaten/Kota untuk difasilitasi;',
            $fN, $pIn
        );
        $section->addText(
            'b.   Fasilitasi sebagaimana dimaksud merupakan tindakan pembinaan berupa pemberian pedoman dan petunjuk teknis, arahan, bimbingan teknis, supervisi, asistensi dan kerja sama serta monitoring dan evaluasi yang dilakukan oleh Gubernur kepada kabupaten/kota terhadap materi muatan rancangan produk hukum daerah berbentuk peraturan sebelum ditetapkan.',
            $fN, $pIn
        );
        $section->addTextBreak(1);

        // ─── 4. II. DASAR HUKUM ──────────────────────────────────────────────
        $section->addText('II.   DASAR HUKUM', $fN, $pL);
        $dasarHukum = [
            'a' => 'Undang-Undang Nomor 25 Tahun 2004 tentang Sistem Perencanaan Pembangunan Nasional (Lembaran Negara Republik Indonesia Tahun 2004 Nomor 104, Tambahan Lembaran Negara Republik Indonesia Nomor 4421);',
            'b' => 'Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintah Daerah (Lembaran Negara Republik Indonesia Tahun 2014 Nomor 244 Tambahan Lembaran Negara Republik Indonesia Nomor 5587) sebagaimana telah beberapa kali diubah, terakhir dengan Undang-Undang Nomor 9 Tahun 2015 tentang Perubahan Kedua atas Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah (Lembaran Negara Republik Indonesia tahun 2015 Nomor 58, Tambahan Lembar Negara Republik Indonesia Nomor 5679);',
            'c' => 'Peraturan Pemerintah Nomor 12 Tahun 2019 tentang Pengelolaan Keuangan Daerah (Lembaran Negara Republik Indonesia Tahun 2019 Nomor 42, Tambahan Lembaran Negara Republik Indonesia Nomor 6322);',
            'd' => 'Peraturan Menteri Dalam Negeri Nomor 80 Tahun 2015 tentang Produk Hukum Daerah (Berita Negara Republik Indonesia Tahun 2015 Nomor 2036) sebagaimana telah diubah dengan Peraturan Menteri Dalam Negeri Nomor 120 Tahun 2018 tentang Perubahan atas Peraturan Menteri Dalam Negeri Nomor 80 Tahun 2015 tentang Pembentukan Produk Hukum Daerah (Berita Negara Republik Indonesia Tahun 2021 Nomor 157);',
            'e' => 'Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian Dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah Dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, Dan Rencana Kerja Pemerintah Daerah (Berita Negara Republik Indonesia Tahun 2017 Nomor 1312);',
            'f' => 'Peraturan Menteri Dalam Negeri Nomor 70 Tahun 2019 tentang Sistem Informasi Pemerintahan Daerah (Berita Negara Republik Indonesia Tahun 2019 Nomor 1114);',
            'g' => 'Peraturan Menteri Dalam Negeri Republik Indonesia Nomor 10 Tahun 2025 tentang Pedoman Penyusunan Rencana Kerja Pemerintah Daerah Tahun 2026;',
            'h' => 'Instruksi Menteri Dalam Negeri Nomor 2 Tahun 2025 tentang Pedoman Penyusunan Rencana Pembangunan Jangka Menengah Daerah dan Rencana Strategis Perangkat Daerah Tahun 2025-2029;',
            'i' => 'Keputusan Menteri Dalam Negeri Nomor 900.1.15.5-3406 Tahun 2024 tentang Perubahan Atas Keputusan Menteri Dalam Negeri Nomor 050-5889 Tahun 2021 tentang Hasil Verifikasi, Validasi dan Inventarisasi Pemutakhiran Klasifikasi, Kodefikasi dan Nomenklatur Perencanaan Pembangunan dan Keuangan Daerah.',
        ];
        foreach ($dasarHukum as $letter => $text) {
            $section->addText($letter . '.   ' . $text, $fN, $pIn);
        }
        $section->addTextBreak(1);

        // ─── 5. III. MAKSUD DAN TUJUAN ───────────────────────────────────────
        $section->addText('III.   MAKSUD DAN TUJUAN', $fN, $pL);
        $section->addText(
            'Maksud dan tujuan dari fasilitasi Rancangan Perkada tentang ' . $jenisDokumen
            . ' ' . $jenisWilayah . ' ' . $kabkota . ' Tahun ' . $tahun
            . ' adalah memberikan masukan dan saran penyempurnaan terhadap Rancangan Akhir '
            . $jenisDokumen . ' ' . $jenisWilayah . ' ' . $kabkota . ' Tahun ' . $tahun
            . ' sehingga kebijakan perencanaan pembangunan tahunan ' . $jenisWilayah . ' '
            . $kabkota . ' lebih berkualitas yang mengarah kepada semakin baiknya kinerja pembangunan daerah.',
            $fN, $pL
        );
        $section->addTextBreak(1);

        // ─── 6. IV. KELENGKAPAN PERSYARATAN FASILITASI ───────────────────────
        $section->addText('IV.   KELENGKAPAN PERSYARATAN FASILITASI', $fN, $pL);
        $section->addText(
            'Fasilitasi terhadap Rancangan Perkada ' . $jenisDokumen . ' ' . $jenisWilayah
            . ' ' . $kabkota . ' Tahun ' . $tahun
            . ' dapat dilaksanakan berdasarkan persyaratan yang diterima. Selanjutnya telah dilakukan pemeriksaan terhadap persyaratan dimaksud dengan beberapa catatan antara lain:',
            $fN, $pL
        );
        if ($kelengkapan->count() > 0) {
            foreach ($kelengkapan as $idx => $doc) {
                $namaDok = $doc->masterKelengkapan->nama_dokumen ?? $doc->file_name ?? '-';
                $section->addText(($idx + 1) . '.   ' . $namaDok, $fN, $pIn);
            }
        } else {
            $section->addText('1.   -', $fN, $pIn);
        }
        $section->addTextBreak(1);

        // ─── 7. V. HASIL FASILITASI ──────────────────────────────────────────
        $section->addText('V.   HASIL FASILITASI', $fN, $pL);
        $section->addText(
            'Berdasarkan hasil pencermatan serta diskusi Tim Fasilitasi dengan Pemerintah '
            . $jenisWilayah . ' ' . $kabkota . ' terhadap Rancangan Akhir ' . $jenisDokumen
            . ' ' . $jenisWilayah . ' ' . $kabkota . ' Tahun ' . $tahun
            . ', terdapat beberapa hal yang perlu dilakukan penyesuaian dan penyempurnaan baik sistematika dan teknik penulisan maupun substansi dalam rancangan akhir '
            . $jenisDokumen . ' ' . $jenisWilayah . ' ' . $kabkota
            . '. Beberapa hal yang perlu disempurnakan dalam ' . strtoupper($ranperkada)
            . ' tentang ' . $jenisDokumen . ' ' . $jenisWilayah . ' ' . $kabkota
            . ' Tahun ' . $tahun . ', antara lain:',
            $fN, $pL
        );
        $section->addTextBreak(1);

        // ─── 7a. V.1 Sistematika ─────────────────────────────────────────────
        $section->addText('1.   Sistematika dan Substansi Rancangan Akhir ' . $jenisDokumen, $fN, $pL);
        $section->addText(
            'a.   Sistematika Rancangan Akhir ' . $jenisDokumen . ' ' . $jenisWilayah . ' '
            . $kabkota . ' Tahun ' . $tahun
            . ' Telah Sesuai dengan ayat (1) Pasal 79 Peraturan Menteri Dalam Negeri Nomor 86 Tahun 2017 tentang Tata Cara Perencanaan, Pengendalian dan Evaluasi Pembangunan Daerah, Tata Cara Evaluasi Rancangan Peraturan Daerah Tentang Rencana Pembangunan Jangka Panjang Daerah dan Rencana Pembangunan Jangka Menengah Daerah, Serta Tata Cara Perubahan Rencana Pembangunan Jangka Panjang Daerah, Rencana Pembangunan Jangka Menengah Daerah, dan Rencana Kerja Pemerintah Daerah.',
            $fN, $pIn
        );
        $section->addText(
            'b.   Catatan penyempurnaan terhadap sistematika dan substansi Rancangan Peraturan '
            . $jabatanTtd . ' tentang ' . $jenisDokumen . ' ' . $jenisWilayah . ' '
            . $kabkota . ' Tahun ' . $tahun . ' sebagaimana pada tabel berikut:',
            $fN, $pIn
        );
        $section->addTextBreak(1);

        // Caption tabel sistematika
        $section->addText('Tabel 1.1', $fN, $pC);
        $section->addText('Catatan Penyempurnaan', $fN, $pC);
        $section->addText(
            $ranperkada . ' tentang ' . $jenisDokumen . ' Tahun ' . $tahun,
            $fN, $pC
        );
        $section->addTextBreak(1);
        $this->addSistematikaTable($section, $sistematika);
        $section->addTextBreak(1);

        // ─── 7b. V.2 Konsistensi & Keselarasan ───────────────────────────────
        $section->addText('2.   Konsistensi dan Keselarasan Perencanaan Pembangunan', $fN, $pL);
        if ($form->count() > 0) {
            foreach ($form as $idx => $item) {
                $letter  = chr(96 + $idx + 1);
                $catatan = $this->cleanText($item->catatan ?? '');
                $section->addText($letter . '.   ' . $catatan, $fN, $pIn);
            }
        } else {
            $section->addText('a.   -', $fN, $pIn);
        }
        $section->addTextBreak(1);

        // ─── 7c. V.3 Urusan Pemerintahan (halaman baru) ──────────────────────
        $section->addPageBreak();
        $section->addText('3.   Masukan Terkait Penyelenggaraan Urusan Pemerintah Daerah', $fN, $pL);
        $this->addUrusanTable($section, $urusan);
        $section->addTextBreak(1);

        // ─── 8. VI. REKOMENDASI (halaman baru) ───────────────────────────────
        $section->addPageBreak();
        $section->addText('VI.   REKOMENDASI', $fN, $pL);
        if ($rekomendasi->count() > 0) {
            foreach ($rekomendasi as $idx => $item) {
                $num     = $idx + 1;
                $catatan = $this->cleanText($item->catatan ?? '');
                $section->addText($num . '.   ' . $catatan, $fN, $pIn);
            }
        } else {
            $section->addText('1.   -', $fN, $pIn);
        }

        // ─── 9. VII. PENUTUP (halaman baru) ──────────────────────────────────
        $section->addPageBreak();
        $section->addText('VII.   PENUTUP', $fN, $pL);
        $section->addText(
            'Demikian hasil fasilitasi terhadap Rancangan Perkada tentang ' . $jenisDokumen
            . ' ' . $jenisWilayah . ' ' . $kabkota . ' Tahun ' . $tahun
            . '. Masukan dari hasil fasilitasi dijadikan penyempurnaan Rancangan Perkada tentang '
            . $jenisDokumen . ' ' . $jenisWilayah . ' ' . $kabkota . ' Tahun ' . $tahun
            . ' sebelum ditetapkan menjadi Perkada tentang ' . $jenisDokumen
            . ' ' . $jenisWilayah . ' ' . $kabkota . ' Tahun ' . $tahun . '.',
            $fN, $pL
        );
        $section->addTextBreak(3);

        // ─── 10. Tanda tangan (kanan bawah) ─────────────────────────────────
        $phpWord->addTableStyle('ttd', [
            'borderSize'  => 0,
            'borderColor' => 'FFFFFF',
            'width'       => self::CONTENT_WIDTH,
            'unit'        => 'dxa',
        ]);
        $sigTable = $section->addTable('ttd');
        $sigRow   = $sigTable->addRow();

        $leftW  = intval(self::CONTENT_WIDTH * 0.55);
        $rightW = self::CONTENT_WIDTH - $leftW;

        $sigRow->addCell($leftW, ['borderSize' => 0, 'borderColor' => 'FFFFFF'])
               ->addText('');

        $sigCell = $sigRow->addCell($rightW, ['borderSize' => 0, 'borderColor' => 'FFFFFF']);
        $pTtd    = ['alignment' => 'center', 'lineHeight' => 1.5, 'spaceAfter' => 0, 'spaceBefore' => 0];
        $sigCell->addText('Kepala Badan Perencanaan Pembangunan Daerah', $fN, $pTtd);
        $sigCell->addText('Provinsi Maluku Utara', $fN, $pTtd);

        return $phpWord;
    }

    /**
     * Tambah tabel Sistematika ke dalam section PhpWord.
     */
    private function addSistematikaTable($section, $sistematika): void
    {
        $fCell   = ['name' => 'Arial', 'size' => 12];
        $pCenter = ['alignment' => 'center'];

        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'width'       => self::CONTENT_WIDTH,
            'unit'        => 'dxa',
        ];
        $table = $section->addTable($tableStyle);

        // Header row – 3 sel terpisah
        $headerRow = $table->addRow();
        $this->addHeaderCell($headerRow, self::NO_WIDTH, 'No.');
        $this->addHeaderCell($headerRow, self::BAB_WIDTH, 'Bab/Sub Bab');
        $this->addHeaderCell($headerRow, self::CATATAN_WIDTH, 'Catatan Penyempurnaan');

        if ($sistematika->count() === 0) {
            $row = $table->addRow();
            $row->addCell(self::NO_WIDTH)->addText('');
            $row->addCell(self::BAB_WIDTH)
                ->addText('Tidak ada catatan penyempurnaan', ['italic' => true] + $fCell);
            $row->addCell(self::CATATAN_WIDTH)->addText('');
            return;
        }

        $grouped    = $this->groupSistematika($sistematika);
        $currentBab = null;
        $counter    = 1;

        foreach ($grouped as $item) {
            if ($currentBab !== $item['bab_id']) {
                // Bab header – teks di kolom Bab/Sub Bab
                $row = $table->addRow();
                $row->addCell(self::NO_WIDTH)->addText('');
                $row->addCell(self::BAB_WIDTH)
                    ->addText(
                        $this->formatTitleWithRomanNumerals($item['bab_nama']),
                        $fCell
                    );
                $row->addCell(self::CATATAN_WIDTH)->addText('');
                $currentBab = $item['bab_id'];
            }

            $row = $table->addRow();

            $row->addCell(self::NO_WIDTH, ['valign' => 'top'])
                ->addText((string) $counter, $fCell, $pCenter);

            $row->addCell(self::BAB_WIDTH, ['valign' => 'top'])
                ->addText(
                    $this->formatTitleWithRomanNumerals($this->cleanText($item['sub_bab'])),
                    $fCell
                );

            $catatanCell  = $row->addCell(self::CATATAN_WIDTH, ['valign' => 'top']);
            $totalCatatan = count($item['catatan']);
            foreach ($item['catatan'] as $idx => $catatan) {
                $prefix = $totalCatatan > 1 ? ($idx + 1) . '. ' : '';
                $plain  = $prefix . $this->cleanText($catatan ?? '');
                $catatanCell->addText($plain, $fCell);
            }

            $counter++;
        }
    }

    /**
     * Tambah tabel Urusan ke dalam section PhpWord.
     */
    private function addUrusanTable($section, $urusan): void
    {
        $fCell   = ['name' => 'Arial', 'size' => 12];
        $pCenter = ['alignment' => 'center'];

        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'width'       => self::CONTENT_WIDTH,
            'unit'        => 'dxa',
        ];
        $table = $section->addTable($tableStyle);

        // Header row – 3 sel terpisah
        $headerRow = $table->addRow();
        $this->addHeaderCell($headerRow, self::NO_WIDTH, 'No.');
        $this->addHeaderCell($headerRow, self::URUSAN_WIDTH, 'Masukan/Saran');
        $this->addHeaderCell($headerRow, self::KET_WIDTH, 'Keterangan');

        if ($urusan->count() === 0) {
            $row = $table->addRow();
            $row->addCell(self::NO_WIDTH)->addText('');
            $row->addCell(self::URUSAN_WIDTH)
                ->addText('Tidak ada catatan masukan', ['italic' => true] + $fCell);
            $row->addCell(self::KET_WIDTH)->addText('');
            return;
        }

        $grouped = $this->groupUrusan($urusan);

        foreach ($grouped as $group) {
            // Urusan group header – teks di kolom Masukan/Saran
            $row = $table->addRow();
            $row->addCell(self::NO_WIDTH)->addText('');
            $row->addCell(self::URUSAN_WIDTH)
                ->addText(
                    'Urusan ' . $this->cleanText($group['nama']),
                    $fCell
                );
            $row->addCell(self::KET_WIDTH)->addText('');

            foreach ($group['items'] as $index => $catatan) {
                $row = $table->addRow();

                $row->addCell(self::NO_WIDTH, ['valign' => 'top'])
                    ->addText(($index + 1) . '.', $fCell, $pCenter);

                $row->addCell(self::URUSAN_WIDTH, ['valign' => 'top'])
                    ->addText(
                        $this->cleanText($catatan ?? ''),
                        $fCell
                    );

                $row->addCell(self::KET_WIDTH, ['valign' => 'top'])->addText('');
            }
        }
    }

    /**
     * Tambah cell header tabel dengan style standar.
     */
    private function addHeaderCell($row, int $width, string $text): void
    {
        $row->addCell($width, ['valign' => 'center'])
            ->addText(
                $text,
                ['name' => 'Arial', 'size' => 12],
                ['alignment' => 'center']
            );
    }

    /**
     * Group sistematika by bab dan sub_bab.
     */
    private function groupSistematika($sistematika): array
    {
        $grouped = [];

        foreach ($sistematika as $item) {
            $babId  = $item->master_bab_id;
            $subBab = $item->sub_bab ?: ($item->masterBab->nama_bab ?? '-');
            $key    = $babId . '|' . $subBab;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'bab_id'   => $babId,
                    'bab_nama' => $item->masterBab->nama_bab ?? '-',
                    'sub_bab'  => $subBab,
                    'catatan'  => [],
                ];
            }

            $grouped[$key]['catatan'][] = $item->catatan_penyempurnaan;
        }

        return $grouped;
    }

    /**
     * Group urusan by master_urusan_id.
     */
    private function groupUrusan($urusan): array
    {
        $grouped = [];

        foreach ($urusan as $item) {
            $id = $item->master_urusan_id;

            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'nama'  => $item->masterUrusan->nama_urusan ?? $item->masterUrusan->nama ?? '-',
                    'items' => [],
                ];
            }

            $grouped[$id]['items'][] = $item->catatan_masukan;
        }

        return $grouped;
    }

    /**
     * Format judul dengan angka Romawi kapital.
     */
    private function formatTitleWithRomanNumerals(string $title): string
    {
        $title = ucwords(strtolower($title));

        $title = preg_replace_callback(
            '/\b(i{1,3}|iv|vi{0,3}|ix|xi{0,3}|xiv|xv|xvi{0,3}|xix|xx)\b/i',
            fn($m) => strtoupper($m[0]),
            $title
        );

        return $title;
    }

    /**
     * Periksa integritas DOCX: apakah ZIP valid dan document.xml bisa di-parse.
     * Hanya untuk logging diagnostik — tidak melempar exception.
     */
    private function checkDocxIntegrity(string $fullPath): void
    {
        $zip    = new \ZipArchive();
        $result = $zip->open($fullPath);

        if ($result !== true) {
            Log::error('HasilFasilitasi: DOCX bukan ZIP yang valid', [
                'zip_error_code' => $result,
                'path'           => $fullPath,
            ]);
            return;
        }

        $hasDocXml = $zip->locateName('word/document.xml') !== false;
        $docXml    = $hasDocXml ? $zip->getFromName('word/document.xml') : null;
        $numFiles  = $zip->numFiles;
        $zip->close();

        libxml_use_internal_errors(true);
        $xmlValid  = $docXml ? (simplexml_load_string($docXml) !== false) : false;
        $xmlErrors = array_map(fn($e) => trim($e->message), array_slice(libxml_get_errors(), 0, 5));
        libxml_clear_errors();

        Log::info('HasilFasilitasi: DOCX integrity check', [
            'is_valid_zip'   => true,
            'num_files'      => $numFiles,
            'has_document'   => $hasDocXml,
            'xml_valid'      => $xmlValid,
            'xml_errors'     => $xmlErrors,
            'xml_length'     => $docXml ? strlen($docXml) : null,
        ]);
    }

    /**
     * Bersihkan teks agar aman ditulis ke XML/DOCX:
     * - strip HTML tags & decode entities
     * - buang BOM dan zero-width characters
     * - normalisasi NBSP dan whitespace non-standar → spasi biasa
     * - pastikan UTF-8 valid (buang byte invalid via iconv)
     * - hapus karakter kontrol ilegal XML 1.0 (kecuali tab U+0009, LF U+000A, CR U+000D)
     * - normalisasi line endings → spasi tunggal
     */
    private function cleanText(string $html): string
    {
        // 1. Strip HTML tags & decode entities (termasuk &amp; &lt; &gt; &nbsp; dll)
        $text = strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // 2. Buang BOM UTF-8 (EF BB BF) jika ada di awal
        $text = ltrim($text, "\xEF\xBB\xBF");

        // 3. Normalisasi NBSP (U+00A0 = \xc2\xa0) dan spasi khusus lainnya ke spasi biasa
        //    Termasuk: thin space, en space, em space, narrow no-break space, dll.
        $text = preg_replace('/[\x{00A0}\x{00AD}\x{200B}\x{200C}\x{200D}\x{200E}\x{200F}\x{FEFF}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}]/u', ' ', $text) ?? $text;

        // 4. Buang byte UTF-8 tidak valid DULU — preg_replace /u mengembalikan null
        //    jika string mengandung byte invalid, sehingga harus dibersihkan lebih dulu.
        if (function_exists('iconv')) {
            $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
            if ($cleaned === false || $cleaned !== $text) {
                Log::warning('HasilFasilitasi: cleanText — byte UTF-8 invalid ditemukan dan dibuang', [
                    'original_len' => strlen($text),
                    'cleaned_len'  => $cleaned === false ? null : strlen($cleaned),
                    'preview'      => mb_substr($text, 0, 80, 'UTF-8'),
                ]);
            }
            $text = $cleaned ?: '';
        } elseif (!mb_check_encoding($text, 'UTF-8')) {
            Log::warning('HasilFasilitasi: cleanText — UTF-8 tidak valid (iconv tidak tersedia, pakai mb_convert_encoding)');
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // 5. Hapus karakter kontrol ilegal XML 1.0: U+0000–U+0008, U+000B, U+000C, U+000E–U+001F, U+007F
        $before = $text;
        $text   = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
        if ($text !== $before) {
            Log::warning('HasilFasilitasi: cleanText — karakter kontrol XML ilegal ditemukan dan dibuang');
        }

        // 6. Normalisasi line endings (CR+LF / CR → LF), lalu ganti newline dengan spasi
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = str_replace("\n", ' ', $text);

        // 7. Rapikan spasi berulang
        $text = preg_replace('/ {2,}/', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Format tanggal ke format Indonesia (misal: 30 Mei 2026).
     */
    private function tanggalIndonesia(?\DateTimeInterface $date = null): string
    {
        $d = $date ?? new \DateTime();
        return $d->format('j') . ' ' . self::BULAN_ID[(int)$d->format('n')] . ' ' . $d->format('Y');
    }

    public function saveDocument(string $content, string $filename): string
    {
        $filepath = 'hasil-fasilitasi/' . $filename;
        Storage::disk('public')->put($filepath, $content);
        return $filepath;
    }

    public function getStoragePath(string $filepath): string
    {
        return storage_path('app/public/' . $filepath);
    }
}
