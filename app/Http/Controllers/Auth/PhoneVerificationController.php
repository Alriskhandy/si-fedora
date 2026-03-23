<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\OtpCode;
use App\Services\FonteService;

class PhoneVerificationController extends Controller
{
    protected $fonteService;

    public function __construct(FonteService $fonteService)
    {
        $this->fonteService = $fonteService;
    }

    /**
     * Show the phone verification form
     */
    public function showPhoneForm()
    {
        $user = Auth::user();
        
        // Redirect if phone is already verified
        if ($user->no_hp && $user->phone_verified_at) {
            return redirect()->route('dashboard')
                ->with('info', 'Nomor WhatsApp Anda sudah terverifikasi.');
        }

        return view('auth.verify-phone', ['user' => $user]);
    }

    /**
     * Send OTP to WhatsApp
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'numeric', 'digits_between:9,13'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi',
            'phone.numeric' => 'Nomor WhatsApp harus berupa angka',
            'phone.digits_between' => 'Nomor WhatsApp harus antara 9-13 digit',
        ]);

        $user = Auth::user();
        $phone = $request->phone;
        
        // Pastikan nomor dimulai dengan 62
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . ltrim($phone, '0');
        }

        // Generate OTP dan simpan ke database
        $otpData = OtpCode::generate($phone, $user->id);
        $otp = $otpData['code'];
        
        // Simpan phone ke session untuk keperluan form OTP
        Session::put('otp_phone', $phone);

        // Send OTP via WhatsApp using Fonnte
        try {
            $result = $this->fonteService->sendOTP($phone, $otp);
            
            if (isset($result['success']) && $result['success'] === false) {
                Log::error("Failed to send OTP via Fonnte: " . ($result['error'] ?? 'Unknown error'));
                return back()->with('error', 'Gagal mengirim kode OTP. Silakan coba lagi.');
            }
            
            Log::info("OTP sent successfully to {$phone}");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP: " . $e->getMessage());
            return back()->with('error', 'Gagal mengirim kode OTP. Silakan coba lagi.');
        }

        // Mask phone for display
        $maskedPhone = '+62 ' . substr($phone, 2, 3) . '***' . substr($phone, -3);

        return redirect()->route('phone.verify.otp.form')
            ->with('phone_masked', $maskedPhone)
            ->with('status', 'Kode OTP telah dikirim ke WhatsApp Anda');
    }

    /**
     * Show the OTP verification form
     */
    public function showOtpForm()
    {
        // Check if phone session exists
        if (!Session::has('otp_phone')) {
            return redirect()->route('phone.verify')
                ->with('error', 'Silakan masukkan nomor WhatsApp terlebih dahulu');
        }

        $phone = Session::get('otp_phone');

        // Check if there's a valid OTP for this phone
        $validOtp = OtpCode::where('phone', $phone)
            ->valid()
            ->exists();

        if (!$validOtp) {
            Session::forget('otp_phone');
            return redirect()->route('phone.verify')
                ->with('error', 'Kode OTP telah kadaluarsa. Silakan kirim ulang.');
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        // Validate OTP
        try {
            $request->validate([
                'otp' => ['required', 'numeric', 'digits:6'],
            ], [
                'otp.required' => 'Kode OTP wajib diisi',
                'otp.numeric' => 'Kode OTP harus berupa angka',
                'otp.digits' => 'Kode OTP harus 6 digit',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first()
                ], 422);
            }
            throw $e;
        }

        // Check if phone session exists
        if (!Session::has('otp_phone')) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi telah berakhir. Silakan kirim ulang kode OTP.'
                ], 400);
            }
            return back()->with('error', 'Sesi telah berakhir. Silakan kirim ulang kode OTP.');
        }

        $phone = Session::get('otp_phone');

        // Verify OTP using database
        if (!OtpCode::verify($phone, $request->otp)) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP yang Anda masukkan salah atau sudah kadaluarsa.'
                ], 422);
            }
            return back()->with('error', 'Kode OTP yang Anda masukkan salah atau sudah kadaluarsa. Silakan coba lagi.');
        }

        // Update user phone and verification status
        $user = User::find(Auth::id());
        $user->no_hp = $phone;
        $user->phone_verified_at = now();
        $user->save();

        // Clear phone session
        Session::forget('otp_phone');

        // Log successful verification
        Log::info("User {$user->id} successfully verified phone: {$phone}");

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Nomor WhatsApp berhasil diverifikasi!',
                'redirect' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard')
            ->with('status', 'Nomor WhatsApp Anda berhasil diverifikasi!');
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        // Check if phone session exists
        if (!Session::has('otp_phone')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan mulai dari awal.'
            ], 400);
        }

        $phone = Session::get('otp_phone');
        $user = Auth::user();

        // Generate OTP baru dan simpan ke database
        $otpData = OtpCode::generate($phone, $user->id);
        $otp = $otpData['code'];

        // Send OTP via WhatsApp using Fonnte
        try {
            $result = $this->fonteService->sendOTP($phone, $otp);
            
            if (isset($result['success']) && $result['success'] === false) {
                Log::error("Failed to resend OTP via Fonnte: " . ($result['error'] ?? 'Unknown error'));
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim kode OTP. Silakan coba lagi.'
                ], 500);
            }
            
            Log::info("OTP resent successfully to {$phone}");
            
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP baru telah dikirim'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to resend OTP: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim kode OTP. Silakan coba lagi.'
            ], 500);
        }
    }
}