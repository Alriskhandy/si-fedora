<?php

namespace App\Notifications\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Get phone number from notifiable (User model)
        $phone = $notifiable->phone ?? $notifiable->phoneNumber ?? null;

        if (!$phone) {
            Log::warning('WhatsApp notification skipped: No phone number', [
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable)
            ]);
            return;
        }

        // Validate phone number
        if (!$this->whatsappService->isValidPhoneNumber($phone)) {
            Log::warning('WhatsApp notification skipped: Invalid phone number', [
                'notifiable_id' => $notifiable->id,
                'phone' => $phone
            ]);
            return;
        }

        // Get message from notification
        $message = $notification->toWhatsApp($notifiable);

        // Send WhatsApp message
        $result = $this->whatsappService->sendMessage($phone, $message);

        if (!$result['success']) {
            Log::error('WhatsApp notification failed', [
                'notifiable_id' => $notifiable->id,
                'phone' => $phone,
                'error' => $result['error']
            ]);
        }
    }
}
