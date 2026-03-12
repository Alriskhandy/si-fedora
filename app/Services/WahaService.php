<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WAHA (WhatsApp HTTP API) Service
 * 
 * Pemetaan API Endpoints:
 * - POST /api/sendText     : Mengirim pesan teks ke nomor WhatsApp
 * - POST /api/sendSeen     : Menandai pesan sebagai telah dibaca
 * - POST /api/startTyping  : Menampilkan indikator "sedang mengetik"
 * - POST /api/stopTyping   : Menghentikan indikator "sedang mengetik"
 */
class WahaService
{
    /**
     * Base URL untuk WAHA API
     */
    private $baseUrl;

    /**
     * Session ID untuk WAHA
     */
    private $session;

    /**
     * API Key untuk WAHA
     */
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('WAHA_URL');
        $this->session = env('WAHA_SESSION');
        $this->apiKey = env('WAHA_API_KEY');
    }

    /**
     * Format nomor telepon ke format WhatsApp chatId
     * 
     * @param string $phone
     * @return string
     */
    private function formatChatId($phone)
    {
        // Pastikan nomor dalam format internasional tanpa + atau spasi
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return $phone . '@c.us';
    }

    /**
     * Delay random untuk membuat interaksi lebih natural
     * 
     * @param int $minSeconds Minimal detik (default: 1)
     * @param int $maxSeconds Maksimal detik (default: 3)
     * @return void
     */
    private function randomDelay($minSeconds = 1, $maxSeconds = 3)
    {
        $microseconds = rand($minSeconds * 1000000, $maxSeconds * 1000000);
        usleep($microseconds);
    }

    /**
     * Kirim pesan teks ke nomor WhatsApp
     * Endpoint: POST /api/sendText
     * 
     * @param string $phone Nomor telepon tujuan (format: 628xxx atau +628xxx)
     * @param string $message Isi pesan yang akan dikirim
     * @param string|null $replyTo ID pesan yang akan di-reply (optional)
     * @param bool $linkPreview Tampilkan preview link (default: true)
     * @param bool $linkPreviewHighQuality Preview link kualitas tinggi (default: false)
     * @return array Response dari WAHA API
     */
    public function sendMessage($phone, $message, $replyTo = null, $linkPreview = true, $linkPreviewHighQuality = false)
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey
            ])->post($this->baseUrl . '/api/sendText', [
                'chatId' => $this->formatChatId($phone),
                'reply_to' => $replyTo,
                'text' => $message,
                'linkPreview' => $linkPreview,
                'linkPreviewHighQuality' => $linkPreviewHighQuality,
                'session' => $this->session
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WAHA sendMessage error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tandai pesan sebagai telah dibaca
     * Endpoint: POST /api/sendSeen
     * 
     * @param string $phone Nomor telepon
     * @param array $messageIds Array ID pesan yang akan ditandai dibaca (optional)
     * @param string|null $participant ID participant untuk grup (optional)
     * @return array Response dari WAHA API
     */
    public function sendSeen($phone, $messageIds = [], $participant = null)
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey
            ])->post($this->baseUrl . '/api/sendSeen', [
                'chatId' => $this->formatChatId($phone),
                'messageIds' => $messageIds,
                'participant' => $participant,
                'session' => $this->session
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WAHA sendSeen error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tampilkan indikator "sedang mengetik" di chat
     * Endpoint: POST /api/startTyping
     * 
     * @param string $phone Nomor telepon
     * @return array Response dari WAHA API
     */
    public function startTyping($phone)
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey
            ])->post($this->baseUrl . '/api/startTyping', [
                'chatId' => $this->formatChatId($phone),
                'session' => $this->session
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WAHA startTyping error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hentikan indikator "sedang mengetik" di chat
     * Endpoint: POST /api/stopTyping
     * 
     * @param string $phone Nomor telepon
     * @return array Response dari WAHA API
     */
    public function stopTyping($phone)
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey
            ])->post($this->baseUrl . '/api/stopTyping', [
                'chatId' => $this->formatChatId($phone),
                'session' => $this->session
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WAHA stopTyping error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim OTP dengan efek mengetik untuk pengalaman pengguna yang lebih baik
     * 
     * @param string $phone Nomor telepon tujuan
     * @param string $otp Kode OTP
     * @return array Response dari pengiriman pesan
     */
    public function sendOTP($phone, $otp)
    {
        try {
            // Tampilkan indikator sedang mengetik
            $this->startTyping($phone);
            
            // Delay random 1-3 detik untuk efek natural
            $this->randomDelay(1, 3);
            
            // Hentikan indikator mengetik
            $this->stopTyping($phone);

            // Kirim pesan OTP
            $message = "Kode OTP Anda: *{$otp}*\n\nJangan bagikan kode ini kepada siapapun.\nKode berlaku selama 5 menit.";
            $result = $this->sendMessage($phone, $message);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('WAHA sendOTP error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim notifikasi dengan efek mengetik
     * 
     * @param string $phone Nomor telepon tujuan
     * @param string $message Isi notifikasi
     * @param bool $markAsSeen Apakah menandai sebagai telah dibaca setelah dikirim
     * @return array Response dari pengiriman pesan
     */
    public function sendNotification($phone, $message, $markAsSeen = false)
    {
        try {
            // Tampilkan indikator sedang mengetik
            $this->startTyping($phone);
            
            // Delay random 1-3 detik untuk efek natural
            $this->randomDelay(1, 3);
            
            // Hentikan indikator mengetik
            $this->stopTyping($phone);

            // Kirim notifikasi
            $result = $this->sendMessage($phone, $message);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('WAHA sendNotification error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}