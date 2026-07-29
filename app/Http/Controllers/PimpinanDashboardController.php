<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\BloodStock;
use App\Services\MbgService;
use App\Services\PerindagService;
use App\Services\PertanianService;
use App\Services\KepegawaianService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PimpinanDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Complaint Stats
        $baseQuery = Complaint::query();
        $complaintStats = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'total' => (clone $baseQuery)->count(),
        ];

        // 2. Harga Pasar (Perindag)
        $perindagService = app(PerindagService::class);
        $perindagData = $perindagService->getDashboardData($request);
        $pasarAvgToday = 0;
        $pasarAvgYesterday = 0;
        $pasarChange = 0;
        if (!empty($perindagData['price_trends'])) {
            $sumToday = 0;
            $sumYesterday = 0;
            $count = count($perindagData['price_trends']);
            foreach ($perindagData['price_trends'] as $item) {
                $sumToday += $item['raw_price_ii'] ?? 0;
                $sumYesterday += $item['raw_price_i'] ?? 0;
            }
            $pasarAvgToday = $count > 0 ? $sumToday / $count : 0;
            $pasarAvgYesterday = $count > 0 ? $sumYesterday / $count : 0;
            if ($pasarAvgYesterday > 0) {
                $pasarChange = (($pasarAvgToday - $pasarAvgYesterday) / $pasarAvgYesterday) * 100;
            }
        }

        // 3. Harga Sawit (Pertanian)
        $pertanianService = app(PertanianService::class);
        $pertanianData = $pertanianService->getDashboardData($request);
        $sawitAvgToday = 0;
        $sawitAvgYesterday = 0;
        $sawitChange = 0;
        if (!empty($pertanianData['district_prices'])) {
            $sumToday = 0;
            $sumYesterday = 0;
            $count = count($pertanianData['district_prices']);
            foreach ($pertanianData['district_prices'] as $item) {
                $sumToday += $item['today'] ?? 0;
                $sumYesterday += $item['yesterday'] ?? 0;
            }
            $sawitAvgToday = $count > 0 ? $sumToday / $count : 0;
            $sawitAvgYesterday = $count > 0 ? $sumYesterday / $count : 0;
            if ($sawitAvgYesterday > 0) {
                $sawitChange = (($sawitAvgToday - $sawitAvgYesterday) / $sawitAvgYesterday) * 100;
            }
        }

        // 4. Kepegawaian
        $kepegawaianService = app(KepegawaianService::class);
        $kepegawaianData = $kepegawaianService->getDashboardData($request);
        $totalPegawai = $kepegawaianData['metadata']['grand_total'] ?? 0;
        $totalUpd = $kepegawaianData['metadata']['total_upd'] ?? 0;

        // 5. Makanan Bergizi Gratis (MBG)
        $mbgService = app(MbgService::class);
        $mbgData = $mbgService->getDashboardData($request);
        $totalMbg = $mbgData['grand_total_penerima'] ?? 0;

        // 6. UTD Blood Stock
        $totalBloodBags = BloodStock::sum('amount');
        $lastUpdateBlood = BloodStock::max('updated_at');

        $recap = [
            'pasar' => [
                'avg_today' => $pasarAvgToday,
                'avg_yesterday' => $pasarAvgYesterday,
                'change_percentage' => $pasarChange,
            ],
            'sawit' => [
                'avg_today' => $sawitAvgToday,
                'avg_yesterday' => $sawitAvgYesterday,
                'change_percentage' => $sawitChange,
            ],
            'kepegawaian' => [
                'total_pegawai' => $totalPegawai,
                'total_upd' => $totalUpd,
            ],
            'mbg' => [
                'total_penerima' => $totalMbg,
            ],
            'utd' => [
                'total_bags' => $totalBloodBags,
                'last_update' => $lastUpdateBlood,
            ],
            'kdmp' => [
                'total' => 1248,
            ],
        ];

        $cctvs = [
            [
                'id' => 1,
                'name' => 'GERBANG KOBA',
                'ip' => '157.10.109.241',
                'channel' => '101',
                'stream' => 'http://157.10.109.241:8888/gerbang_koba/index.m3u8',
                'lat' => -2.4738699,
                'lng' => 106.3742131,
            ],
            [
                'id' => 2,
                'name' => 'ALUN ALUN KOBA',
                'ip' => '157.10.109.241',
                'channel' => '102',
                'stream' => 'http://157.10.109.241:8888/alunalun_koba/index.m3u8',
                'lat' => -2.5085485,
                'lng' => 106.4138998,
            ],
            [
                'id' => 3,
                'name' => 'BUNDARAN KOBA',
                'ip' => '157.10.109.241',
                'channel' => '103',
                'stream' => 'http://157.10.109.241:8888/bundaran_koba/index.m3u8',
                'lat' => -2.4898301,
                'lng' => 106.4086202,
            ],
            [
                'id' => 4,
                'name' => 'SIMPANG NAMANG',
                'ip' => '157.10.109.241',
                'channel' => '104',
                'stream' => 'http://157.10.109.241:8888/simpang_namang/index.m3u8',
                'lat' => -2.3218823,
                'lng' => 106.1877463,
            ],
            [
                'id' => 5,
                'name' => 'SIMPANG PANGKALAN BARU',
                'ip' => '157.10.109.241',
                'channel' => '105',
                'stream' => 'http://157.10.109.241:8888/simpang_pangkalan_baru/index.m3u8',
                'lat' => -2.1643530,
                'lng' => 106.1345648,
            ]
        ];

        return Inertia::render('Pimpinan/Dashboard', [
            'recap' => $recap,
            'complaintStats' => $complaintStats,
            'userDomain' => $user?->service_slug,
            'cctvs' => $cctvs,
        ]);
    }

    public function pad(Request $request)
    {
        return Inertia::render('Pimpinan/ExecutiveDashboardPAD');
    }
}
