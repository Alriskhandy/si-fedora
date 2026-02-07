<?php

namespace App\Notifications;

use App\Models\SuratPemberitahuan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SuratPemberitahuanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $suratPemberitahuan;

    /**
     * Create a new notification instance.
     */
    public function __construct(SuratPemberitahuan $suratPemberitahuan)
    {
        $this->suratPemberitahuan = $suratPemberitahuan;
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
        $kabupaten = $this->suratPemberitahuan->kabupatenKota;

        $message = "*NOTIFIKASI SURAT PEMBERITAHUAN*\n\n";
        $message .= "Kepada Yth. {$notifiable->name}\n";
        $message .= "*{$kabupaten->getFullNameAttribute()}*\n\n";
        $message .= "Nomor Surat: {$this->suratPemberitahuan->nomor_surat}\n";
        $message .= "Perihal: {$this->suratPemberitahuan->perihal}\n";
        $message .= "Tanggal: " . \Carbon\Carbon::parse($this->suratPemberitahuan->tanggal_surat)->format('d M Y') . "\n\n";
        $message .= "Jadwal Fasilitasi: {$this->suratPemberitahuan->jadwalFasilitasi->nama_kegiatan}\n";
        $message .= "Periode: {$this->suratPemberitahuan->jadwalFasilitasi->tanggal_mulai->format('d M Y')} - {$this->suratPemberitahuan->jadwalFasilitasi->tanggal_selesai->format('d M Y')}\n\n";

        // Add URL if available
        if (config('app.url')) {
            $message .= "Link: " . route('jadwal.show', $this->suratPemberitahuan->jadwal_fasilitasi_id) . "\n\n";
        }

        $message .= "Silakan login ke sistem untuk melihat detail lengkap.\n\n";
        $message .= "Terima kasih.";

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'surat_pemberitahuan_id' => $this->suratPemberitahuan->id,
            'nomor_surat' => $this->suratPemberitahuan->nomor_surat,
            'perihal' => $this->suratPemberitahuan->perihal,
            'jadwal_fasilitasi_id' => $this->suratPemberitahuan->jadwal_fasilitasi_id,
        ];
    }
}
