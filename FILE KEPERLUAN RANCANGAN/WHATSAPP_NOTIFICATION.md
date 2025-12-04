# WhatsApp Notification System - Documentation

## 📁 Struktur File

```
app/
├── Services/
│   └── WhatsAppService.php          # Core service untuk WhatsApp
├── Notifications/
│   ├── SuratPemberitahuanNotification.php  # Notification untuk surat
│   └── Channels/
│       └── WhatsAppChannel.php      # Custom notification channel
├── Jobs/
│   └── SendSuratPemberitahuanJob.php  # Queue job untuk kirim notif
└── Providers/
    └── AppServiceProvider.php       # Register service & channel

config/
└── services.php                     # Twilio configuration

.env                                 # Environment variables
```

## 🚀 Cara Implementasi

### 1. Menggunakan Service Langsung (Sync)

```php
use App\Services\WhatsAppService;

// Di controller atau class lainnya
public function sendWhatsApp()
{
    $whatsapp = app(WhatsAppService::class);
    
    $result = $whatsapp->sendMessage(
        '+6282394603002',
        'Halo, ini pesan test!'
    );
    
    if ($result['success']) {
        // Berhasil
        $sid = $result['sid'];
        $status = $result['status'];
    } else {
        // Gagal
        $error = $result['error'];
    }
}
```

### 2. Menggunakan Notification (Async - Recommended)

```php
use App\Notifications\SuratPemberitahuanNotification;

// Kirim ke satu user
$user->notify(new SuratPemberitahuanNotification($suratPemberitahuan));

// Kirim ke banyak user
$users = User::whereNotNull('phone')->get();
Notification::send($users, new SuratPemberitahuanNotification($suratPemberitahuan));
```

### 3. Menggunakan Job (Background - Recommended)

```php
use App\Jobs\SendSuratPemberitahuanJob;

// Dispatch job
SendSuratPemberitahuanJob::dispatch($suratPemberitahuan);

// Dispatch dengan delay
SendSuratPemberitahuanJob::dispatch($suratPemberitahuan)->delay(now()->addMinutes(5));

// Dispatch ke specific queue
SendSuratPemberitahuanJob::dispatch($suratPemberitahuan)->onQueue('notifications');
```

## 📝 Membuat Notification Baru

### Step 1: Buat Notification Class

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomWhatsAppNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable): string
    {
        return "Halo {$notifiable->name}, ini pesan custom!";
    }
}
```

### Step 2: Gunakan Notification

```php
$user->notify(new CustomWhatsAppNotification($data));
```

## 🔧 Konfigurasi

### Environment Variables (.env)

```env
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### Queue Configuration

Di `.env`:
```env
QUEUE_CONNECTION=database
```

Jalankan migration queue:
```bash
php artisan queue:table
php artisan migrate
```

Jalankan queue worker:
```bash
php artisan queue:work
# atau
php artisan queue:listen
```

## 📊 Monitoring & Logging

### Cek Log

Log tersimpan di `storage/logs/laravel.log`:

```
[INFO] WhatsApp message sent
[ERROR] WhatsApp send failed
```

### Cek Status Message

```php
$whatsapp = app(WhatsAppService::class);
$status = $whatsapp->getMessageStatus('SMxxxxxxxxxxxx');

// Output:
// [
//     'status' => 'delivered',
//     'error_code' => null,
//     'date_sent' => ...
// ]
```

## 🎯 Use Cases

### Use Case 1: Kirim Surat Pemberitahuan (Sudah Implemented)

```php
// Di SuratPemberitahuanController
public function send(SuratPemberitahuan $suratPemberitahuan)
{
    $suratPemberitahuan->update(['status' => 'sent']);
    SendSuratPemberitahuanJob::dispatch($suratPemberitahuan);
    
    return redirect()->back()->with('success', 'Surat terkirim!');
}
```

### Use Case 2: Reminder Jadwal Fasilitasi

```php
<?php

namespace App\Notifications;

class JadwalFasilitasiReminderNotification extends Notification
{
    protected $jadwal;

    public function __construct($jadwal)
    {
        $this->jadwal = $jadwal;
    }

    public function via($notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable): string
    {
        return "*REMINDER JADWAL FASILITASI*\n\n"
            . "Halo {$notifiable->name},\n\n"
            . "Kegiatan: {$this->jadwal->nama_kegiatan}\n"
            . "Waktu: {$this->jadwal->tanggal_mulai->format('d M Y H:i')}\n\n"
            . "Jangan lupa hadir ya!";
    }
}

// Penggunaan:
$users->each->notify(new JadwalFasilitasiReminderNotification($jadwal));
```

### Use Case 3: Update Status Permohonan

```php
<?php

namespace App\Notifications;

class PermohonanStatusNotification extends Notification
{
    protected $permohonan;
    protected $status;

    public function __construct($permohonan, $status)
    {
        $this->permohonan = $permohonan;
        $this->status = $status;
    }

    public function via($notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable): string
    {
        $statusText = [
            'verified' => 'telah diverifikasi',
            'rejected' => 'ditolak',
            'approved' => 'disetujui',
        ];

        return "*UPDATE STATUS PERMOHONAN*\n\n"
            . "Halo {$notifiable->name},\n\n"
            . "Permohonan Anda dengan nomor {$this->permohonan->nomor_permohonan}\n"
            . "Status: " . ($statusText[$this->status] ?? $this->status) . "\n\n"
            . "Silakan cek sistem untuk detail.";
    }
}

// Penggunaan:
$user->notify(new PermohonanStatusNotification($permohonan, 'verified'));
```

## 🧪 Testing

### Test Script (Sudah Ada)

```bash
# Test kirim pesan
php test-whatsapp.php

# Cek status pesan
php check-message-status.php SMxxxxxxxxxxxx
```

### Manual Test via Tinker

```bash
php artisan tinker
```

```php
// Test service
$whatsapp = app(\App\Services\WhatsAppService::class);
$result = $whatsapp->sendMessage('+6282394603002', 'Test dari tinker');
dump($result);

// Test notification
$user = User::find(1);
$surat = SuratPemberitahuan::find(1);
$user->notify(new \App\Notifications\SuratPemberitahuanNotification($surat));
```

## ⚠️ Important Notes

1. **Twilio Sandbox**: Untuk akun trial, nomor penerima harus JOIN sandbox dulu
2. **Queue**: Notification berjalan async jika queue worker aktif
3. **Rate Limit**: Twilio punya rate limit, gunakan queue untuk bulk send
4. **Phone Format**: Service auto-format nomor (08xxx → +62xxx)
5. **Logging**: Semua aktivitas tercatat di log untuk debugging

## 🔍 Troubleshooting

### Problem: Message status "queued" atau "undelivered"
**Solution**: Nomor penerima belum join Twilio Sandbox
```
Kirim WA ke: +1 415 523 8886
Pesan: join <sandbox-code>
```

### Problem: "Twilio credentials not configured"
**Solution**: Cek config/services.php dan .env

### Problem: Notification tidak terkirim
**Solution**: 
1. Pastikan queue worker running: `php artisan queue:work`
2. Cek log: `storage/logs/laravel.log`
3. Pastikan user punya nomor telepon

### Problem: Job gagal terus
**Solution**: Cek failed_jobs table
```bash
php artisan queue:failed
php artisan queue:retry <job-id>
```

## 📚 References

- Twilio PHP SDK: https://www.twilio.com/docs/libraries/php
- Laravel Notifications: https://laravel.com/docs/notifications
- Laravel Queues: https://laravel.com/docs/queues
