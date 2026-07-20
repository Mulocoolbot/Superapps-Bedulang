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
        'name'=>'CCTV 1',
        'ip'=>'157.10.109.242',
        'stream'=>'http://157.10.109.242',
        'lat'=>-2.123,
        'lng'=>106.123,
    ],

    [
        'id'=>2,
        'name'=>'CCTV 2',
        'ip'=>'157.10.109.243',
        'stream'=>'http://157.10.109.243',
        'lat'=>-2.124,
        'lng'=>106.125,
    ],

    [
        'id'=>3,
        'name'=>'CCTV 3',
        'ip'=>'157.10.109.244',
        'stream'=>'http://157.10.109.244',
        'lat'=>-2.126,
        'lng'=>106.127,
    ],

    [
        'id'=>4,
        'name'=>'CCTV 4',
        'ip'=>'157.10.109.245',
        'stream'=>'http://157.10.109.245',
        'lat'=>-2.129,
        'lng'=>106.130,
    ],

    [
        'id'=>5,
        'name'=>'CCTV 5',
        'ip'=>'157.10.109.246',
        'stream'=>'http://157.10.109.246',
        'lat'=>-2.132,
        'lng'=>106.132,
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