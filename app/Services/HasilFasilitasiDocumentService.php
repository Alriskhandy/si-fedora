<?php

namespace App\Services;

use App\Models\Permohonan;
use Illuminate\Support\Facades\Storage;

class HasilFasilitasiDocumentService
{
    /**
     * Generate Word document content
     */
    public function generateWordDocument(Permohonan $permohonan, $sistematika, $urusan): string
    {
        $kabkota = $permohonan->kabupatenKota->nama;
        $tahun = date('Y');

        $html = $this->getDocumentHeader($kabkota, $tahun);
        $html .= $this->generateSistematikaSection($sistematika, $kabkota);
        $html .= $this->generateUrusanSection($urusan);
        $html .= $this->getDocumentFooter();

        return $html;
    }

    /**
     * Get document header with styles
     */
    private function getDocumentHeader(string $kabkota, string $tahun): string
    {
        return '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body { 
                            font-family: Arial, sans-serif; 
                            font-size: 12pt; 
                            line-height: 1.5; 
                            margin: 20px;
                        }
                        h2 { 
                            font-size: 12pt; 
                            font-weight: normal; 
                            margin-top: 20px; 
                            margin-bottom: 10px; 
                        }
                        table { 
                            width: 100%; 
                            border-collapse: collapse; 
                            margin-bottom: 20px; 
                        }
                        table, th, td { 
                            border: 1px solid black; 
                        }
                        th, td { 
                            padding: 8px; 
                            vertical-align: top; 
                            font-size: 12pt;
                        }
                        th { 
                            font-weight: bold; 
                            text-align: center; 
                        }
                        .no-col { 
                            width: 5%; 
                            text-align: center; 
                        }
                        .title-col { 
                            width: 25%; 
                        }
                        .content-col { 
                            width: 70%; 
                        }
                    </style>
                </head>
                <body>';
    }

    /**
     * Generate Sistematika section
     */
    private function generateSistematikaSection($sistematika, string $kabkota): string
    {
        $html = '
            <p>Catatan penyempurnaan terhadap sistematika dan substansi sebagai berikut:</p>
            
            <table>
                <thead>
                    <tr>
                        <th class="no-col">No.</th>
                        <th class="title-col">Bab/Sub Bab</th>
                        <th class="content-col">Catatan Penyempurnaan</th>
                    </tr>
                </thead>
                <tbody>';

                if ($sistematika->count() > 0) {
                    $counter = 1;
                    $currentBabId = null;
                    $groupedItems = $this->groupSistematika($sistematika);

                    foreach ($groupedItems as $groupedItem) {
                        // Display bab header if changed
                        if ($currentBabId !== $groupedItem['bab_id']) {
                            $html .= '<tr>
                                <td colspan="3">' . ucwords(strtolower(htmlspecialchars($groupedItem['bab_nama']))) . '</td>
                            </tr>';
                            $currentBabId = $groupedItem['bab_id'];
                        }

                        // Merge catatan with numbering
                        $catatanGabungan = $this->mergeCatatan($groupedItem['catatan']);

                        $html .= '<tr>
                            <td class="no-col">' . $counter . '</td>
                            <td>' . ucwords(strtolower(htmlspecialchars($groupedItem['sub_bab']))) . '</td>
                            <td>' . $catatanGabungan . '</td>
                        </tr>';

                        $counter++;
                    }
                } else {
                    $html .= '<tr><td colspan="3" style="text-align: center; font-style: italic;">Tidak ada catatan penyempurnaan</td></tr>';
                }

                $html .= '</tbody>
            </table>';

        return $html;
    }

    /**
     * Generate Urusan section
     */
    private function generateUrusanSection($urusan): string
    {
        $html = '
            <p>Masukan terkait penyelenggaraan urusan Pemerintah Daerah sebagai berikut:</p>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="width: 70%;">Masukan/Saran</th>
                        <th style="width: 25%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>';

                if ($urusan->count() > 0) {
                    $groupedUrusan = $this->groupUrusan($urusan);

                    foreach ($groupedUrusan as $urusan) {
                        // Header urusan
                        $html .= '<tr>
                            <td colspan="3">Urusan ' . htmlspecialchars($urusan['nama']) . '</td>
                        </tr>';

                        // Items with numbering
                        foreach ($urusan['items'] as $index => $catatan) {
                            $html .= '<tr>
                                <td style="text-align: center;">' . ($index + 1) . '.</td>
                                <td>' . $this->cleanHtmlContent($catatan) . '</td>
                                <td></td>
                            </tr>';
                        }
                    }
                } else {
                    $html .= '<tr><td colspan="3" style="text-align: center; font-style: italic;">Tidak ada catatan masukan</td></tr>';
                }

                $html .= '</tbody>
            </table>';

        return $html;
    }

    /**
     * Get document footer
     */
    private function getDocumentFooter(): string
    {
        return '
            </body>
            </html>';
    }

    /**
     * Group sistematika by bab and sub_bab
     */
    private function groupSistematika($sistematika): array
    {
        $groupedItems = [];

        foreach ($sistematika as $item) {
            $babId = $item->master_bab_id;
            $subBab = $item->sub_bab ?: ($item->masterBab->nama_bab ?? '-');
            $key = $babId . '|' . $subBab;

            if (!isset($groupedItems[$key])) {
                $groupedItems[$key] = [
                    'bab_id' => $babId,
                    'bab_nama' => $item->masterBab->nama_bab ?? '-',
                    'sub_bab' => $subBab,
                    'catatan' => []
                ];
            }

            $groupedItems[$key]['catatan'][] = $item->catatan_penyempurnaan;
        }

        return $groupedItems;
    }

    /**
     * Group urusan by master_urusan_id
     */
    private function groupUrusan($urusan): array
    {
        $groupedUrusan = [];

        foreach ($urusan as $item) {
            $urusanId = $item->master_urusan_id;

            if (!isset($groupedUrusan[$urusanId])) {
                $groupedUrusan[$urusanId] = [
                    'nama' => $item->masterUrusan->nama_urusan ?? $item->masterUrusan->nama,
                    'items' => []
                ];
            }

            $groupedUrusan[$urusanId]['items'][] = $item->catatan_masukan;
        }

        return $groupedUrusan;
    }

    /**
     * Merge catatan with numbering
     */
    private function mergeCatatan(array $catatanArray): string
    {
        $merged = '';

        foreach ($catatanArray as $index => $catatan) {
            $cleanContent = $this->cleanHtmlContent($catatan);
            $merged .= ($index + 1) . '. ' . $cleanContent;

            if ($index < count($catatanArray) - 1) {
                $merged .= '<br><br>';
            }
        }

        return $merged;
    }

    /**
     * Clean HTML content from TinyMCE/RichText
     * Renders RichText objects to HTML or returns sanitized HTML string
     */
    private function cleanHtmlContent($content): string
    {
        // If it's a RichText object, render it first
        if (is_object($content) && method_exists($content, 'render')) {
            $content = $content->render();
        }

        // Convert to string if not already
        $content = (string) $content;

        // Replace common HTML tags with plain text equivalents
        $content = str_replace(['<br>', '<br/>', '<br />'], "\n", $content);
        $content = preg_replace('/<\/p>\s*<p>/', "\n\n", $content);
        $content = preg_replace('/<li>/', '• ', $content);
        $content = preg_replace('/<\/li>/', "\n", $content);

        // Strip all remaining HTML tags
        $content = strip_tags($content);

        // Decode HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Clean up extra whitespace
        $content = trim($content);

        return $content;
    }

    /**
     * Save document to storage and return filepath
     */
    public function saveDocument(string $content, string $filename): string
    {
        $filepath = 'hasil-fasilitasi/' . $filename;
        Storage::disk('public')->put($filepath, $content);

        return $filepath;
    }

    /**
     * Get full storage path
     */
    public function getStoragePath(string $filepath): string
    {
        return storage_path('app/public/' . $filepath);
    }
}
