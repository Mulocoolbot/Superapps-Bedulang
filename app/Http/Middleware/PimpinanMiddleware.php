<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class PimpinanMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {

        if (!$request->user()) {

            return redirect('/login');

        }


        if (
            $request->user()->role !== 'pimpinan'
            &&
            $request->user()->role !== 'admin'
        ) {

            return redirect('/')
                ->with(
                    'error',
                    'Layanan Pengaduan Bedulang hanya dapat diakses oleh Pimpinan.'
                );

        }


        return $next($request);

    }

}