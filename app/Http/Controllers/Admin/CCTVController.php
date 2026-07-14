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
                'nama' => 'Bundaran Koba',
                'lokasi' => 'Koba',
                'status' => 'Online',
                'latitude' => -2.4876,
                'longitude' => 106.1088,
                'stream' => '',
            ],

            [
                'id' => 2,
                'nama' => 'Pasar Modern',
                'lokasi' => 'Koba',
                'status' => 'Offline',
                'latitude' => -2.4895,
                'longitude' => 106.1105,
                'stream' => '',
            ],

            [
                'id' => 3,
                'nama' => 'Simpang Namang',
                'lokasi' => 'Namang',
                'status' => 'Online',
                'latitude' => -2.3590,
                'longitude' => 106.1805,
                'stream' => '',
            ],

        ];

        return Inertia::render('Admin/CCTV', [
            'cctvs' => $cctvs,
        ]);
    }
}