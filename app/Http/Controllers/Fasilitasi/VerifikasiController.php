<?php

namespace App\Http\Controllers\Fasilitasi;

use App\Http\Controllers\Controller;
use App\Models\MasterTahapan;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanDokumen;
use App\Models\PermohonanTahapan;
use App\Services\PermohonanNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Verifikasi kelengkapan dokumen permohonan oleh verifikator (tahapan 2) dan lembar verifikasi (Word).
 *
 * Akses: permohonan.verify.
 */
class VerifikasiController extends Controller
{
    /**
     * Simpan hasil verifikasi semua dokumen (JSON). Status permohonan menjadi "revisi" bila ada dokumen yang perlu revisi; tahapan, activity log, dan notifikasi diperbarui.
     */
    public function submit(Request $request, Permohonan $permohonan)
    {
        try {
            DB::beginTransaction();

            $verifications = $request->input('verifications', []);

            if (empty($verifications)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data verifikasi tidak ditemukan',
                ], 400);
            }

            // Update dokumen dan hitung statistik
            $totalVerified = 0;
            $totalRevision = 0;

            foreach ($verifications as $verification) {
                $dokumen = PermohonanDokumen::findOrFail($verification['id']);
                $dokumen->update([
                    'status_verifikasi' => $verification['status'],
                    'catatan_verifikasi' => $verification['catatan'] ?? null,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ]);

                $verification['status'] === 'verified' ? $totalVerified++ : $totalRevision++;
            }

            // Load relasi
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // Update status permohonan
            $hasRevision = $totalRevision > 0;

            $newStatus = $hasRevision ? 'revisi' : 'proses';

            $permohonan->update(['status_akhir' => $newStatus]);

            // Update tahapan Verifikasi
            $this->updateTahapanVerifikasi($permohonan, $hasRevision, $totalVerified, $totalRevision);

            // Activity log
            $this->logVerifikasi($permohonan, $verifications, $totalVerified, $totalRevision, $newStatus);

