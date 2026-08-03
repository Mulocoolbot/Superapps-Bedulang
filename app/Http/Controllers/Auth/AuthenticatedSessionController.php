<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        $ipKey = 'login_ip_block:' . $request->ip();
        $isBlocked = \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($ipKey, LoginRequest::MAX_IP_ATTEMPTS);
        $ipBlockedMessage = null;

        if ($isBlocked) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($ipKey);
            $hours = floor($seconds / 3600);
            $minutes = ceil(($seconds % 3600) / 60);
            $timeText = $hours > 0 ? "{$hours} jam {$minutes} menit" : "{$seconds} detik";
            $ipBlockedMessage = "Alamat IP Anda ({$request->ip()}) diblokir dari melakukan login selama 24 jam karena 3 kali gagal verifikasi/login. Silakan coba lagi dalam {$timeText}.";
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'ipBlockedMessage' => $ipBlockedMessage,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
