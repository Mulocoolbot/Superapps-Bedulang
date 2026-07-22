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
        'stream'=>'http://127.0.0.1:8888/gerbang_koba/index.m3u8',
        'lat'=>-2.4738699,
        'lng'=>106.3742131,
    ],

    [
        'id'=>2,
        'name'=>'ALUN ALUN KOBA',
        'ip'=>'157.10.109.241',
        'stream'=>'http://127.0.0.1:8888/alunalun_koba/index.m3u8',
        'lat'=>-2.5085485,
        'lng'=>106.4138998,
    ],

    [
        'id'=>3,
        'name'=>'BUNDARAN KOBA',
        'ip'=>'157.10.109.241',
        'stream'=>'http://127.0.0.1:8888/bundaran_koba/index.m3u8',
        'lat'=>-2.4898301,
        'lng'=>106.4086202,
    ],

    [
        'id'=>4,
        'name'=>'SIMPANG NAMANG',
        'ip'=>'157.10.109.241',
        'stream'=>'http://127.0.0.1:8888/simpang_namang/index.m3u8',
        'lat'=>-2.3218823,
        'lng'=>106.1877463,
    ],

    [
        'id'=>5,
        'name'=>'SIMPANG PANGKALAN BARU',
        'ip'=>'157.10.109.241',
        'stream'=>'http://127.0.0.1:8888/simpang_pangkalan_baru/index.m3u8',
        'lat'=>-2.1643530,
        'lng'=>106.1345648,
    ]

        ];

        return Inertia::render(
            'Admin/CCTV',
            [
                'cctvs'=>$cctvs
            ]
        );
    }
}

