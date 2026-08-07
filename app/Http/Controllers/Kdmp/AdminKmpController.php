<?php

namespace App\Http\Controllers\Kdmp;

use App\Http\Controllers\Controller;
use App\Models\Kmp;
use App\Models\KmpEditRequest;
use App\Models\KmpHistoryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AdminKmpController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $kmp = Kmp::where('user_id', $user->id)->first();
        
        $pendingEdits = null;
        if ($kmp) {
            $pendingEdits = KmpEditRequest::where('kmp_id', $kmp->id)->where('status', 'pending')->first();
        }

        return Inertia::render('Kdmp/Kmp/Dashboard', [
            'kmp' => $kmp,
            'pendingEdits' => $pendingEdits,
        ]);
    }

    public function historyLog()
    {
        $kmp = auth()->user()->kmp;
        $logs = [];
        if ($kmp) {
            $logs = KmpHistoryLog::where('kmp_id', $kmp->id)->with(['user'])->latest()->paginate(10)->withQueryString();
        }

        return Inertia::render('Kdmp/Kmp/HistoryLog', [
            'kmp' => $kmp,
            'logs' => $logs,
        ]);
    }

    public function requestEdit(Request $request)
    {
        $user = Auth::user();
        $kmp = Kmp::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'desa_kelurahan' => 'required|string|max:255',
            'jenis' => 'required|in:Desa,Kelurahan',
            'nama_koperasi' => 'required|string|max:255',
            'nomor_induk' => 'nullable|string|max:255',
            'no_ahu' => 'nullable|string|max:255',
            'tanggal_ahu' => 'nullable|date',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'status_operasional' => 'nullable|boolean',
        ]);

        KmpEditRequest::create([
            'kmp_id' => $kmp->id,
            'user_id' => $user->id,
            'requested_data' => $validated,
            'status' => 'pending',
        ]);

        KmpHistoryLog::create([
            'kmp_id' => $kmp->id,
            'user_id' => $user->id,
            'action' => 'request_edit',
            'description' => 'Admin KMP mengajukan perubahan data.',
        ]);

        return redirect()->back()->with('success', 'Pengajuan perubahan data berhasil dikirim.');
    }

    public function uploadSertifikat(Request $request)
    {
        $user = Auth::user();
        $kmp = Kmp::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'sertifikat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('sertifikat')->store('kmp_sertifikat', 'public');

        $kmp->file_sertifikat = $path;
        $kmp->status_sertifikat = 'pending';
        $kmp->save();

        KmpHistoryLog::create([
            'kmp_id' => $kmp->id,
            'user_id' => $user->id,
            'action' => 'upload_sertifikat',
            'description' => 'Admin KMP mengunggah dokumen sertifikat.',
        ]);

        return redirect()->back()->with('success', 'Sertifikat berhasil diunggah dan menunggu verifikasi.');
    }

    public function uploadPelatihan(Request $request)
    {
        $user = Auth::user();
        $kmp = Kmp::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'pelatihan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('pelatihan')->store('kmp_pelatihan', 'public');

        $kmp->file_pelatihan = $path;
        $kmp->status_pelatihan = 'pending';
        $kmp->save();

        KmpHistoryLog::create([
            'kmp_id' => $kmp->id,
            'user_id' => $user->id,
            'action' => 'upload_pelatihan',
            'description' => 'Admin KMP mengunggah bukti pelaksanaan pelatihan.',
        ]);

        return redirect()->back()->with('success', 'Bukti pelatihan berhasil diunggah dan menunggu verifikasi.');
    }

    public function updateManagement(Request $request)
    {
        $user = Auth::user();
        $kmp = Kmp::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'jumlah_anggota' => 'nullable|integer|min:0',
            'jumlah_pengawas' => 'nullable|integer|min:0',
            'jumlah_pengurus' => 'nullable|integer|min:0',
            'ketua_nama' => 'nullable|string|max:255',
            'ketua_foto' => 'nullable|image|max:2048',
            'sekretaris_nama' => 'nullable|string|max:255',
            'sekretaris_foto' => 'nullable|image|max:2048',
            'bendahara_nama' => 'nullable|string|max:255',
            'bendahara_foto' => 'nullable|image|max:2048',
            'simpanan_pokok' => 'nullable|integer|min:0',
            'simpanan_wajib' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('ketua_foto')) {
            $validated['ketua_foto'] = $request->file('ketua_foto')->store('kmp_photos', 'public');
        } else {
            unset($validated['ketua_foto']);
        }

        if ($request->hasFile('sekretaris_foto')) {
            $validated['sekretaris_foto'] = $request->file('sekretaris_foto')->store('kmp_photos', 'public');
        } else {
            unset($validated['sekretaris_foto']);
        }

        if ($request->hasFile('bendahara_foto')) {
            $validated['bendahara_foto'] = $request->file('bendahara_foto')->store('kmp_photos', 'public');
        } else {
            unset($validated['bendahara_foto']);
        }

        $kmp->update($validated);

        KmpHistoryLog::create([
            'kmp_id' => $kmp->id,
            'user_id' => $user->id,
            'action' => 'update_management',
            'description' => 'Admin KMP memperbarui data pengurus koperasi.',
        ]);

        return redirect()->back()->with('success', 'Data struktur kepengurusan KMP berhasil diperbarui.');
    }
}
