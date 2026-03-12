<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OtpCode;

class CleanupExpiredOtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired and used OTP codes from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up expired OTP codes...');
        
        $deletedCount = OtpCode::cleanupExpired();
        
        $this->info("Successfully deleted {$deletedCount} expired/used OTP codes.");
        
        return Command::SUCCESS;
    }
}
