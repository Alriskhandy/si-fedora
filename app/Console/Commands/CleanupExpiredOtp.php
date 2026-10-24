<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use Illuminate\Console\Command;

/**
 * Menghapus kode OTP yang kedaluwarsa atau sudah dipakai (OtpCode::cleanupExpired).
 *
 * Dijadwalkan setiap jam di bootstrap/app.php.
 */
class CleanupExpiredOtp extends Command
{
    /**
     * Nama dan argumen command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * Deskripsi yang tampil di `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Hapus kode OTP yang kedaluwarsa atau sudah dipakai';

    /**
     * Hapus OTP kedaluwarsa/terpakai dan laporkan jumlahnya.
     */
    public function handle()
    {
        $this->info('Cleaning up expired OTP codes...');

        $deletedCount = OtpCode::cleanupExpired();

        $this->info("Successfully deleted {$deletedCount} expired/used OTP codes.");

        return Command::SUCCESS;
    }
}
