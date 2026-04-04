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
     * Kirim notifikasi saat jadwal ditetapkan
     */
    public function notifyJadwalDitetapkan(Permohonan $permohonan)
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'pemohon', 'penetapanJadwal']);

            // Kirim ke pemohon
            $this->notifyPemohon($permohonan, 'jadwal_ditetapkan');

            // Kirim ke tim fedora
            $this->notifyTimFedora($permohonan, 'jadwal_ditetapkan');

            Log::info('Jadwal ditetapkan notifications sent successfully', [
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
            ->with(['user' => function($q) {
                $q->role('verifikator');
            }])
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
                $data['title'] = 'Jadwal Fasilitasi Ditetapkan';
                $data['message'] = "Jadwal pelaksanaan fasilitasi untuk permohonan {$kabkota} telah ditetapkan.";
                $data['type'] = 'info';
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
                $message .= "Silakan login ke sistem untuk melihat detail jadwal dan undangan.\n\n";
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
