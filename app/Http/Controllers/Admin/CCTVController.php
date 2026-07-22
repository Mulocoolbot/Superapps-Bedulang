<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class CCTVController extends Controller
{
    public function index()
    {
       $cctvs = [

    [
        'id'=>1,
        'name'=>'GERBANG KOBA',
        'ip'=>'157.10.109.241',
        'channel'=>'101',
        'stream'=>'http://localhost:8888/gerbang_koba/index.m3u8',
        'lat'=>-2.123,
        'lng'=>106.123,
    ],


        ];

        return Inertia::render(
            'Admin/CCTV',
            [
                'cctvs'=>$cctvs
            ]
        );
    }
}

