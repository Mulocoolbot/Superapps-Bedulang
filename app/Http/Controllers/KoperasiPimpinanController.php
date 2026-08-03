<?php

namespace App\Http\Controllers;

use App\Models\Koperasi;
use App\Models\KoperasiImportLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KoperasiPimpinanController extends Controller
{
    public function detail(Request $request)
    {
        $kecamatanDesaMap = [
            'Koba' => ['Berok', 'Arung Dalam', 'Padang Mulia', 'Simpang Perlang', 'Nibung', 'Terentang', 'Kurau', 'Kurau Barat', 'Penyak', 'Guntung'],
            'Pangkalan Baru' => ['Dul', 'Beluluk', 'Mesu', 'Mesu Timur', 'Air Mesu', 'Air Mesu Timur', 'Mangkol', 'Pedindang', 'Benteng', 'Batu Belubang', 'Tanjung Gunung', 'Jeruk'],
            'Sungai Selan' => ['Sungai Selan', 'Sungai Selan Hulu', 'Lampur', 'Simpang Tiga', 'Munggu', 'Sarang Mandi', 'Keretak', 'Keretak Atas', 'Melabun', 'Kemingking', 'Romadhon', 'Tanjung Pura'],
            'Simpang Katis' => ['Simpang Katis', 'Terak', 'Puak', 'Sungkap', 'Celuak', 'Teru', 'Beravas', 'Pinang Sebatang', 'Katis'],
            'Namang' => ['Namang', 'Kayu Besi', 'Belulik', 'Cambai', 'Jelutung', 'Baskara Bakti', 'Bukit Kijang'],
            'Lubuk Besar' => ['Lubuk Besar', 'Lubuk Lingkuk', 'Lubuk Pabrik', 'Perlang', 'Trubus', 'Kulur', 'Kulur Ilir', 'Batu Beriga'],
        ];

        $koperasis = Koperasi::select('id', 'nama_koperasi', 'kecamatan', 'desa_kelurahan', 'tahun')
            ->orderBy('tahun', 'asc')
            ->orderBy('kecamatan', 'asc')
            ->orderBy('nama_koperasi', 'asc')
            ->get();

        $yearsList = $koperasis->pluck('tahun')->unique()->sort()->values()->all();
        if (empty($yearsList)) {
            $yearsList = [(int)date('Y')];
        }

        $latestYear = max($yearsList);
        $latestCount = Koperasi::where('tahun', $latestYear)->count();

        return Inertia::render('Pimpinan/KoperasiDetail', [
            'totalCount' => $latestCount,
            'latestYear' => $latestYear,
            'koperasis' => $koperasis,
            'kecamatanDesaMap' => $kecamatanDesaMap,
            'yearsList' => $yearsList,
        ]);
    }
}
