<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $twilioSid = config('services.twilio.sid');
        $twilioAuthToken = config('services.twilio.auth_token');
        $this->from = config('services.twilio.whatsapp_from');

        if (!$twilioSid || !$twilioAuthToken || !$this->from) {
            throw new Exception('Twilio credentials not configured properly');
        }

        $this->client = new Client($twilioSid, $twilioAuthToken);
    }

    /**
     * Send WhatsApp message
     * 
     * @param string $to Nomor telepon penerima
     * @param string $message Isi pesan
     * @return array ['success' => bool, 'sid' => string|null, 'error' => string|null, 'status' => string|null]
     */
    public function sendMessage(string $to, string $message): array
    {
        try {
            // Format nomor telepon
            $formattedPhone = $this->formatPhoneNumber($to);

            Log::info('Attempting to send WhatsApp message', [
                'to' => $formattedPhone,
                'message_length' => strlen($message)
            ]);

            // Send message via Twilio
            $response = $this->client->messages->create(
                'whatsapp:' . $formattedPhone,
                [
                    'from' => $this->from,
                    'body' => $message
                ]
            );

            Log::info('WhatsApp message sent', [
                'to' => $formattedPhone,
                'sid' => $response->sid,
                'status' => $response->status
            ]);

            return [
                'success' => true,
                'sid' => $response->sid,
                'status' => $response->status,
                'error' => null
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp send failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'sid' => null,
                'status' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk WhatsApp messages
     * 
     * @param array $recipients Array of ['phone' => string, 'message' => string]
     * @return array ['total' => int, 'sent' => int, 'failed' => int, 'results' => array]
     */
    public function sendBulkMessages(array $recipients): array
    {
        $results = [];
        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $result = $this->sendMessage($recipient['phone'], $recipient['message']);

            $results[] = [
                'phone' => $recipient['phone'],
                'success' => $result['success'],
                'sid' => $result['sid'],
                'error' => $result['error']
            ];

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'total' => count($recipients),
            'sent' => $sent,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Check message status
     * 
     * @param string $messageSid
     * @return array
     */
    public function getMessageStatus(string $messageSid): array
    {
        try {
            $message = $this->client->messages($messageSid)->fetch();

            return [
                'success' => true,
                'sid' => $message->sid,
                'status' => $message->status,
                'to' => $message->to,
                'from' => $message->from,
                'error_code' => $message->errorCode,
                'error_message' => $message->errorMessage,
                'date_sent' => $message->dateSent,
                'date_updated' => $message->dateUpdated
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format nomor telepon ke format internasional
     * 
     * @param string $phone
     * @return string
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Hapus karakter non-numeric
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Jika sudah ada +, return as is
        if (substr($phone, 0, 1) === '+') {
            return $phone;
        }

        // Jika diawali 0, ganti dengan +62
        if (substr($phone, 0, 1) === '0') {
            return '+62' . substr($phone, 1);
        }

        // Jika diawali 62, tambahkan +
        if (substr($phone, 0, 2) === '62') {
            return '+' . $phone;
        }

        // Default: tambahkan +62
        return '+62' . $phone;
    }

    /**
     * Validate if phone number is properly formatted
     * 
     * @param string $phone
     * @return bool
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $formatted = $this->formatPhoneNumber($phone);
        // Indonesian phone numbers: +62 followed by 8-12 digits
        return preg_match('/^\+62[0-9]{8,12}$/', $formatted) === 1;
    }
}
