<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->isAdmin() && ! $user->isPimpinan() && ! str_starts_with($user->role ?? '', 'admin_') && empty($user->service_slug) && empty($user->kdmp_role)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}