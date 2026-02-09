<?php

namespace App\Notifications;

use App\Models\UndanganPelaksanaan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UndanganPelaksanaanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $undangan;

    /**
     * Create a new notification instance.
     */
    public function __construct(UndanganPelaksanaan $undangan)
    {
        $this->undangan = $undangan;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['whatsapp'];
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsApp($notifiable): string
    {
        $permohonan = $this->undangan->permohonan;
        $jadwal = $this->undangan->penetapanJadwal;
        $kabupaten = $permohonan->kabupatenKota;

        $message = "*UNDANGAN PELAKSANAAN FASILITASI*\n\n";
        $message .= "Kepada Yth. {$notifiable->name}\n";
        $message .= "*{$kabupaten->getFullNameAttribute()}*\n\n";
        
        $message .= "Dengan hormat,\n";
        $message .= "Bersama ini kami mengundang Saudara/i untuk mengikuti kegiatan Fasilitasi Penyusunan RKPD.\n\n";
        
        $message .= "📅 *Jadwal Pelaksanaan*\n";
        $message .= "Tanggal: {$jadwal->tanggal_mulai->format('d M Y')} - {$jadwal->tanggal_selesai->format('d M Y')}\n";
        $message .= "Lokasi: {$jadwal->lokasi}\n";
        $message .= "Durasi: {$jadwal->durasi_hari} hari\n\n";

        $message .= "📄 *File Undangan*\n";
        $message .= "File undangan lengkap telah tersedia di sistem.\n\n";

        // Add URL if available
        if (config('app.url')) {
            $message .= "🔗 Link: " . route('undangan-pelaksanaan.view-penerima', $this->undangan->id) . "\n\n";
        }

        $message .= "Silakan login ke sistem untuk melihat detail dan mengunduh file undangan lengkap.\n\n";
        $message .= "Terima kasih atas perhatian dan kerjasamanya.\n\n";
        $message .= "_Sistem Informasi FEDORA_";

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'undangan_id' => $this->undangan->id,
            'permohonan_id' => $this->undangan->permohonan_id,
            'kabupaten_kota' => $this->undangan->permohonan->kabupatenKota->nama,
            'jadwal' => $this->undangan->penetapanJadwal->tanggal_mulai->format('d M Y'),
        ];
    }
}
