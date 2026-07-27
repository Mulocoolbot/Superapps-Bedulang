<?php

namespace App\Http\Controllers\Kdmp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\KmpHistoryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class AdminKdmpController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = User::where('service_slug', 'kdmp')
            ->whereIn('kdmp_role', ['admin_kecamatan', 'admin_kmp']);

        if ($request->has('role') && $request->role !== '') {
            $query->where('kdmp_role', $request->role);
        }
        
        if ($request->has('kecamatan') && $request->kecamatan !== '') {
            $query->where('kdmp_kecamatan', $request->kecamatan);
        }

        $admins = $query->paginate(10)->withQueryString();

        return Inertia::render('Kdmp/Admin/Dashboard', [
            'admins' => $admins,
            'filters' => $request->only(['role', 'kecamatan']),
        ]);
    }

    public function historyLog(Request $request)
    {
        $query = KmpHistoryLog::with(['user', 'kmp']);

        if ($request->has('kecamatan') && $request->kecamatan !== '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('kdmp_kecamatan', $request->kecamatan);
            });
        }

        $logs = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Kdmp/Admin/HistoryLog', [
            'logs' => $logs,
            'filters' => $request->only(['kecamatan']),
        ]);
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'kdmp_role' => 'required|in:admin_kecamatan,admin_kmp',
            'kdmp_kecamatan' => 'nullable|string|required_if:kdmp_role,admin_kecamatan',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'service_slug' => 'kdmp',
            'kdmp_role' => $validated['kdmp_role'],
            'kdmp_kecamatan' => $validated['kdmp_kecamatan'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Admin KDMP berhasil ditambahkan.');
    }
}
