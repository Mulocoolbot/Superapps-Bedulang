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
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user) {
            if ($user->isSuperAdmin()) {
                return redirect()->intended('/admin');
            }
            if ($user->isPimpinan()) {
                return redirect()->intended('/pimpinan');
            }
            if ($user->isAdminKependudukan()) {
                return redirect()->intended('/admin/kependudukan');
            }
            if ($user->isAdminKoperasi()) {
                return redirect()->intended('/admin/koperasi');
            }
            if ($user->isKdmpKecamatanAdmin()) {
                return redirect()->intended('/admin/kdmp/kecamatan');
            }
            if ($user->isKdmpKmpAdmin()) {
                return redirect()->intended('/admin/kdmp/kmp');
            }
            if ($user->isKdmpAdmin()) {
                return redirect()->intended('/admin/kdmp');
            }
            if ($user->isAdmin()) {
                return redirect()->intended('/admin');
            }
        }

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
