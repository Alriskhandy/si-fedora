<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\UserKabkotaAssignment;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola notifikasi permohonan
 * Menangani notifikasi database dan WhatsApp untuk setiap tahapan
 */
class PermohonanNotificationService
{
    protected $fonteService;

    public function __construct(FonteService $fonteService)
    {
        $this->fonteService = $fonteService;
    }

    /**
     * Kirim notifikasi saat permohonan disubmit
     * Target: Admin dan Tim Fedora (Verifikator & Fasilitator)
     */
    public function notifyPermohonanSubmitted(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // 1. Kirim ke Admin (database + WA)
            $this->notifyAdmins($permohonan, 'submitted');

            // 2. Kirim ke Tim Fedora yang di-assign (database + WA)
            $this->notifyTimFedora($permohonan, 'submitted');

            Log::info('Permohonan submitted notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
                'kabupaten_kota' => $permohonan->kabupatenKota->nama ?? '-',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending permohonan submitted notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat verifikasi selesai
     * - Jika ada revisi: kirim WA ke pemohon saja
     * - Jika semua lengkap: kirim WA ke admin_peran saja
     */
    public function notifyVerifikasiSelesai(Permohonan $permohonan, $status = 'lengkap')
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            if ($status === 'revisi') {
                // Jika ada revisi: kirim ke pemohon saja (database + WA)
                $this->notifyPemohon($permohonan, 'verifikasi_selesai', $status);
            } else {
                // Jika semua lengkap: kirim ke admin_peran saja (database + WA)
                $admins = User::role('admin_peran')->get();
                
                if (!$admins->isEmpty()) {
                    $notifData = $this->getNotificationData($permohonan, 'verifikasi_selesai', $status);

                    // Kirim notifikasi database
                    foreach ($admins as $admin) {
                        Notifikasi::create([
                            'user_id' => $admin->id,
                            'title' => $notifData['title'],
                            'message' => $notifData['message'],
                            'type' => $notifData['type'],
                            'model_type' => Permohonan::class,
                            'model_id' => $permohonan->id,
                            'action_url' => $notifData['action_url'],
                            'is_read' => false,
                        ]);
                    }

                    // Kirim WhatsApp bulk
                    $this->sendBulkWhatsApp($admins, $permohonan, 'verifikasi_selesai', $status);
                }
            }

            Log::info('Verifikasi notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending verifikasi notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat jadwal ditetapkan oleh kaban
     * Target: Admin (untuk buat undangan), Tim Fedora, Pemohon
     */
    /**
     * Kirim notifikasi saat kaban menetapkan jadwal fasilitasi
     * Target: Admin Peran saja (untuk membuat undangan)
     * Nantinya admin peran akan mengirim undangan ke tim fedora dan pemohon dengan notifikasi tersendiri
     */
    public function notifyJadwalDitetapkan(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon', 'penetapanJadwal']);

            // Kirim ke Admin Peran untuk membuat undangan (database + WA)
            $admins = User::role('admin_peran')->get();
            
            if (!$admins->isEmpty()) {
                $notifData = $this->getNotificationData($permohonan, 'jadwal_ditetapkan', 'admin');

                // Kirim notifikasi database
                foreach ($admins as $admin) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'type' => $notifData['type'],
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => $notifData['action_url'],
                        'is_read' => false,
                    ]);
                }

                // Kirim WhatsApp bulk
                $this->sendBulkWhatsApp($admins, $permohonan, 'jadwal_ditetapkan', 'admin');
            }

            Log::info('Jadwal ditetapkan notifications sent to admin_peran successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending jadwal notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat jadwal diubah oleh kaban
     * Target: Admin Peran saja (untuk update undangan jika diperlukan)
     * Nantinya admin peran akan mengirim undangan perubahan ke tim fedora dan pemohon jika diperlukan
     */
    public function notifyJadwalDiubah(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon', 'penetapanJadwal']);

            // Kirim ke Admin Peran untuk update undangan (database + WA)
            $admins = User::role('admin_peran')->get();
            
            if (!$admins->isEmpty()) {
                $notifData = $this->getNotificationData($permohonan, 'jadwal_diubah');

                // Kirim notifikasi database
                foreach ($admins as $admin) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'type' => $notifData['type'],
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => $notifData['action_url'],
                        'is_read' => false,
                    ]);
                }

                // Kirim WhatsApp bulk
                $this->sendBulkWhatsApp($admins, $permohonan, 'jadwal_diubah');
            }

