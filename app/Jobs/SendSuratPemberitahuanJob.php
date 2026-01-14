<?php

namespace App\Jobs;

use App\Models\SuratPemberitahuan;
use App\Notifications\SuratPemberitahuanNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSuratPemberitahuanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $suratPemberitahuan;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(SuratPemberitahuan $suratPemberitahuan)
    {
        $this->suratPemberitahuan = $suratPemberitahuan;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SendSuratPemberitahuanJob started', [
            'surat_id' => $this->suratPemberitahuan->id
        ]);

        // Get all users from the kabupaten/kota with phone numbers
        $users = $this->suratPemberitahuan->kabupatenKota
            ->users()
            ->whereNotNull('phone')
            ->get();

        if ($users->isEmpty()) {
            Log::warning('No users with phone numbers found', [
                'surat_id' => $this->suratPemberitahuan->id,
                'kabupaten_kota_id' => $this->suratPemberitahuan->kabupaten_kota_id
            ]);
            return;
        }

        $sentCount = 0;
        $failedCount = 0;

        // Send notification to each user
        foreach ($users as $user) {
            try {
                $user->notify(new SuratPemberitahuanNotification($this->suratPemberitahuan));
                $sentCount++;

                Log::info('Notification queued for user', [
                    'user_id' => $user->id,
                    'phone' => $user->phone
                ]);
            } catch (\Exception $e) {
                $failedCount++;

                Log::error('Failed to queue notification', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('SendSuratPemberitahuanJob completed', [
            'surat_id' => $this->suratPemberitahuan->id,
            'total_users' => $users->count(),
            'sent' => $sentCount,
            'failed' => $failedCount
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendSuratPemberitahuanJob failed', [
            'surat_id' => $this->suratPemberitahuan->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
