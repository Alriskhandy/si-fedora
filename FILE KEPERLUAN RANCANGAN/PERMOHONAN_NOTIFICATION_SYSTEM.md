# Sistem Notifikasi Permohonan - Dokumentasi

## 📋 Overview

**PermohonanNotificationService** adalah service terpusat untuk mengelola semua notifikasi terkait permohonan fasilitasi/evaluasi dokumen. Service ini mengintegrasikan notifikasi database dan WhatsApp untuk setiap tahapan proses permohonan.

## 🎯 Tujuan

1. **Sentralisasi**: Mengelola semua notifikasi dari satu tempat
2. **Konsistensi**: Format pesan yang seragam untuk database dan WhatsApp
3. **Efisiensi**: Bulk sending untuk WhatsApp menggunakan FonteService
4. **Maintainability**: Mudah ditambahkan event/tahapan baru

## 📁 Struktur File

```
app/
├── Services/
│   ├── FonteService.php                        # Service untuk WhatsApp API
│   └── PermohonanNotificationService.php       # Service notifikasi permohonan (NEW)
├── Http/
│   └── Controllers/
│       └── PermohonanController.php            # Updated: menggunakan notification service
└── Models/
    ├── Notifikasi.php                          # Model untuk notifikasi database
    └── Permohonan.php                          # Model permohonan
```

## 🚀 Cara Penggunaan

### 1. Inject Service di Controller

```php
use App\Services\PermohonanNotificationService;

// Di method controller
$notificationService = app(PermohonanNotificationService::class);
```

### 2. Panggil Method Sesuai Event

#### Event: Permohonan Disubmit
```php
// Kirim notifikasi ke Admin dan Tim Fedora
$notificationService->notifyPermohonanSubmitted($permohonan);
```

#### Event: Verifikasi Selesai
```php
// Status: 'lengkap' atau 'tidak_lengkap'
$notificationService->notifyVerifikasiSelesai($permohonan, 'lengkap');
```

#### Event: Jadwal Ditetapkan
```php
$notificationService->notifyJadwalDitetapkan($permohonan);
```

## 📊 Event yang Tersedia

| Event | Method | Target Penerima | Kapan Dipanggil |
|-------|--------|-----------------|-----------------|
| `submitted` | `notifyPermohonanSubmitted()` | Admin + Tim Fedora | Saat pemohon submit dokumen persyaratan |
| `verifikasi_selesai` | `notifyVerifikasiSelesai()` | Pemohon + Admin | Saat verifikator selesai verifikasi |
| `jadwal_ditetapkan` | `notifyJadwalDitetapkan()` | Pemohon + Tim Fedora | Saat Kaban tetapkan jadwal |
| `hasil_dibuat` | *(belum diimplementasi)* | Pemohon + Admin | Saat hasil fasilitasi selesai dibuat |

## 👥 Target Penerima

### 1. **Admin**
- Role: `admin_peran`, `kaban`, `superadmin`
- Mendapat: Notifikasi database + WhatsApp
- Use case: Monitoring semua permohonan

### 2. **Tim Fedora**
Tim yang di-assign ke kabupaten/kota tertentu melalui `UserKabkotaAssignment`:
- Role: `verifikator`, `fasilitator`
- Filter: 
  - `kabupaten_kota_id` sesuai permohonan
  - `tahun` sesuai permohonan
  - `is_active = true`
  - `jenis_dokumen_id` null atau sesuai permohonan
- Mendapat: Notifikasi database + WhatsApp

### 3. **Pemohon**
- Role: `pemohon`
- User yang membuat permohonan (`permohonan.user_id`)
- Mendapat: Notifikasi database + WhatsApp

## 📱 Format Pesan WhatsApp

### Event: Permohonan Disubmit
```
📋 *Notifikasi Permohonan Fasilitasi*

Halo *Nama User*,

Permohonan fasilitasi/evaluasi dokumen baru telah diajukan:

🏛️ Kabupaten/Kota: *Halmahera Utara*
📄 Jenis Dokumen: *RKPD*
📅 Tahun: *2026*

Status: *Menunggu Verifikasi*

Silakan login ke sistem untuk melakukan verifikasi dokumen.

_*SI-FEDORA*_
_si-fedora.malutprov.go.id_
```

### Event: Verifikasi Selesai (Lengkap)
```
📋 *Notifikasi Permohonan Fasilitasi*

Halo *Nama User*,

Verifikasi dokumen permohonan Anda telah selesai:

🏛️ Kabupaten/Kota: *Halmahera Utara*
📄 Jenis Dokumen: *RKPD*
📅 Tahun: *2026*

Status Verifikasi: *Lengkap ✅*

Permohonan Anda akan segera diproses ke tahap berikutnya.

_*SI-FEDORA*_
_si-fedora.malutprov.go.id_
```

## 💻 Implementasi Saat Ini

