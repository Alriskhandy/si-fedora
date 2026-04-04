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
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'createdBy']);

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
     */
    public function notifyVerifikasiSelesai(Permohonan $permohonan, $status = 'lengkap')
    {
        try {
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'createdBy']);

            // Kirim ke pemohon
            $this->notifyPemohon($permohonan, 'verifikasi_selesai', $status);

            // Kirim ke admin
            $this->notifyAdmins($permohonan, 'verifikasi_selesai', $status);

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
            $permohonan->load(['kabupatenKota', 'jenisDokumen', 'penetapanJadwal']);

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
     * Kirim notifikasi ke Pemohon
     */
    private function notifyPemohon(Permohonan $permohonan, $event, $additionalData = null)
    {
        $pemohon = $permohonan->createdBy;

        if (!$pemohon) {
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
                $status = $additionalData === 'lengkap' ? 'lengkap' : 'perlu dilengkapi';
                $data['title'] = 'Verifikasi Dokumen Selesai';
                $data['message'] = "Verifikasi dokumen untuk permohonan {$kabkota} telah selesai dengan status: {$status}.";
                $data['type'] = $additionalData === 'lengkap' ? 'success' : 'warning';
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
                $status = $additionalData === 'lengkap' ? 'Lengkap ✅' : 'Perlu Dilengkapi ⚠️';
                $message .= "Verifikasi dokumen permohonan Anda telah selesai:\n\n";
                $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
                $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
                $message .= "📅 Tahun: *{$tahun}*\n\n";
                $message .= "Status Verifikasi: *{$status}*\n\n";
                if ($additionalData !== 'lengkap') {
                    $message .= "Silakan login ke sistem untuk melihat dokumen yang perlu dilengkapi.\n\n";
                } else {
                    $message .= "Permohonan Anda akan segera diproses ke tahap berikutnya.\n\n";
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
