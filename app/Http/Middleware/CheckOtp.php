<?php

namespace App\Http\Middleware;

use App\Models\Otp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOtp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       $user = auth()->user();

    if (!$user) {
        return redirect()->route('login.show');
    }

    $otpExists = Otp::where('email', $user->email)
        ->where('expires_at', '>', now())
        ->exists();

    if (!$otpExists) {
        return redirect()->route('forget-password.show');
    }

    return $next($request);
    }
}