### PermohonanController::submit()

**SEBELUM:**
```php
// Manual notifikasi ke verifikator
foreach ($verifikators as $assignment) {
    if ($assignment->user && $assignment->user->hasRole('verifikator')) {
        Notifikasi::create([...]);
    }
}

// Manual notifikasi ke admin
foreach ($admins as $admin) {
    Notifikasi::create([...]);
}
```

**SESUDAH:**
```php
// Otomatis kirim ke Admin + Tim Fedora (database + WA)
$notificationService = app(PermohonanNotificationService::class);
$notificationService->notifyPermohonanSubmitted($permohonan);
```

## 🔧 Menambahkan Event Baru

### 1. Tambahkan Method Public di Service

```php
public function notifyHasilDibuat(Permohonan $permohonan)
{
    try {
        $permohonan->load(['kabupatenKota', 'jenisDokumen', 'hasilFasilitasi']);

        // Kirim ke pemohon
        $this->notifyPemohon($permohonan, 'hasil_dibuat');

        // Kirim ke admin
        $this->notifyAdmins($permohonan, 'hasil_dibuat');

        Log::info('Hasil dibuat notifications sent successfully', [
            'permohonan_id' => $permohonan->id,
        ]);
    } catch (\Exception $e) {
        Log::error('Error sending hasil notifications: ' . $e->getMessage());
    }
}
```

### 2. Tambahkan Case di getNotificationData()

```php
case 'hasil_dibuat':
    $data['title'] = 'Hasil Fasilitasi Tersedia';
    $data['message'] = "Hasil fasilitasi untuk permohonan {$kabkota} telah dibuat dan dapat diunduh.";
    $data['type'] = 'success';
    break;
```

### 3. Tambahkan Case di getWhatsAppMessage()

```php
case 'hasil_dibuat':
    $message .= "Hasil fasilitasi/evaluasi telah tersedia:\n\n";
    $message .= "🏛️ Kabupaten/Kota: *{$kabkota}*\n";
    $message .= "📄 Jenis Dokumen: *{$jenisDokumen}*\n";
    $message .= "📅 Tahun: *{$tahun}*\n\n";
    $message .= "Silakan login ke sistem untuk mengunduh hasil fasilitasi.\n\n";
    break;
```

### 4. Panggil di Controller yang Sesuai

```php
// Di HasilFasilitasiController::store() atau submit()
$notificationService = app(PermohonanNotificationService::class);
$notificationService->notifyHasilDibuat($permohonan);
```

## 📊 Logging

Service ini melakukan logging otomatis untuk:
- ✅ Notifikasi berhasil dikirim
- ❌ Error saat mengirim notifikasi
- 📊 Total penerima bulk WhatsApp

Lokasi log: `storage/logs/laravel.log`

```bash
# Monitor log real-time
tail -f storage/logs/laravel.log | grep "notification"
```

## ⚠️ Error Handling

- **Non-blocking**: Jika gagal kirim notifikasi, tidak memblokir proses utama
- **Logging lengkap**: Semua error tercatat dengan trace
- **Graceful degradation**: Jika WhatsApp gagal, notifikasi database tetap tersimpan

## 🧪 Testing

### Manual Testing

1. **Login sebagai pemohon**
2. **Buat permohonan baru dan submit dokumen**
3. **Cek:**
   - [ ] Notifikasi database masuk ke admin & tim fedora
   - [ ] WhatsApp terkirim ke semua penerima yang punya no_hp
   - [ ] Log mencatat pengiriman berhasil

### Check Recipients

```php
// Cek siapa saja yang akan menerima notifikasi
$permohonan = Permohonan::find(1);

// Admin
$admins = User::role(['admin_peran', 'kaban', 'superadmin'])->get();

// Tim Fedora
$timFedora = UserKabkotaAssignment::where('kabupaten_kota_id', $permohonan->kab_kota_id)
    ->where('tahun', $permohonan->tahun)
    ->where('is_active', true)
    ->with('user')
    ->get();
```

## 🎯 Roadmap

- [x] Event: Permohonan Disubmit
- [ ] Event: Verifikasi Selesai
- [ ] Event: Jadwal Ditetapkan
- [ ] Event: Hasil Fasilitasi Dibuat
- [ ] Event: Perpanjangan Waktu
- [ ] Event: Tindak Lanjut
- [ ] Event: Penetapan Perda
- [ ] Queue support untuk bulk WhatsApp
- [ ] Template management untuk pesan
- [ ] Notifikasi preference per user

## 📚 Dependencies

- **FonteService**: Untuk mengirim WhatsApp
- **Notifikasi Model**: Untuk menyimpan notifikasi database
- **UserKabkotaAssignment**: Untuk mendapatkan tim fedora yang di-assign

---

**Last Updated:** April 4, 2026  
**Version:** 1.0.0  
**Author:** SI-FEDORA Development Team
