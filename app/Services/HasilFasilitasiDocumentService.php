<?php

namespace App\Services;

use App\Models\Permohonan;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

class HasilFasilitasiDocumentService
{
    // Lebar kolom dalam twips (A4 dengan margin 2cm kiri/kanan = ~9072 twips total)
    private const CONTENT_WIDTH = 9072;
    private const NO_WIDTH      = 454;   // 5%
    private const BAB_WIDTH     = 2268;  // 25%
    private const CATATAN_WIDTH = 6350;  // 70%
    private const URUSAN_WIDTH  = 6804;  // 75%
    private const KET_WIDTH     = 1814;  // 20%

    /**
     * Generate dokumen DOCX menggunakan PhpOffice/PhpWord.
     * HTML dari TinyMCE di-render native ke format Word sehingga format (bold,
     * italic, list, dll) terjaga dan konsisten dengan tampilan editor.
     *
     * @return string filepath relatif dari storage/public
     */
    public function generateDocx(Permohonan $permohonan, $sistematika, $urusan): string
    {
        $kabkota = $permohonan->kabupatenKota->nama;
        $tahun   = $permohonan->tahun ?? date('Y');

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'marginTop'    => 1134, // 2 cm
            'marginBottom' => 1134,
            'marginLeft'   => 1701, // 3 cm
            'marginRight'  => 1134,
        ]);

        // Pengantar Sistematika
        $section->addText(
            'Catatan penyempurnaan terhadap sistematika dan substansi sebagai berikut:',
            ['name' => 'Arial', 'size' => 11]
        );
        $section->addTextBreak(1);

        $this->addSistematikaTable($section, $sistematika);
        $section->addTextBreak(1);

        // Pengantar Urusan
        $section->addText(
            'Masukan terkait penyelenggaraan urusan Pemerintah Daerah sebagai berikut:',
            ['name' => 'Arial', 'size' => 11]
        );
        $section->addTextBreak(1);

        $this->addUrusanTable($section, $urusan);

        // Simpan ke storage
        $safeName = str_replace([' ', '/'], '_', $kabkota);
        $filename = 'Hasil_Fasilitasi_' . $safeName . '_' . $tahun . '.docx';
        $filepath = 'hasil-fasilitasi/' . $filename;
        $fullPath = storage_path('app/public/' . $filepath);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        return $filepath;
    }

    /**
     * Tambah tabel Sistematika ke dalam section PhpWord
     */
    private function addSistematikaTable($section, $sistematika): void
    {
        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'width'       => self::CONTENT_WIDTH,
            'unit'        => 'dxa',
        ];
        $table = $section->addTable($tableStyle);

        // Baris header — tblHeader agar berulang di halaman baru dan tidak terpisah dari isi
        $headerRow = $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
        $this->addHeaderCell($headerRow, self::NO_WIDTH, 'No.');
        $this->addHeaderCell($headerRow, self::BAB_WIDTH, 'Bab/Sub Bab');
        $this->addHeaderCell($headerRow, self::CATATAN_WIDTH, 'Catatan Penyempurnaan');

        if ($sistematika->count() === 0) {
            $row  = $table->addRow(null, ['cantSplit' => true]);
            $cell = $row->addCell(self::CONTENT_WIDTH, ['gridSpan' => 3]);
            $cell->addText('Tidak ada catatan penyempurnaan', ['italic' => true], ['alignment' => 'center']);
            return;
        }

        $grouped    = $this->groupSistematika($sistematika);
        $currentBab = null;
        $counter    = 1;

        foreach ($grouped as $item) {
            // Baris header bab (satu baris per bab, colspan 3)
            if ($currentBab !== $item['bab_id']) {
                $row  = $table->addRow(null, ['cantSplit' => true]);
                $cell = $row->addCell(self::CONTENT_WIDTH, [
                    'gridSpan' => 3,
                    'bgColor'  => 'e8f4f8',
                ]);
                $cell->addText(
                    $this->formatTitleWithRomanNumerals($item['bab_nama']),
                    ['name' => 'Arial', 'size' => 11]
                );
                $currentBab = $item['bab_id'];
            }

            $row = $table->addRow(null, ['cantSplit' => true]);

            // Kolom nomor
            $row->addCell(self::NO_WIDTH, ['valign' => 'top'])
                ->addText((string) $counter, ['name' => 'Arial', 'size' => 11], ['alignment' => 'center']);

            // Kolom bab/sub bab
            $row->addCell(self::BAB_WIDTH, ['valign' => 'top'])
                ->addText(
                    $this->formatTitleWithRomanNumerals(html_entity_decode($item['sub_bab'], ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                    ['name' => 'Arial', 'size' => 11]
                );

            // Kolom catatan — render HTML TinyMCE langsung ke PhpWord
            $catatanCell = $row->addCell(self::CATATAN_WIDTH, ['valign' => 'top']);
            $totalCatatan = count($item['catatan']);
            foreach ($item['catatan'] as $idx => $catatan) {
                if ($idx > 0) {
                    $catatanCell->addTextBreak();
                }
                $prefix = $totalCatatan > 1 ? ($idx + 1) . '. ' : '';
                Html::addHtml($catatanCell, $prefix . $this->sanitizeHtml($catatan), false, false);
            }

            $counter++;
        }
    }

    /**
     * Tambah tabel Urusan ke dalam section PhpWord
     */
    private function addUrusanTable($section, $urusan): void
    {
        $tableStyle = [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'width'       => self::CONTENT_WIDTH,
            'unit'        => 'dxa',
        ];
        $table = $section->addTable($tableStyle);

        // Baris header — tblHeader agar tidak terpisah dari isi
        $headerRow = $table->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
        $this->addHeaderCell($headerRow, self::NO_WIDTH, 'No.');
        $this->addHeaderCell($headerRow, self::URUSAN_WIDTH, 'Masukan/Saran');
        $this->addHeaderCell($headerRow, self::KET_WIDTH, 'Keterangan');

        if ($urusan->count() === 0) {
            $row  = $table->addRow(null, ['cantSplit' => true]);
            $cell = $row->addCell(self::CONTENT_WIDTH, ['gridSpan' => 3]);
            $cell->addText('Tidak ada catatan masukan', ['italic' => true], ['alignment' => 'center']);
            return;
        }

        $grouped = $this->groupUrusan($urusan);

        foreach ($grouped as $group) {
            // Baris header urusan (colspan 3)
            $row        = $table->addRow(null, ['cantSplit' => true]);
            $headerCell = $row->addCell(self::CONTENT_WIDTH, [
                'gridSpan' => 3,
                'bgColor'  => 'f0f0f0',
            ]);
            $headerCell->addText(
                'Urusan ' . html_entity_decode($group['nama'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ['bold' => true, 'name' => 'Arial', 'size' => 11]
            );

            // Baris per item catatan
            foreach ($group['items'] as $index => $catatan) {
                $row = $table->addRow(null, ['cantSplit' => true]);

                $row->addCell(self::NO_WIDTH, ['valign' => 'top'])
                    ->addText(($index + 1) . '.', ['name' => 'Arial', 'size' => 11], ['alignment' => 'center']);

                $masukanCell = $row->addCell(self::URUSAN_WIDTH, ['valign' => 'top']);
                Html::addHtml($masukanCell, $this->sanitizeHtml($catatan), false, false);

                $row->addCell(self::KET_WIDTH, ['valign' => 'top'])->addText('');
            }
        }
    }

    /**
     * Tambah cell header tabel dengan style standar
     */
    private function addHeaderCell($row, int $width, string $text): void
    {
        $row->addCell($width, ['bgColor' => 'f0f0f0', 'valign' => 'center'])
            ->addText(
                $text,
                ['bold' => true, 'name' => 'Arial', 'size' => 11],
                ['alignment' => 'center']
            );
    }

    /**
     * Konversi HTML dari TinyMCE ke XHTML valid yang diterima DOMDocument::loadXML()
     * yang digunakan secara internal oleh Html::addHtml() PhpWord.
     *
     * TinyMCE menghasilkan HTML5 (<br>, <li>text<br>more</li>) yang tidak valid
     * sebagai XML. Metode ini menggunakan DOMDocument::loadHTML() yang lenient
     * untuk parse HTML, lalu saveXML() untuk menghasilkan XHTML valid (<br/>).
     */
    private function sanitizeHtml(string $html): string
    {
        if (empty(trim(strip_tags($html)))) {
            return '<p></p>';
        }

        // Hapus nested table (tidak didukung di dalam cell PhpWord)
        $html = preg_replace('/<table[^>]*>.*?<\/table>/is', '', $html);

        // Normalisasi strike/del → <s> (yang dikenali PhpWord)
        $html = str_replace(
            ['<strike>', '</strike>', '<del>', '</del>'],
            ['<s>', '</s>', '<s>', '</s>'],
            $html
        );

        // Konversi HTML5 → XHTML menggunakan DOMDocument
        // loadHTML() lenient (menerima <br>, tag tidak tertutup, dll)
        // saveXML() menghasilkan format XML valid (<br/>, semua tag tertutup)
        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>',
            LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '<p>' . htmlspecialchars(strip_tags($html), ENT_XML1, 'UTF-8') . '</p>';
        }

        $xhtml = '';
        foreach ($body->childNodes as $node) {
            $xhtml .= $dom->saveXML($node);
        }

        return $xhtml ?: '<p></p>';
    }

    /**
     * Group sistematika by bab dan sub_bab
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
     * Group urusan by master_urusan_id
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
     * Format judul dengan angka Romawi kapital (contoh: "bab ii evaluasi" → "Bab II Evaluasi")
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
     * Simpan konten string ke storage dan kembalikan filepath relatif.
     * Dipertahankan untuk backward compatibility.
     */
    public function saveDocument(string $content, string $filename): string
    {
        $filepath = 'hasil-fasilitasi/' . $filename;
        Storage::disk('public')->put($filepath, $content);
        return $filepath;
    }

    /**
     * Kembalikan path absolut dari filepath relatif storage/public
     */
    public function getStoragePath(string $filepath): string
    {
        return storage_path('app/public/' . $filepath);
    }
}
