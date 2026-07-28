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

        if ($request->filled('role')) {
            $query->where('kdmp_role', $request->role);
        }
        
        if ($request->filled('kecamatan')) {
            $query->where('kdmp_kecamatan', $request->kecamatan);
        }

        if ($request->filled('status')) {
            $query->where('kdmp_admin_status', $request->status);
        }

        $admins = $query->paginate(10)->withQueryString();

        return Inertia::render('Kdmp/Admin/Dashboard', [
            'admins' => $admins,
            'filters' => $request->only(['role', 'kecamatan', 'status']),
        ]);
    }

    public function historyLog(Request $request)
    {
        $query = KmpHistoryLog::with(['user', 'kmp']);

        if ($request->filled('kecamatan')) {
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

    public function updateAdmin(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'kdmp_role' => 'required|in:admin_kecamatan,admin_kmp',
            'kdmp_kecamatan' => 'nullable|string|required_if:kdmp_role,admin_kecamatan',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->kdmp_role = $validated['kdmp_role'];
        $user->kdmp_kecamatan = $validated['kdmp_kecamatan'] ?? null;
        $user->save();

        return redirect()->back()->with('success', 'Admin KDMP berhasil diperbarui.');
    }

    public function destroyAdmin(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'Admin KDMP berhasil dihapus.');
    }

    public function verifyAdminKmp(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
        ]);
        
        $user->kdmp_admin_status = $validated['action'] === 'verify' ? 'approved' : 'rejected';
        $user->save();
        
        return redirect()->back()->with('success', 'Status Admin KMP berhasil diubah.');
    }
}
