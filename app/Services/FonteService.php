<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fonnte WhatsApp Service
 * 
 * Service untuk mengirim pesan WhatsApp menggunakan Fonnte API
 * Dokumentasi: https://api.fonnte.com/docs
 */
class FonteService
{
    /**
     * Base URL untuk Fonnte API
     */
    private $baseUrl = 'https://api.fonnte.com';

    /**
     * API Token untuk Fonnte
     */
    private $token;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN');
    }

    /**
     * Format nomor telepon ke format WhatsApp
     * 
     * @param string $phone
     * @return string
     */
    private function formatPhone($phone)
    {
        // Pastikan nomor dalam format internasional tanpa + atau spasi
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Pastikan dimulai dengan 62 (Indonesia)
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . ltrim($phone, '0');
        }
        
        return $phone;
    }

    /**
     * Kirim pesan WhatsApp menggunakan Fonnte API
     * 
     * @param string $target Nomor telepon tujuan
     * @param string $message Isi pesan
     * @return array Response dari Fonnte API
     */
    public function sendMessage($target, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->asForm()->post($this->baseUrl . '/send', [
                'target' => $this->formatPhone($target),
                'message' => $message,
                'countryCode' => '62'
            ]);

            $result = $response->json();
            
            // Log response untuk debugging
            Log::info('Fonnte sendMessage response', [
                'target' => $target,
                'status' => $response->status(),
                'response' => $result
            ]);

            return [
                'success' => $response->successful(),
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte sendMessage error: ' . $e->getMessage(), [
                'target' => $target,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim OTP ke WhatsApp
     * 
     * @param string $phone Nomor telepon tujuan
     * @param string $otp Kode OTP
     * @return array Response dari pengiriman pesan
     */
    public function sendOTP($phone, $otp)
    {
        try {
            $message = "🔐 *Kode Verifikasi OTP*\n\n";
            $message .= "Kode OTP Anda: *{$otp}*\n\n";
            $message .= "⚠️ Jangan bagikan kode ini kepada siapapun.\n";
            $message .= "⏱️ Kode berlaku selama 5 menit.\n\n";
            $message .= "_*SI-FEDORA*_\n";
            $message .= "_si-fedora.malutprov.go.id_";
            
            $result = $this->sendMessage($phone, $message);
            
            if ($result['success']) {
                Log::info("OTP sent successfully to {$phone}");
            } else {
                Log::error("Failed to send OTP to {$phone}", $result);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Fonnte sendOTP error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim notifikasi ke WhatsApp
     * 
     * @param string $phone Nomor telepon tujuan
     * @param string $message Isi notifikasi
     * @return array Response dari pengiriman pesan
     */
    public function sendNotification($phone, $message)
    {
        try {
            $result = $this->sendMessage($phone, $message);
            
            if ($result['success']) {
                Log::info("Notification sent successfully to {$phone}");
            } else {
                Log::error("Failed to send notification to {$phone}", $result);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Fonnte sendNotification error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
