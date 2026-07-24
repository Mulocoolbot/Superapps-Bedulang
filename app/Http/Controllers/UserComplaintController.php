<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintProgressLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserComplaintController extends Controller
{
    /**
     * Display a listing of the logged-in user's complaints.
     */
    public function index(Request $request): Response
    {
        $complaints = Complaint::query()
            ->where('user_id', $request->user()->id)
            ->with(['progressLogs.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->onEachSide(100);

        return Inertia::render('Front/MyComplaints', [
            'complaints' => $complaints,
        ]);
    }

    /**
     * Cancel a complaint submitted by the user.
     */
    public function cancel(Request $request, Complaint $complaint): RedirectResponse
    {
        // Ensure user is the owner of the complaint
        if ($complaint->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak untuk membatalkan pengaduan ini.');
        }

        // Only allow cancellation if status is strictly pending (masih menunggu)
        if ($complaint->status !== 'pending') {
            return redirect()->back()->withErrors([
                'status' => 'Pengaduan hanya dapat dibatalkan ketika statusnya masih Menunggu.',
            ]);
        }

        $oldStatus = $complaint->status;

        // Update status to cancelled
        $complaint->update([
            'status' => 'cancelled',
        ]);

        // Record progress log
        ComplaintProgressLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => $request->user()->id,
            'status' => 'cancelled',
            'notes' => 'Pengaduan ini telah dibatalkan oleh pengaju/pelapor.',
        ]);

        // Send confirmation notification to user
        $request->user()->notify(new \App\Notifications\ComplaintStatusUpdatedNotification(
            $complaint,
            'cancelled',
            'Pengaduan ini telah Anda batalkan.'
        ));

        return redirect()->back()->with('success', 'Pengaduan Anda berhasil dibatalkan.');
    }
}
