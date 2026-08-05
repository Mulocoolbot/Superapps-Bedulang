<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CctvService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->isPimpinan()) {
            return redirect()->route('pimpinan.dashboard');
        }

        if ($user && $user->kdmp_role === 'admin_kecamatan') {
            return redirect()->route('admin.kdmp.kecamatan.dashboard');
        }

        if ($user && $user->kdmp_role === 'admin_kmp') {
            return redirect()->route('admin.kdmp.kmp.dashboard');
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

        $cctvs = CctvService::getLiveCctvs();

        return Inertia::render('Admin/Dashboard', [
            'cctvs' => $cctvs,
            'complaintStats' => $complaintStats,
            'userDomain' => $user?->service_slug,
        ]);
    }
}