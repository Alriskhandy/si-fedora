<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Method to return the WhatsApp view
     */
    public function index()
    {
        return view('whatsapp.index');
    }
    
    /**
     * Method to handle form submission and send a WhatsApp message via Twilio
     */
    public function store(Request $request)
    {
        $to = $request->phone;
        $message = $request->message;
        
        $result = $this->sendMessage($to, $message);
        
        if ($result['success']) {
            return "Message sent successfully! SID: " . $result['sid'];
        } else {
            return "Error sending message: " . $result['error'];
        }
    }
    
    /**
     * Static method untuk mengirim WhatsApp message
     * 
     * @param string $phoneNumber Nomor telepon (format: +628xxx atau 08xxx)
     * @param string $message Isi pesan
     * @return array ['success' => bool, 'sid' => string|null, 'error' => string|null]
     */
    public static function sendMessage($phoneNumber, $message)
    {
        try {
            // Format nomor telepon
            $phoneNumber = self::formatPhoneNumber($phoneNumber);
            
            // Get Twilio credentials
            $twilioSid = env('TWILIO_SID');
            $twilioAuthToken = env('TWILIO_AUTH_TOKEN');
            $twilioWhatsappFrom = env('TWILIO_WHATSAPP_FROM');
            
            // Validasi credentials
            if (!$twilioSid || !$twilioAuthToken || !$twilioWhatsappFrom) {
                throw new Exception('Twilio credentials not configured');
            }
            
            // Create Twilio client
            $client = new Client($twilioSid, $twilioAuthToken);
            
            // Send message
            $response = $client->messages->create(
                'whatsapp:' . $phoneNumber,
                [
                    'from' => $twilioWhatsappFrom,
                    'body' => $message
                ]
            );
            
            Log::info('WhatsApp message sent', [
                'to' => $phoneNumber,
                'sid' => $response->sid
            ]);
            
            return [
                'success' => true,
                'sid' => $response->sid,
                'error' => null
            ];
            
        } catch (Exception $e) {
            Log::error('WhatsApp send failed', [
                'to' => $phoneNumber ?? null,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'sid' => null,
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
    private static function formatPhoneNumber($phone)
    {
        // Hapus karakter non-numeric
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika diawali 0, ganti dengan +62
        if (substr($phone, 0, 1) === '0') {
            $phone = '+62' . substr($phone, 1);
        }
        // Jika diawali 62, tambahkan +
        elseif (substr($phone, 0, 2) === '62') {
            $phone = '+' . $phone;
        }
        // Jika belum ada +, tambahkan +62
        elseif (substr($phone, 0, 1) !== '+') {
            $phone = '+62' . $phone;
        }
        
        return $phone;
    }
}
