<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip check if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Routes that don't require phone verification
        $allowedRoutes = [
            'phone.verify',
            'phone.verify.send',
            'phone.verify.otp.form',
            'phone.verify.otp',
            'phone.verify.resend',
            'logout',
            'profile.edit',
            'profile.update',
        ];

        // Check if current route is in allowed routes
        $currentRoute = $request->route()->getName();
        if (in_array($currentRoute, $allowedRoutes)) {
            return $next($request);
        }

        // Check if phone is verified
        if (!$user->no_hp || !$user->phone_verified_at) {
            return redirect()->route('phone.verify')
                ->with('error', 'Anda harus memverifikasi nomor WhatsApp terlebih dahulu.');
        }

        return $next($request);
    }
}
