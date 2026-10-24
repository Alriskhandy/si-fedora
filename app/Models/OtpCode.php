<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * Kode OTP verifikasi nomor WhatsApp.
 */
class OtpCode extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'code',
        'expired_at',
        'used_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate OTP baru
     *
     * @return array ['record' => OtpCode, 'code' => string]
     */
    public static function generate(string $phone, ?int $userId = null, int $length = 6, int $expiryMinutes = 5): array
    {
        // Hapus OTP lama yang belum digunakan untuk phone/user ini
        static::where('phone', $phone)
            ->whereNull('used_at')
            ->delete();

        // Generate OTP code
        $otp = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        // Simpan dengan hash
        $record = static::create([
            'user_id' => $userId,
            'phone' => $phone,
            'code' => Hash::make($otp),
            'expired_at' => Carbon::now()->addMinutes($expiryMinutes),
        ]);

        return [
            'record' => $record,
            'code' => $otp,
        ];
    }

    /**
     * Verifikasi OTP
     */
    public static function verify(string $phone, string $code): bool
    {
        $otpRecord = static::where('phone', $phone)
            ->whereNull('used_at')
            ->where('expired_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (! $otpRecord) {
            return false;
        }

        if (! Hash::check($code, $otpRecord->code)) {
            return false;
        }

        // Tandai sebagai used
        $otpRecord->update(['used_at' => Carbon::now()]);

        return true;
    }

    /**
     * Cek apakah OTP sudah expired
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expired_at);
    }

    /**
     * Cek apakah OTP sudah digunakan
     */
    public function isUsed(): bool
    {
        return ! is_null($this->used_at);
    }

    /**
     * Scope untuk OTP yang masih valid
     */
    public function scopeValid(Builder $query)
    {
        return $query->whereNull('used_at')
            ->where('expired_at', '>', Carbon::now());
    }

    /**
     * Hapus OTP yang sudah expired
     */
    public static function cleanupExpired(): int
    {
        return static::where('expired_at', '<', Carbon::now())
            ->orWhereNotNull('used_at')
            ->delete();
    }
}
