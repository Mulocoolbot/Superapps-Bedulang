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
                'stream' => 'http://localhost:8888/gerbang_koba/index.m3u8',
                'lat' => -2.123,
                'lng' => 106.123,
            ],
        ];

        return Inertia::render('Admin/Dashboard', [
            'cctvs' => $cctvs,
            'complaintStats' => $complaintStats,
            'userDomain' => $user?->service_slug,
        ]);
    }
}