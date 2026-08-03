<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->isPimpinan()) {
            return redirect()->route('pimpinan.dashboard');
        }

        if ($user && !$user->isSuperAdmin() && $user->isAdminKoperasi()) {
            return redirect()->route('admin.koperasi.index');
        }

        // Base scoped query matching admin domain logic
        $baseQuery = Complaint::query();
        if ($user && !$user->isSuperAdmin() && !empty($user->service_slug)) {
            $baseQuery->where('service_slug', $user->service_slug);
        }

        $complaintStats = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
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

        return Inertia::render('Admin/Dashboard', [
            'cctvs' => $cctvs,
            'complaintStats' => $complaintStats,
            'userDomain' => $user?->service_slug,
        ]);
    }
}