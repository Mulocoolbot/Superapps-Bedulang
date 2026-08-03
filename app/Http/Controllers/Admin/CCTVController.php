<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Services\CctvService;

class CCTVController extends Controller
{
    public function index()
    {
        $cctvs = CctvService::getLiveCctvs();

        return Inertia::render(
            'Admin/CCTV',
            [
                'cctvs' => $cctvs
            ]
        );
    }
}


