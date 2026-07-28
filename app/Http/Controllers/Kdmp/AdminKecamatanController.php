<?php

namespace App\Http\Controllers\Kdmp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kmp;
use App\Models\KmpEditRequest;
use App\Models\KmpHistoryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\KdmpApprovalNotification;

class AdminKecamatanController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        $kmpAdmins = User::where('kdmp_role', 'admin_kmp')
            ->where('kdmp_kecamatan', $user->kdmp_kecamatan)
            ->paginate(10)->withQueryString();

        return Inertia::render('Kdmp/Kecamatan/Dashboard', [
            'kmpAdmins' => $kmpAdmins,
            'kecamatan' => $user->kdmp_kecamatan,
        ]);
    }

    public function verifikasiKmp(Request $request)
    {
        $user = Auth::user();
        
        $query = Kmp::where('kecamatan', $user->kdmp_kecamatan)->with('user');

        if ($request->filled('status') && $request->status !== 'semua') {
            if (in_array($request->status, ['pending_verification', 'verified', 'rejected'])) {
                $query->where('status_pembentukan', $request->status);
            }
        }

        if ($request->filled('sort') && $request->sort === 'terlama') {
            $query->oldest();
        } else {
            $query->latest();
        }
        
        $kmps = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Kdmp/Kecamatan/VerifikasiKmp', [
            'kmps' => $kmps,
            'kecamatan' => $user->kdmp_kecamatan,
            'filters' => [
                'status' => $request->status ?? 'semua',
                'sort' => $request->sort ?? 'terbaru',
            ]
        ]);
    }

    public function verifikasiPerubahan(Request $request)
    {
        $user = Auth::user();
        
        $query = KmpEditRequest::whereHas('kmp', function ($q) use ($user) {
            $q->where('kecamatan', $user->kdmp_kecamatan);
        })->with(['kmp', 'user'])->where('status', 'pending');

        if ($request->filled('sort') && $request->sort === 'terlama') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $editRequests = $query->paginate(10)->withQueryString();

        return Inertia::render('Kdmp/Kecamatan/VerifikasiPerubahan', [
            'editRequests' => $editRequests,
            'kecamatan' => $user->kdmp_kecamatan,
            'filters' => [
                'sort' => $request->sort ?? 'terbaru',
            ]
        ]);
    }

    public function historyLog()
    {
        $user = auth()->user();
        $kecamatan = $user->kdmp_kecamatan;

        $kmpIds = Kmp::where('kecamatan', $kecamatan)->pluck('id');
        $logs = KmpHistoryLog::whereIn('kmp_id', $kmpIds)->with(['user', 'kmp'])->latest()->paginate(10)->withQueryString();

        return Inertia::render('Kdmp/Kecamatan/HistoryLog', [
            'kecamatan' => $kecamatan,
            'logs' => $logs,
        ]);
    }

    public function storeKmpAdmin(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'service_slug' => 'kdmp',
            'kdmp_role' => 'admin_kmp',
            'kdmp_kecamatan' => $user->kdmp_kecamatan,
            'kdmp_admin_status' => 'pending',
        ]);

        $adminKdmps = User::where('kdmp_role', 'admin_kdmp')->orWhere('role', 'super_admin')->get();
        Notification::send($adminKdmps, new KdmpApprovalNotification(
            "Akun Admin KMP baru ({$newUser->name}) dibuat oleh Admin Kecamatan {$user->kdmp_kecamatan} dan menunggu verifikasi.",
            "info"
        ));

        return redirect()->back()->with('success', 'Admin KMP berhasil ditambahkan dan menunggu verifikasi admin pusat.');
    }

    public function updateAdmin(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->back()->with('success', 'Admin KMP berhasil diperbarui.');
    }

    public function destroyAdmin(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'Admin KMP berhasil dihapus.');
    }

    public function verifyPembentukan(Kmp $kmp, Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
        ]);

        $kmp->status_pembentukan = $validated['action'] === 'verify' ? 'verified' : 'rejected';
        $kmp->save();

        KmpHistoryLog::create([
            'kmp_id' => $kmp->id,
            'user_id' => Auth::id(),
            'action' => 'verify_pembentukan',
            'description' => "Admin Kecamatan " . ($validated['action'] === 'verify' ? 'menyetujui' : 'menolak') . " pembentukan KMP.",
        ]);

        $adminKdmps = User::where('kdmp_role', 'admin_kdmp')->orWhere('role', 'super_admin')->get();
        $statusText = $validated['action'] === 'verify' ? 'disetujui' : 'ditolak';
        Notification::send($adminKdmps, new KdmpApprovalNotification(
            "Pembentukan KMP {$kmp->nama} telah {$statusText} oleh Admin Kecamatan.",
            $validated['action'] === 'verify' ? "success" : "warning"
        ));

        return redirect()->back()->with('success', 'Status pembentukan KMP berhasil diubah.');
    }
    
    public function verifySertifikat(Kmp $kmp, Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
        ]);

        $kmp->status_sertifikat = $validated['action'] === 'verify' ? 'verified' : 'rejected';
        $kmp->save();

        KmpHistoryLog::create([
            'kmp_id' => $kmp->id,
            'user_id' => Auth::id(),
            'action' => 'verify_sertifikat',
            'description' => "Admin Kecamatan " . ($validated['action'] === 'verify' ? 'menyetujui' : 'menolak') . " sertifikat KMP.",
        ]);

        $adminKdmps = User::where('kdmp_role', 'admin_kdmp')->orWhere('role', 'super_admin')->get();
        $statusText = $validated['action'] === 'verify' ? 'disetujui' : 'ditolak';
        Notification::send($adminKdmps, new KdmpApprovalNotification(
            "Sertifikat KMP {$kmp->nama} telah {$statusText} oleh Admin Kecamatan.",
            $validated['action'] === 'verify' ? "success" : "warning"
        ));

        return redirect()->back()->with('success', 'Status sertifikat KMP berhasil diubah.');
    }

    public function verifyEditRequest(KmpEditRequest $editRequest, Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'nullable|string|required_if:action,reject',
        ]);

        if ($validated['action'] === 'approve') {
            $kmp = $editRequest->kmp;
            $kmp->update($editRequest->requested_data);
            
            $editRequest->status = 'approved';
            $editRequest->save();

            KmpHistoryLog::create([
                'kmp_id' => $kmp->id,
                'user_id' => Auth::id(),
                'action' => 'approve_edit',
                'description' => "Admin Kecamatan menyetujui perubahan data KMP.",
            ]);
        } else {
            $editRequest->status = 'rejected';
            $editRequest->rejection_reason = $validated['rejection_reason'];
            $editRequest->save();

            KmpHistoryLog::create([
                'kmp_id' => $editRequest->kmp_id,
                'user_id' => Auth::id(),
                'action' => 'reject_edit',
                'description' => "Admin Kecamatan menolak perubahan data KMP: " . $validated['rejection_reason'],
            ]);
        }

        $adminKdmps = User::where('kdmp_role', 'admin_kdmp')->orWhere('role', 'super_admin')->get();
        $statusText = $validated['action'] === 'approve' ? 'disetujui' : 'ditolak';
        $kmpName = $editRequest->kmp->nama ?? 'Unknown';
        Notification::send($adminKdmps, new KdmpApprovalNotification(
            "Pengajuan perubahan data KMP {$kmpName} telah {$statusText} oleh Admin Kecamatan.",
            $validated['action'] === 'approve' ? "success" : "warning"
        ));

        return redirect()->back()->with('success', 'Status perubahan data KMP berhasil diubah.');
    }
}