            Log::info('Jadwal diubah notifications sent to admin_peran successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending jadwal diubah notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat dokumen revisi berhasil diupload ulang
     * Target: Verifikator yang bertanggung jawab
     */
    public function notifyDokumenRevisiUploaded(Permohonan $permohonan, $namaDokumen)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // Kirim ke verifikator yang bertanggung jawab
            $this->notifyTimFedoraVerifikator($permohonan, 'dokumen_revisi_uploaded', $namaDokumen);

            Log::info('Dokumen revisi uploaded notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
                'dokumen' => $namaDokumen,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending dokumen revisi uploaded notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat laporan verifikasi dibuat oleh admin
     * Target: Kaban (untuk penetapan jadwal), Tim Fedora, Pemohon
     */
    public function notifyLaporanVerifikasiDibuat(Permohonan $permohonan, $statusKelengkapan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // 1. Kirim ke Kaban untuk penetapan jadwal (database + WA)
            $this->notifyKaban($permohonan, 'laporan_verifikasi_dibuat', $statusKelengkapan);

            // 2. Kirim ke Tim Fedora (database + WA)
            $this->notifyTimFedora($permohonan, 'laporan_verifikasi_dibuat', $statusKelengkapan);

            // 3. Kirim ke Pemohon (database + WA)
            $this->notifyPemohon($permohonan, 'laporan_verifikasi_dibuat', $statusKelengkapan);

            Log::info('Laporan verifikasi notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
                'status_kelengkapan' => $statusKelengkapan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending laporan verifikasi notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat tahapan pelaksanaan selesai
     * Target: Fasilitator untuk input hasil fasilitasi
     */
    public function notifyPelaksanaanSelesai(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // Kirim ke Fasilitator yang di-assign untuk input hasil
            $this->notifyTimFedoraFasilitator($permohonan, 'pelaksanaan_selesai');

            Log::info('Pelaksanaan selesai notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending pelaksanaan selesai notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat draft hasil fasilitasi diajukan ke Kaban
     * Target: Kaban untuk persetujuan
     */
    public function notifyDraftSubmittedToKaban(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // Kirim ke Kaban untuk persetujuan (database + WA)
            $this->notifyKaban($permohonan, 'draft_submitted_to_kaban');

            Log::info('Draft submitted to kaban notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending draft submitted to kaban notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat draft hasil fasilitasi disetujui oleh Kaban
     * Target: Admin Peran, Tim Fedora, dan Pemohon
     */
    public function notifyDraftApproved(Permohonan $permohonan, $keterangan = null)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // 1. Kirim ke Admin Peran (database + WA)
            $admins = User::role('admin_peran')->get();
            if (!$admins->isEmpty()) {
                $notifData = $this->getNotificationData($permohonan, 'draft_approved', $keterangan);

                foreach ($admins as $admin) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'type' => $notifData['type'],
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => $notifData['action_url'],
                        'is_read' => false,
                    ]);
                }
                $this->sendBulkWhatsApp($admins, $permohonan, 'draft_approved', $keterangan);
            }

            // 2. Kirim ke Tim Fedora (database + WA)
            $this->notifyTimFedora($permohonan, 'draft_approved', $keterangan);

            // 3. Kirim ke Pemohon (database + WA)
            $this->notifyPemohon($permohonan, 'draft_approved', $keterangan);

            Log::info('Draft approved notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending draft approved notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat draft hasil fasilitasi ditolak/revisi oleh Kaban
     * Target: Admin Peran
     */
    public function notifyDraftRejected(Permohonan $permohonan, $catatanPenolakan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // Kirim ke Admin Peran (database + WA)
            $admins = User::role('admin_peran')->get();
            if (!$admins->isEmpty()) {
                $notifData = $this->getNotificationData($permohonan, 'draft_rejected', $catatanPenolakan);

                foreach ($admins as $admin) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'type' => $notifData['type'],
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => $notifData['action_url'],
                        'is_read' => false,
                    ]);
                }
                $this->sendBulkWhatsApp($admins, $permohonan, 'draft_rejected', $catatanPenolakan);
            }

            Log::info('Draft rejected notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending draft rejected notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat dokumen tindak lanjut disubmit oleh pemohon
     * Target: Admin Peran dan Tim Fedora
     */
    public function notifyTindakLanjutSubmitted(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // 1. Kirim ke Admin Peran (database + WA)
            $admins = User::role('admin_peran')->get();
            if (!$admins->isEmpty()) {
                $notifData = $this->getNotificationData($permohonan, 'tindak_lanjut_submitted');

                foreach ($admins as $admin) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'type' => $notifData['type'],
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => $notifData['action_url'],
                        'is_read' => false,
                    ]);
                }
                $this->sendBulkWhatsApp($admins, $permohonan, 'tindak_lanjut_submitted');
            }

            // 2. Kirim ke Tim Fedora (database + WA)
            $this->notifyTimFedora($permohonan, 'tindak_lanjut_submitted');

            Log::info('Tindak lanjut submitted notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending tindak lanjut submitted notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat pemohon submit dokumen penetapan perda
     * Target: Admin Peran dan Tim Fedora
     */
    public function notifyPenetapanPerdaSubmitted(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon']);

            // 1. Kirim ke Admin Peran (database + WA)
            $admins = User::role('admin_peran')->get();
            if (!$admins->isEmpty()) {
                $notifData = $this->getNotificationData($permohonan, 'penetapan_perda_submitted');

                foreach ($admins as $admin) {
                    Notifikasi::create([
                        'user_id' => $admin->id,
                        'title' => $notifData['title'],
                        'message' => $notifData['message'],
                        'type' => $notifData['type'],
                        'model_type' => Permohonan::class,
                        'model_id' => $permohonan->id,
                        'action_url' => $notifData['action_url'],
                        'is_read' => false,
                    ]);
                }
                $this->sendBulkWhatsApp($admins, $permohonan, 'penetapan_perda_submitted');
            }

            // 2. Kirim ke Tim Fedora (database + WA)
            $this->notifyTimFedora($permohonan, 'penetapan_perda_submitted');

            Log::info('Penetapan perda submitted notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending penetapan perda submitted notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi saat admin mengirim undangan pelaksanaan
     * Target: Tim Fedora dan Pemohon yang menerima undangan
     */
    public function notifyUndanganDikirim(Permohonan $permohonan, array $penerimaUserIds)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon', 'penetapanJadwal']);

            $recipients = [];
            foreach ($penerimaUserIds as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                // Kirim notifikasi database (sudah di-handle di controller)
                // Hanya kirim WhatsApp di sini
                if ($user->no_hp) {
                    $message = $this->getWhatsAppMessage($permohonan, 'undangan_dikirim', $user);
                    $recipients[] = [
                        'target' => $user->no_hp,
                        'message' => $message,
                        'delay' => '2-4'
                    ];
                }
            }

            // Kirim WhatsApp bulk
            if (!empty($recipients)) {
                $result = $this->fonteService->sendBulkMessage($recipients);

                if ($result['success']) {
                    Log::info('Undangan pelaksanaan WhatsApp sent', [
                        'permohonan_id' => $permohonan->id,
                        'total_sent' => count($recipients)
                    ]);
                }
            }

            Log::info('Undangan pelaksanaan notifications sent successfully', [
                'permohonan_id' => $permohonan->id,
                'total_penerima' => count($penerimaUserIds),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending undangan pelaksanaan notifications: ' . $e->getMessage(), [
                'permohonan_id' => $permohonan->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Kirim notifikasi ke Admin
     */
    private function notifyAdmins(Permohonan $permohonan, $event, $additionalData = null)
    {
        $admins = User::role(['admin_peran', 'kaban', 'superadmin'])
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        $notifData = $this->getNotificationData($permohonan, $event, $additionalData);

        // Kirim notifikasi database
        foreach ($admins as $admin) {
            Notifikasi::create([
                'user_id' => $admin->id,
                'title' => $notifData['title'],
                'message' => $notifData['message'],
                'type' => $notifData['type'],
                'model_type' => Permohonan::class,
                'model_id' => $permohonan->id,
                'action_url' => $notifData['action_url'],
                'is_read' => false,
            ]);
        }

        // Kirim WhatsApp bulk
        $this->sendBulkWhatsApp($admins, $permohonan, $event, $additionalData);
    }

    /**
     * Kirim notifikasi ke Tim Fedora (Verifikator + Fasilitator yang di-assign)
     */
    private function notifyTimFedora(Permohonan $permohonan, $event, $additionalData = null)
    {
        // Get tim fedora yang di-assign untuk kab/kota dan tahun ini
        $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->where(function ($q) use ($permohonan) {
                $q->whereNull('jenis_dokumen_id')
                    ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
            })
            ->with('user')
            ->get();

        if ($assignments->isEmpty()) {
            Log::info('No tim fedora assigned for this permohonan', [
                'permohonan_id' => $permohonan->id,
                'kab_kota_id' => $permohonan->kab_kota_id,
                'tahun' => $permohonan->tahun,
            ]);
            return;
        }

        $notifData = $this->getNotificationData($permohonan, $event, $additionalData);

        // Kirim notifikasi database
        foreach ($assignments as $assignment) {
            if ($assignment->user) {
                Notifikasi::create([
                    'user_id' => $assignment->user_id,
                    'title' => $notifData['title'],
                    'message' => $notifData['message'],
                    'type' => $notifData['type'],
                    'model_type' => Permohonan::class,
                    'model_id' => $permohonan->id,
                    'action_url' => $notifData['action_url'],
                    'is_read' => false,
                ]);
            }
        }

        // Kirim WhatsApp bulk
        $users = $assignments->pluck('user')->filter();
        $this->sendBulkWhatsApp($users, $permohonan, $event, $additionalData);
    }

    /**
     * Kirim notifikasi ke Fasilitator saja (untuk pelaksanaan selesai)
     */
    private function notifyTimFedoraFasilitator(Permohonan $permohonan, $event, $additionalData = null)
    {
        // Get fasilitator yang di-assign untuk kab/kota dan tahun ini
        $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->where(function ($q) use ($permohonan) {
                $q->whereNull('jenis_dokumen_id')
                    ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
            })
            ->with('user')
            ->get();

        // Filter hanya yang punya user dan user adalah fasilitator/koordinator
        $fasilitators = $assignments->filter(function($assignment) {
            return $assignment->user && $assignment->user->hasAnyRole(['fasilitator', 'koordinator']);
        });

        if ($fasilitators->isEmpty()) {
            Log::info('No fasilitator assigned for this permohonan', [
                'permohonan_id' => $permohonan->id,
                'kab_kota_id' => $permohonan->kab_kota_id,
                'tahun' => $permohonan->tahun,
            ]);
            return;
        }

        $notifData = $this->getNotificationData($permohonan, $event, $additionalData);

        // Kirim notifikasi database
        foreach ($fasilitators as $assignment) {
            if ($assignment->user) {
                Notifikasi::create([
                    'user_id' => $assignment->user_id,
                    'title' => $notifData['title'],
                    'message' => $notifData['message'],
                    'type' => $notifData['type'],
                    'model_type' => Permohonan::class,
                    'model_id' => $permohonan->id,
                    'action_url' => $notifData['action_url'],
                    'is_read' => false,
                ]);
            }
        }

        // Kirim WhatsApp bulk
        $users = $fasilitators->pluck('user')->filter();
        $this->sendBulkWhatsApp($users, $permohonan, $event, $additionalData);
    }

    /**
     * Kirim notifikasi ke Verifikator saja (untuk dokumen revisi uploaded)
     */
    private function notifyTimFedoraVerifikator(Permohonan $permohonan, $event, $additionalData = null)
    {
        // Get verifikator yang di-assign untuk kab/kota dan tahun ini
        $assignments = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
            ->where('tahun', $permohonan->tahun)
            ->where('is_active', true)
            ->where(function ($q) use ($permohonan) {
                $q->whereNull('jenis_dokumen_id')
                    ->orWhere('jenis_dokumen_id', $permohonan->jenis_dokumen_id);
            })
            ->with('user')
            ->get();

        // Filter hanya yang punya user dan user adalah verifikator
        $verifikators = $assignments->filter(function($assignment) {
            return $assignment->user && $assignment->user->hasRole('verifikator');
        });

        if ($verifikators->isEmpty()) {
            Log::info('No verifikator assigned for this permohonan', [
                'permohonan_id' => $permohonan->id,
                'kab_kota_id' => $permohonan->kab_kota_id,
                'tahun' => $permohonan->tahun,
            ]);
            return;
        }

        $notifData = $this->getNotificationData($permohonan, $event, $additionalData);

        // Kirim notifikasi database
        foreach ($verifikators as $assignment) {
            if ($assignment->user) {
                Notifikasi::create([
                    'user_id' => $assignment->user_id,
                    'title' => $notifData['title'],
                    'message' => $notifData['message'],
                    'type' => $notifData['type'],
                    'model_type' => Permohonan::class,
                    'model_id' => $permohonan->id,
                    'action_url' => $notifData['action_url'],
                    'is_read' => false,
                ]);
            }
        }

        // Kirim WhatsApp bulk
        $users = $verifikators->pluck('user')->filter();
        $this->sendBulkWhatsApp($users, $permohonan, $event, $additionalData);
    }

    /**
     * Kirim notifikasi ke Kaban
     */
    private function notifyKaban(Permohonan $permohonan, $event, $additionalData = null)
    {
        $kabans = User::role(['kaban'])->get();

        if ($kabans->isEmpty()) {
            return;
        }

        $notifData = $this->getNotificationData($permohonan, $event, $additionalData);

        // Kirim notifikasi database
        foreach ($kabans as $kaban) {
            Notifikasi::create([
                'user_id' => $kaban->id,
                'title' => $notifData['title'],
                'message' => $notifData['message'],
                'type' => $notifData['type'],
                'model_type' => Permohonan::class,
                'model_id' => $permohonan->id,
                'action_url' => $notifData['action_url'],
                'is_read' => false,
            ]);
        }

        // Kirim WhatsApp bulk
        $this->sendBulkWhatsApp($kabans, $permohonan, $event, $additionalData);
    }

    /**
     * Kirim notifikasi ke Pemohon
     */
    private function notifyPemohon(Permohonan $permohonan, $event, $additionalData = null)
    {
        // Gunakan relasi pemohon atau query langsung by user_id
        $pemohon = $permohonan->pemohon ?? User::find($permohonan->user_id);

        if (!$pemohon) {
            Log::warning('Pemohon not found for permohonan', [
                'permohonan_id' => $permohonan->id,
                'user_id' => $permohonan->user_id,
            ]);
            return;
        }

        $notifData = $this->getNotificationData($permohonan, $event, $additionalData);

        // Kirim notifikasi database
        Notifikasi::create([
            'user_id' => $pemohon->id,
            'title' => $notifData['title'],
            'message' => $notifData['message'],
            'type' => $notifData['type'],
            'model_type' => Permohonan::class,
            'model_id' => $permohonan->id,
            'action_url' => $notifData['action_url'],
            'is_read' => false,
        ]);

        // Kirim WhatsApp
        if ($pemohon->no_hp) {
            $waMessage = $this->getWhatsAppMessage($permohonan, $event, $pemohon, $additionalData);
            $this->fonteService->sendNotification($pemohon->no_hp, $waMessage);
        }
    }

    /**
     * Kirim WhatsApp bulk ke multiple users
     */
    private function sendBulkWhatsApp($users, Permohonan $permohonan, $event, $additionalData = null)
    {
        $recipients = [];

        foreach ($users as $user) {
            if ($user && $user->no_hp) {
                $message = $this->getWhatsAppMessage($permohonan, $event, $user, $additionalData);
                $recipients[] = [
                    'target' => $user->no_hp,
                    'message' => $message,
                    'delay' => '2-4'
                ];
            }
        }

        if (!empty($recipients)) {
            $result = $this->fonteService->sendBulkMessage($recipients);

            if ($result['success']) {
                Log::info('Bulk WhatsApp notifications sent', [
                    'event' => $event,
                    'permohonan_id' => $permohonan->id,
                    'total_sent' => count($recipients)
                ]);
            }
        }
    }

    /**
     * Get notification data berdasarkan event
     */
    private function getNotificationData(Permohonan $permohonan, $event, $additionalData = null)
    {
        $kabkota = $permohonan->kabupatenKota->nama ?? '-';
        $jenisDokumen = $permohonan->jenisDokumen->nama ?? '-';
        $tahun = $permohonan->tahun;

        $data = [
            'action_url' => route('permohonan.show', $permohonan),
        ];

        switch ($event) {
            case 'submitted':
                $data['title'] = 'Permohonan Baru Diajukan';
                $data['message'] = "Permohonan fasilitasi dari {$kabkota} untuk {$jenisDokumen} tahun {$tahun} telah diajukan dan menunggu verifikasi.";
                $data['type'] = 'info';
                break;

            case 'verifikasi_selesai':
                if ($additionalData === 'lengkap') {
                    // Untuk admin: instruksi buat laporan
                    $data['title'] = 'Buat Laporan Verifikasi';
                    $data['message'] = "Verifikasi untuk {$kabkota} - {$jenisDokumen} tahun {$tahun} telah selesai. Semua dokumen telah diverifikasi dan sesuai. Silakan buat laporan verifikasi untuk melanjutkan ke tahap berikutnya.";
                    $data['type'] = 'info';
                    $data['action_url'] = route('permohonan.tahapan.verifikasi', $permohonan);
                } else {
                    // Untuk pemohon: dokumen perlu revisi
                    $statusText = $additionalData === 'revisi' ? 'perlu revisi' : ($additionalData === 'tidak_lengkap' ? 'tidak lengkap' : 'perlu dilengkapi');
                    $data['title'] = 'Verifikasi Dokumen Selesai';
                    $data['message'] = "Verifikasi dokumen untuk permohonan {$kabkota} telah selesai dengan status: {$statusText}.";
                    $data['type'] = 'warning';
                }
                break;

            case 'jadwal_ditetapkan':
                if ($additionalData === 'admin') {
                    // Untuk admin: instruksi buat undangan
                    $tanggalMulai = $permohonan->penetapanJadwal->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d M Y') : '-';
                    $tanggalSelesai = $permohonan->penetapanJadwal->tanggal_selesai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d M Y') : '-';
                    $lokasi = $permohonan->penetapanJadwal->lokasi ?? '';
                    
                    $data['title'] = 'Jadwal Ditetapkan - Buat Undangan Pelaksanaan';
                    $data['message'] = sprintf(
                        'Jadwal fasilitasi untuk %s - %s tahun %s telah ditetapkan oleh Kaban. Pelaksanaan: %s s/d %s%s. Silakan buat undangan pelaksanaan.',
                        $kabkota,
                        $jenisDokumen,
                        $tahun,
                        $tanggalMulai,
                        $tanggalSelesai,
                        $lokasi ? ' di ' . $lokasi : ''
                    );
                    $data['type'] = 'info';
                    $data['action_url'] = route('permohonan.show', $permohonan);
                } else {
                    // Untuk pemohon dan tim fedora
                    $data['title'] = 'Jadwal Fasilitasi Ditetapkan';
                    $data['message'] = "Jadwal pelaksanaan fasilitasi untuk permohonan {$kabkota} telah ditetapkan.";
                    $data['type'] = 'info';
                }
                break;

            case 'jadwal_diubah':
                $tanggalMulai = $permohonan->penetapanJadwal->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d M Y') : '-';
                $tanggalSelesai = $permohonan->penetapanJadwal->tanggal_selesai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d M Y') : '-';
                $lokasi = $permohonan->penetapanJadwal->lokasi ?? '';
                
                $data['title'] = 'Jadwal Fasilitasi Diperbarui';
                $data['message'] = sprintf(
                    'Jadwal pelaksanaan fasilitasi untuk %s - %s tahun %s telah diperbarui. Jadwal baru: %s s/d %s%s. Harap catat perubahan ini.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun,
                    $tanggalMulai,
                    $tanggalSelesai,
                    $lokasi ? ' di ' . $lokasi : ''
                );
                $data['type'] = 'warning';
                $data['action_url'] = route('permohonan.tahapan.jadwal', $permohonan);
                break;

            case 'hasil_dibuat':
                $data['title'] = 'Hasil Fasilitasi Tersedia';
                $data['message'] = "Hasil fasilitasi untuk permohonan {$kabkota} telah dibuat dan dapat diunduh.";
                $data['type'] = 'success';
                break;

            case 'dokumen_revisi_uploaded':
                $namaDokumen = $additionalData ?? 'Dokumen';
                $data['title'] = 'Dokumen Revisi Telah Diupload Ulang';
                $data['message'] = "Pemohon telah mengupload ulang dokumen \"{$namaDokumen}\" untuk permohonan {$kabkota}. Silakan lakukan verifikasi ulang.";
                $data['type'] = 'info';
                $data['action_url'] = route('permohonan.tahapan.verifikasi', $permohonan);
                break;

            case 'laporan_verifikasi_dibuat':
                $statusText = $additionalData === 'lengkap' ? 'Lengkap' : 'Tidak Lengkap';
                $data['title'] = 'Laporan Verifikasi Telah Dibuat';
                $data['message'] = "Laporan verifikasi untuk permohonan {$kabkota} telah dibuat dengan status: {$statusText}.";
                $data['type'] = 'success';
                break;

            case 'undangan_dikirim':
                $tanggalMulai = $permohonan->penetapanJadwal->tanggal_mulai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d M Y') : '-';
                $tanggalSelesai = $permohonan->penetapanJadwal->tanggal_selesai ? \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d M Y') : '-';
                $lokasi = $permohonan->penetapanJadwal->lokasi ?? '';
                
                $data['title'] = 'Undangan Pelaksanaan Fasilitasi';
                $data['message'] = sprintf(
                    'Anda diundang untuk mengikuti kegiatan fasilitasi untuk %s - %s tahun %s. Pelaksanaan: %s s/d %s%s. Silakan download file undangan lengkap.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun,
                    $tanggalMulai,
                    $tanggalSelesai,
                    $lokasi ? ' di ' . $lokasi : ''
                );
                $data['type'] = 'info';
                break;

            case 'pelaksanaan_selesai':
                $data['title'] = 'Input Hasil Fasilitasi Diperlukan';
                $data['message'] = sprintf(
                    'Pelaksanaan fasilitasi untuk %s - %s tahun %s telah selesai. Silakan segera input hasil fasilitasi pada tahapan berikutnya.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun
                );
                $data['type'] = 'warning';
                $data['action_url'] = route('permohonan.tahapan.hasil', $permohonan);
                break;

            case 'draft_submitted_to_kaban':
                $data['title'] = 'Dokumen Menunggu Persetujuan';
                $data['message'] = sprintf(
                    'Dokumen hasil fasilitasi untuk permohonan %s (%s tahun %s) telah diajukan dan menunggu persetujuan Anda.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun
                );
                $data['type'] = 'warning';
                $data['action_url'] = route('hasil-fasilitasi.show', $permohonan);
                break;

            case 'draft_approved':
                $data['title'] = 'Dokumen Hasil Fasilitasi Disetujui';
                $data['message'] = sprintf(
                    'Dokumen hasil fasilitasi untuk permohonan %s (%s tahun %s) telah disetujui oleh Kepala Badan.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun
                );
                if ($additionalData) {
                    $data['message'] .= ' Keterangan: ' . $additionalData;
                }
                $data['type'] = 'success';
                $data['action_url'] = route('hasil-fasilitasi.show', $permohonan);
                break;

            case 'draft_rejected':
                $data['title'] = 'Dokumen Memerlukan Revisi';
                $data['message'] = sprintf(
                    'Dokumen hasil fasilitasi untuk permohonan %s (%s tahun %s) memerlukan revisi dari Kepala Badan.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun
                );
                if ($additionalData) {
                    $data['message'] .= ' Catatan: ' . $additionalData;
                }
                $data['type'] = 'warning';
                $data['action_url'] = route('hasil-fasilitasi.show', $permohonan);
                break;

            case 'tindak_lanjut_submitted':
                $data['title'] = 'Dokumen Tindak Lanjut Fasilitasi / Evaluasi Disubmit';
                $data['message'] = sprintf(
                    'Pemohon telah submit dokumen tindak lanjut untuk permohonan %s (%s tahun %s).',
                    $kabkota,
                    $jenisDokumen,
                    $tahun
                );
                $data['type'] = 'info';
                $data['action_url'] = route('permohonan.tahapan.tindak-lanjut', $permohonan);
                break;

            case 'penetapan_perda_submitted':
                $data['title'] = 'Dokumen Penetapan PERDA / PERKADA Disubmit';
                $data['message'] = sprintf(
                    'Pemohon telah submit dokumen penetapan PERDA / PERKADA untuk permohonan %s (%s tahun %s). Proses permohonan sudah selesai.',
                    $kabkota,
                    $jenisDokumen,
                    $tahun
                );
                $data['type'] = 'success';
                $data['action_url'] = route('permohonan.tahapan.penetapan', $permohonan);
                break;

            default:
                $data['title'] = 'Notifikasi Permohonan';
                $data['message'] = "Ada update untuk permohonan {$kabkota}.";
                $data['type'] = 'info';
        }

        return $data;
    }

    /**
     * Get WhatsApp message berdasarkan event
     */
    private function getWhatsAppMessage(Permohonan $permohonan, $event, User $user, $additionalData = null)
    {
        $kabkota = $permohonan->kabupatenKota->nama ?? '-';
        $jenisDokumen = $permohonan->jenisDokumen->nama ?? '-';
        $tahun = $permohonan->tahun;

        $message = "📋 *Notifikasi Permohonan Fasilitasi*\n\n";
        $message .= "Halo *{$user->name}*,\n\n";

        switch ($event) {
            case 'submitted':
                $message .= "Permohonan fasilitasi/evaluasi dokumen baru telah diajukan:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "Status: *Menunggu Verifikasi*\n\n";
                
                // Pesan berbeda berdasarkan role
                if ($user->hasRole('verifikator')) {
                    $message .= "Silakan login ke sistem untuk melakukan verifikasi kelengkapan dokumen.\n\n";
                } else {
                    $message .= "Silakan login ke sistem untuk melihat detail permohonan.\n\n";
                }
                break;

            case 'verifikasi_selesai':
                $message .= "Verifikasi dokumen persyaratan fasilitasi / evaluasi telah selesai:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                
                // Status berbeda berdasarkan kondisi
                if ($additionalData === 'lengkap') {
                    // Untuk admin: instruksi buat laporan
                    $message .= "Status Verifikasi: *Lengkap dan Sesuai* ✅\n\n";
                    $message .= "Semua dokumen persyaratan fasilitasi / evaluasi telah diverifikasi dan sesuai.\n\n";
                    if ($user->hasRole('admin_peran')) {
                        $message .= "🎯 *Aksi Diperlukan*\n";
                        $message .= "Silakan buat laporan verifikasi untuk melanjutkan proses ke tahap berikutnya.\n\n";
                    } else {
                        $message .= "Permohonan akan segera diproses ke tahap berikutnya.\n\n";
                    }
                } elseif ($additionalData === 'revisi') {
                    $message .= "Status Verifikasi: *Perlu Revisi* ⚠️\n\n";
                    $message .= "Terdapat dokumen persyaratan fasilitasi / evaluasi yang perlu diperbaiki dan diupload kembali.\n";
                    $message .= "Silakan login ke sistem untuk melihat dokumen yang perlu direvisi beserta catatan verifikator.\n\n";
                } elseif ($additionalData === 'tidak_lengkap') {
                    $message .= "Status Verifikasi: *Tidak Lengkap* ⚠️\n\n";
                    $message .= "Laporan verifikasi telah dibuat dengan status tidak lengkap.\n";
                    $message .= "Silakan login ke sistem untuk melihat detail dan tindak lanjut yang diperlukan.\n\n";
                } else {
                    $message .= "Status Verifikasi: *Selesai* ✅\n\n";
                    $message .= "Silakan login ke sistem untuk melihat detail hasil verifikasi dokumen persyaratan fasilitasi / evaluasi.\n\n";
                }
                break;

            case 'jadwal_ditetapkan':
                $message .= "Jadwal pelaksanaan fasilitasi telah ditetapkan:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                
                // Detail jadwal
                if ($permohonan->penetapanJadwal) {
                    $tanggalMulai = \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d F Y');
                    $tanggalSelesai = \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d F Y');
                    $message .= "📆 Tanggal Pelaksanaan: *{$tanggalMulai}*\n";
                    if ($tanggalMulai != $tanggalSelesai) {
                        $message .= "📆 Sampai: *{$tanggalSelesai}*\n";
                    }
                    if ($permohonan->penetapanJadwal->lokasi) {
                        $message .= "📍 Lokasi: *{$permohonan->penetapanJadwal->lokasi}*\n";
                    }
                    $message .= "\n";
                }
                
                if ($additionalData === 'admin' && $user->hasRole('admin_peran')) {
                    $message .= "🎯 *Aksi Diperlukan*\n";
                    $message .= "Silakan buat undangan pelaksanaan untuk permohonan ini.\n\n";
                } else {
                    $message .= "Silakan login ke sistem untuk melihat detail jadwal dan undangan.\n\n";
                }
                break;

            case 'jadwal_diubah':
                $message .= "⚠️ *Perubahan Jadwal Pelaksanaan Fasilitasi*\n\n";
                $message .= "Jadwal pelaksanaan fasilitasi telah diperbarui:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                
                // Detail jadwal baru
                if ($permohonan->penetapanJadwal) {
                    $tanggalMulai = \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d F Y');
                    $tanggalSelesai = \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d F Y');
                    $message .= "📆 *Jadwal Baru:*\n";
                    $message .= "   Tanggal: *{$tanggalMulai}*\n";
                    if ($tanggalMulai != $tanggalSelesai) {
                        $message .= "   Sampai: *{$tanggalSelesai}*\n";
                    }
                    if ($permohonan->penetapanJadwal->lokasi) {
                        $message .= "   Lokasi: *{$permohonan->penetapanJadwal->lokasi}*\n";
                    }
                    $message .= "\n";
                }
                
                $message .= "⚠️ *Harap catat perubahan jadwal ini.*\n";
                $message .= "Silakan login ke sistem untuk melihat detail lengkap jadwal yang telah diperbarui.\n\n";
                break;

            case 'hasil_dibuat':
                $message .= "Hasil fasilitasi/evaluasi telah tersedia:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "Silakan login ke sistem untuk mengunduh hasil fasilitasi.\n\n";
                break;

            case 'dokumen_revisi_uploaded':
                $namaDokumen = $additionalData ?? 'Dokumen';
                $message .= "Dokumen revisi telah diupload ulang oleh pemohon:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n";
                $message .= "📎 Dokumen: *{$namaDokumen}*\n\n";
                $message .= "Status: *Menunggu Verifikasi Ulang* 🔄\n\n";
                $message .= "Silakan login ke sistem untuk melakukan verifikasi ulang terhadap dokumen yang telah diupload ulang.\n\n";
                break;

            case 'laporan_verifikasi_dibuat':
                $statusText = $additionalData === 'lengkap' ? 'Lengkap ✅' : 'Tidak Lengkap ⚠️';
                $message .= "Laporan verifikasi telah dibuat oleh Admin:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n";
                $message .= "📋 Status Kelengkapan: *{$statusText}*\n\n";
                
                if ($user->hasRole('kaban')) {
                    $message .= "🎯 *Aksi Diperlukan*\n";
                    $message .= "Silakan tetapkan jadwal pelaksanaan fasilitasi untuk permohonan ini.\n\n";
                } elseif ($user->hasRole(['verifikator', 'fasilitator'])) {
                    $message .= "Verifikasi dokumen persyaratan telah selesai dan laporan telah dibuat. Menunggu penetapan jadwal pelaksanaan fasilitasi.\n\n";
                } else {
                    $message .= "Dokumen persyaratan fasilitasi Anda telah berhasil diverifikasi. Proses akan dilanjutkan ke tahap penetapan jadwal pelaksanaan fasilitasi.\n\n";
                }
                
                $message .= "Silakan login ke sistem untuk melihat detail laporan verifikasi.\n\n";
                break;

            case 'undangan_dikirim':
                $message .= "📨 *Undangan Pelaksanaan Fasilitasi*\n\n";
                $message .= "Anda diundang untuk mengikuti kegiatan fasilitasi/evaluasi dokumen:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                
                // Detail jadwal pelaksanaan
                if ($permohonan->penetapanJadwal) {
                    $tanggalMulai = \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_mulai)->format('d F Y');
                    $tanggalSelesai = \Carbon\Carbon::parse($permohonan->penetapanJadwal->tanggal_selesai)->format('d F Y');
                    $message .= "📆 *Jadwal Pelaksanaan:*\n";
                    $message .= "   Tanggal: *{$tanggalMulai}*\n";
                    if ($tanggalMulai != $tanggalSelesai) {
                        $message .= "   Sampai: *{$tanggalSelesai}*\n";
                    }
                    if ($permohonan->penetapanJadwal->lokasi) {
                        $message .= "   Lokasi: *{$permohonan->penetapanJadwal->lokasi}*\n";
                    }
                    $message .= "\n";
                }
                
                $message .= "📎 *File undangan lengkap telah tersedia.*\n";
                $message .= "Silakan login ke sistem untuk download file undangan dan melihat detail lengkap kegiatan.\n\n";
                $message .= "⚠️ Harap konfirmasi kehadiran Anda melalui sistem.\n\n";
                break;

            case 'pelaksanaan_selesai':
                $message .= "✅ *Pelaksanaan Fasilitasi Selesai*\n\n";
                $message .= "Pelaksanaan kegiatan fasilitasi/evaluasi telah diselesaikan:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                
                    $message .= "Silakan segera melakukan input hasil fasilitasi/evaluasi pada sistem.\n\n";
                break;

            case 'draft_submitted_to_kaban':
                $message .= "📋 *Dokumen Menunggu Persetujuan*\n\n";
                $message .= "Dokumen hasil fasilitasi/evaluasi telah diajukan dan menunggu persetujuan Anda:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "⚠️ Status: *Menunggu Persetujuan Kepala Badan*\n\n";
                $message .= "Silakan login ke sistem untuk meninjau dan memberikan persetujuan terhadap dokumen hasil fasilitasi.\n\n";
                break;

            case 'draft_approved':
                $message .= "✅ *Dokumen Disetujui*\n\n";
                $message .= "Dokumen hasil fasilitasi/evaluasi telah disetujui oleh Kepala Badan:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "✅ Status: *Disetujui oleh Kepala Badan*\n\n";
                
                if ($additionalData) {
                    $message .= "📝 *Keterangan:*\n{$additionalData}\n\n";
                }
                
                $message .= "Dokumen dapat dilanjutkan ke tahapan berikutnya. Silakan login ke sistem untuk melihat detail lebih lanjut.\n\n";
                break;

            case 'draft_rejected':
                $message .= "⚠️ *Dokumen Memerlukan Revisi*\n\n";
                $message .= "Dokumen hasil fasilitasi/evaluasi memerlukan revisi dari Kepala Badan:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "⚠️ Status: *Memerlukan Revisi*\n\n";
                
                if ($additionalData) {
                    $message .= "📝 *Catatan Revisi dari Kepala Badan:*\n{$additionalData}\n\n";
                }
                
                $message .= "Silakan perbaiki dokumen sesuai catatan, kemudian upload ulang dan ajukan kembali untuk persetujuan.\n\n";
                break;

            case 'tindak_lanjut_submitted':
                $message .= "📋 *Dokumen Tindak Lanjut Fasilitasi / Evaluasi Disubmit*\n\n";
                $message .= "Pemohon telah submit dokumen tindak lanjut hasil fasilitasi/evaluasi:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "✅ Status: *Dokumen Tindak Lanjut Telah Disubmit*\n\n";
                $message .= "Silakan login ke sistem untuk melihat dokumen tindak lanjut yang telah disubmit oleh pemohon.\n\n";
                break;

            case 'penetapan_perda_submitted':
                $message .= "📋 *Dokumen Penetapan PERDA / PERKADA Disubmit*\n\n";
                $message .= "Pemohon telah submit dokumen penetapan PERDA / PERKADA:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "✅ Status: *Selesai - Proses Permohonan Telah Lengkap*\n\n";
                $message .= "Silakan login ke sistem untuk melihat dokumen penetapan PERDA / PERKADA yang telah disubmit oleh pemohon.\n\n";
                break;

            default:
                $message .= "Ada update untuk permohonan:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "Silakan login ke sistem untuk melihat detail.\n\n";
        }

        $message .= "_*SI-FEDORA*_\n";
        $message .= "_si-fedora.malutprov.go.id_";

        return $message;
    }
}
