<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CCTVController extends Controller
{
    public function index()
    {
        $cctvs = [

            [
                'id' => 1,
                'name' => 'Bundaran Koba',
                'location' => 'Koba',
                'status' => 'Online',
                'lat' => -2.4876,
                'lng' => 106.1088,
                'stream' => '',
            ],

            [
                'id' => 2,
                'name' => 'Pasar Modern',
                'location' => 'Koba',
                'status' => 'Offline',
                'lat' => -2.4895,
                'lng' => 106.1105,
                'stream' => '',
            ],

            [
                'id' => 3,
                'name' => 'Simpang Namang',
                'location' => 'Namang',
                'status' => 'Online',
                'lat' => -2.3590,
                'lng' => 106.1805,
                'stream' => '',
            ],

        ];

        return Inertia::render('Admin/CCTV', [
            'cctvs' => $cctvs,
        ]);
    }
}