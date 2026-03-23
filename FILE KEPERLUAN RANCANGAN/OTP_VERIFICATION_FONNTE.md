# OTP Verification menggunakan Fonnte - Dokumentasi

## 📋 Overview

Sistem verifikasi OTP (One-Time Password) menggunakan **Fonnte API** untuk mengirim kode verifikasi melalui WhatsApp kepada pengguna saat registrasi atau verifikasi nomor telepon.

## 🔧 Konfigurasi

### 1. Dapatkan Token Fonnte

1. Kunjungi [https://api.fonnte.com](https://api.fonnte.com)
2. Daftar dan dapatkan token device Anda
3. Salin token tersebut

### 2. Setup Environment Variables

Tambahkan konfigurasi berikut di file `.env`:

```env
# Fonnte WhatsApp API Configuration
# Get your token from https://api.fonnte.com
FONNTE_TOKEN=your_fonnte_token_here
```

Ganti `your_fonnte_token_here` dengan token yang Anda dapatkan dari Fonnte.

## 📁 Struktur File

```
app/
├── Services/
│   └── FonteService.php          # Service untuk Fonnte WhatsApp API
├── Http/
│   └── Controllers/
│       └── Auth/
│           └── PhoneVerificationController.php  # Controller verifikasi OTP
└── Models/
    └── OtpCode.php               # Model untuk menyimpan OTP
```

## 🚀 Cara Kerja

### Alur Verifikasi OTP

1. **Pengguna memasukkan nomor WhatsApp** di halaman verifikasi
2. **Sistem generate OTP** (6 digit angka) dan simpan ke database
3. **Fonnte mengirim OTP** ke nomor WhatsApp pengguna
4. **Pengguna memasukkan OTP** yang diterima
5. **Sistem memverifikasi OTP** dengan yang tersimpan di database
6. **Jika valid**, nomor WhatsApp ditandai sebagai terverifikasi

### Format Pesan OTP

Pesan yang dikirim ke pengguna:

```
🔐 *Kode Verifikasi OTP*

Kode OTP Anda: *123456*

⚠️ Jangan bagikan kode ini kepada siapapun.
⏱️ Kode berlaku selama 5 menit.

_Aplikasi SI-FEDORA_
```

## 💻 Implementasi

### Menggunakan FonteService

```php
use App\Services\FonteService;

// Inject service
$fonteService = app(FonteService::class);

// Kirim OTP
$result = $fonteService->sendOTP($phoneNumber, $otpCode);

if ($result['success']) {
    // OTP berhasil dikirim
    Log::info("OTP sent to {$phoneNumber}");
} else {
    // Gagal mengirim OTP
    Log::error("Failed to send OTP: " . $result['error']);
}
```

### Kirim Notifikasi Custom

```php
use App\Services\FonteService;

$fonteService = app(FonteService::class);

// Kirim pesan custom
$result = $fonteService->sendMessage($phoneNumber, "Pesan custom Anda");

// Atau gunakan method sendNotification
$result = $fonteService->sendNotification($phoneNumber, "Notifikasi custom");
```

## 📊 API Methods

### FonteService Methods

#### `sendMessage($target, $message)`

Mengirim pesan WhatsApp ke nomor tujuan.

**Parameters:**
- `$target` (string): Nomor telepon tujuan (format: 628xxx atau +628xxx)
- `$message` (string): Isi pesan yang akan dikirim

**Returns:**
```php
[
    'success' => true/false,
    'data' => [...], // Response dari Fonnte API
    'error' => 'Error message' // Jika gagal
]
```

#### `sendOTP($phone, $otp)`

Mengirim kode OTP ke nomor WhatsApp dengan format pesan yang sudah ditentukan.

**Parameters:**
- `$phone` (string): Nomor telepon tujuan
- `$otp` (string): Kode OTP 6 digit

**Returns:** Same as `sendMessage()`

#### `sendNotification($phone, $message)`

Mengirim notifikasi ke nomor WhatsApp.

**Parameters:**
- `$phone` (string): Nomor telepon tujuan
- `$message` (string): Isi notifikasi

**Returns:** Same as `sendMessage()`

## 🔒 Security & Best Practices

### 1. Validasi Nomor Telepon

```php
// Format nomor telepon otomatis di formatPhone()
// - Menghapus karakter non-numeric
// - Memastikan format dimulai dengan 62 (Indonesia)
// - Contoh: 081234567890 → 6281234567890
```

### 2. Rate Limiting

Implementasikan rate limiting untuk mencegah spam:

```php
// Di routes/web.php
Route::post('/send-otp', [PhoneVerificationController::class, 'sendOtp'])
    ->middleware('throttle:3,1'); // Max 3 requests per menit
```

### 3. OTP Expiry

OTP otomatis kadaluarsa setelah 5 menit (dikonfigurasi di model `OtpCode`).

### 4. Logging

Semua aktivitas pengiriman OTP tercatat di log:

```bash
# Cek log
tail -f storage/logs/laravel.log
```

## ⚠️ Troubleshooting

### OTP tidak terkirim

1. **Cek token Fonnte**: Pastikan `FONNTE_TOKEN` di `.env` sudah benar
2. **Cek log**: Lihat `storage/logs/laravel.log` untuk error detail
3. **Cek saldo Fonnte**: Pastikan saldo device Fonnte masih mencukupi
4. **Cek format nomor**: Nomor harus format Indonesia (62xxx)

### Error: "FONNTE_TOKEN not configured"

```bash
# Pastikan .env sudah ter-load ulang
php artisan config:clear
php artisan cache:clear
```

### Pesan tidak sampai ke WhatsApp

1. Pastikan nomor WhatsApp aktif dan terdaftar
2. Cek status device di dashboard Fonnte
3. Pastikan device WhatsApp sedang online

## 📚 Referensi

- [Dokumentasi Fonnte API](https://api.fonnte.com/docs)
- [Laravel HTTP Client](https://laravel.com/docs/11.x/http-client)
- [Laravel Logging](https://laravel.com/docs/11.x/logging)

## 🔄 Migrasi dari WAHA

Jika Anda sebelumnya menggunakan WAHA service:

1. **Environment Variables**: Ganti `WAHA_URL`, `WAHA_SESSION`, `WAHA_API_KEY` dengan `FONNTE_TOKEN`
2. **Service Injection**: Ganti `WahaService` dengan `FonteService` di controller
3. **Method Calls**: Method signature sama, tidak perlu ubah kode controller

## ⚡ Performance Tips

1. **Queue Processing**: Pertimbangkan menggunakan queue untuk mengirim OTP jika volume tinggi
2. **Caching**: Cache hasil validasi nomor telepon untuk mengurangi beban
3. **Monitoring**: Setup monitoring untuk track success rate pengiriman OTP

---

**Last Updated:** March 23, 2026  
**Version:** 1.0.0