            // Kirim notifikasi
            $this->sendNotifications($permohonan, $hasRevision, $totalVerified, $totalRevision);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $hasRevision
                    ? 'Verifikasi berhasil disimpan. Terdapat dokumen yang perlu revisi.'
                    : 'Verifikasi berhasil disimpan. Semua dokumen sesuai. Menunggu laporan dari admin.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error saat submit verifikasi', [
                'permohonan_id' => $permohonan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update tahapan verifikasi hanya jika ada revisi
     */
    private function updateTahapanVerifikasi($permohonan, $hasRevision, $totalVerified, $totalRevision)
    {
        $masterTahapan = MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();

        if (! $masterTahapan) {
            return;
        }

        if ($hasRevision) {
            PermohonanTahapan::updateOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapan->id,
                ],
                [
                    'status' => 'revisi',
                    'catatan' => "Verifikasi - {$totalRevision} dokumen perlu revisi, {$totalVerified} dokumen sesuai. Menunggu pemohon upload ulang dokumen yang valid.",
                    'updated_by' => auth()->id(),
                ]
            );
        } else {
            PermohonanTahapan::updateOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapan->id,
                ],
                [
                    'status' => 'proses',
                    'catatan' => "Semua {$totalVerified} dokumen terverifikasi pada ".now()->format('d M Y H:i').'. Menunggu admin membuat laporan verifikasi.',
                    'updated_by' => auth()->id(),
                ]
            );
        }
    }

    /**
     * Log aktivitas verifikasi
     */
    private function logVerifikasi($permohonan, $verifications, $totalVerified, $totalRevision, $status)
    {
        activity()
            ->performedOn($permohonan)
            ->causedBy(auth()->user())
            ->withProperties([
                'total_dokumen' => count($verifications),
                'total_verified' => $totalVerified,
                'total_revision' => $totalRevision,
                'status' => $status,
                'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? null,
                'jenis_dokumen' => $permohonan->jenisDokumen->nama_dokumen ?? null,
                'tahun' => $permohonan->tahun,
            ])
            ->log('Verifikasi dokumen permohonan');

        Log::info('Verifikasi dokumen berhasil', [
            'permohonan_id' => $permohonan->id,
            'verifikator' => auth()->user()->name,
            'status' => $status,
            'total_verified' => $totalVerified,
            'total_revision' => $totalRevision,
        ]);
    }

    /**
     * Unduh lembar verifikasi dalam format Word.
     */
    public function generateLembarVerifikasi(Permohonan $permohonan)
    {
        $permohonan->load([
            'kabupatenKota',
            'jenisDokumen',
            'permohonanDokumen.masterKelengkapan',
            'permohonanDokumen.verifiedBy',
        ]);

        $namaKabKota = $permohonan->kabupatenKota->nama ?? 'Kabupaten/Kota';
        $jenisDokumen = $permohonan->jenisDokumen->nama ?? 'Dokumen';
        $tahun = $permohonan->tahun;

        $dokumenList = $permohonan->permohonanDokumen
            ->sortBy(fn ($d) => $d->masterKelengkapan->urutan ?? 999);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set default font Arial
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(8);

        // ── Judul baris 1 ──────────────────────────────────────────────
        $judul1 = 'DOKUMEN PERSYARATAN FASILITASI '.strtoupper($jenisDokumen);
        $sheet->setCellValue('A1', $judul1);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ── Judul baris 2 ──────────────────────────────────────────────
        $judul2 = strtoupper($namaKabKota).' TAHUN '.$tahun;
        $sheet->setCellValue('A2', $judul2);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 11, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ── Header kolom baris 3 ───────────────────────────────────────
        $headers = ['NO', 'URAIAN', 'ADA', 'TIDAK ADA', 'TANGGAL SURAT', 'TANGGAL PENYAMPAIAN', 'TANGGAL VERIFIKASI', 'KET'];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}3", $header);
        }
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 8, 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ffffff']],
        ]);

        // ── Sub-header angka baris 4 ───────────────────────────────────
        for ($i = 1; $i <= 8; $i++) {
            $col = chr(64 + $i);
            $sheet->setCellValue("{$col}4", $i);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BFBFBF']],
        ]);

        // ── Data dokumen ───────────────────────────────────────────────
        $row = 5;
        $no = 1;
        $formNo = 1;

        $formatTanggal = function ($date) {
            if (! $date) {
                return '';
            }
            $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $d = Carbon::parse($date);

            return $d->day.' '.$bulan[$d->month].' '.$d->year;
        };

        foreach ($dokumenList as $dokumen) {
            $namaDokumen = $dokumen->masterKelengkapan->nama_dokumen ?? '-';
            $isForm = preg_match('/^form\s/i', $namaDokumen);

            $noValue = $isForm ? 'FORM '.$formNo++ : $no++;

            // Tanggal upload oleh pemohon (created_at dari permohonan_dokumen)
            $tglPenyampaian = $formatTanggal($dokumen->created_at);
            // Tanggal verifikasi
            $tglVerifikasi = $formatTanggal($dokumen->verified_at);

            $sheet->setCellValue("A{$row}", $noValue);
            $sheet->setCellValue("B{$row}", $namaDokumen);
            $sheet->setCellValue("C{$row}", "\u{2713}"); // ✓
            $sheet->setCellValue("D{$row}", '');
            $sheet->setCellValue("E{$row}", '');
            $sheet->setCellValue("F{$row}", $tglPenyampaian);
            $sheet->setCellValue("G{$row}", $tglVerifikasi);
            $sheet->setCellValue("H{$row}", '');

            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 8],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // ── Border seluruh tabel ───────────────────────────────────────
        $lastRow = $row - 1;
        if ($lastRow >= 3) {
            $sheet->getStyle("A3:H{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // ── TTD ────────────────────────────────────────────────────────
        $ttdRow = $lastRow + 2;
        $tglGenerate = now()->day.' '.['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][now()->month].' '.now()->year;

        $sheet->setCellValue("H{$ttdRow}", "Sofifi, {$tglGenerate}");
        $sheet->setCellValue('H'.($ttdRow + 1), "Verifikator - PIC {$namaKabKota}");

        $sheet->getStyle("H{$ttdRow}:H".($ttdRow + 1))->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Lebar kolom ────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(20);

        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(30);

        // ── Output ─────────────────────────────────────────────────────
        $fileName = 'Lembar Verifikasi Dokumen Persyaratan Fasilitasi '.$jenisDokumen.' '.$tahun.' '.$namaKabKota.'.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Kirim notifikasi:
     * - Jika semua verified: notifikasi ke admin (database + WA) untuk buat laporan
     * - Jika ada revisi: notifikasi ke pemohon (database + WA) dengan detail dokumen yang perlu revisi
     */
    private function sendNotifications($permohonan, $hasRevision, $totalVerified, $totalRevision)
    {
        $notificationService = app(PermohonanNotificationService::class);

        // Tentukan status verifikasi
        $status = $hasRevision ? 'revisi' : 'lengkap';

        // Kirim notifikasi via service (database + WhatsApp)
        $notificationService->notifyVerifikasiSelesai($permohonan, $status);

        if ($hasRevision) {
            // ADA DOKUMEN YANG PERLU REVISI - Kirim notifikasi tambahan ke pemohon dengan detail (database saja, WA sudah via service)

            // Ambil daftar dokumen yang perlu revisi
            $dokumenRevisi = $permohonan->permohonanDokumen()
                ->where('status_verifikasi', 'revision')
                ->with('masterKelengkapan')
                ->get();

            $daftarDokumen = $dokumenRevisi->map(function ($dok) {
                return '• '.($dok->masterKelengkapan->nama_kelengkapan ?? 'Dokumen');
            })->implode("\n");

            // Notifikasi database tambahan ke pemohon dengan detail dokumen
            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => 'Segera Upload Ulang Dokumen yang Perlu Revisi',
                'message' => sprintf(
                    "Verifikasi untuk permohonan Anda (%s - %s tahun %s) telah selesai.\n\n".
                    "Terdapat %s dokumen yang perlu diperbaiki dan diupload kembali:\n\n%s\n\n".
                    'Silakan login ke sistem untuk melihat catatan verifikator dan upload ulang dokumen yang telah diperbaiki.',
                    $permohonan->kabupatenKota->nama ?? 'N/A',
                    $permohonan->jenisDokumen->nama ?? 'N/A',
                    $permohonan->tahun,
                    $totalRevision,
                    $daftarDokumen
                ),
                'type' => 'warning',
                'action_url' => route('permohonan.tahapan.verifikasi', $permohonan),
                'model_type' => Permohonan::class,
                'model_id' => $permohonan->id,
            ]);
        }
    }
}
