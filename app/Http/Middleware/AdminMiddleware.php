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

        if (! in_array(auth()->user()->role, ['admin', 'super_admin', 'pimpinan', 'admin_koperasi', 'admin_kependudukan'], true) && ! in_array(auth()->user()->service_slug, ['koperasi', 'kependudukan'], true)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}