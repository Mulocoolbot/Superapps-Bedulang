<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintProgressLog;
use App\Models\User;
use App\Notifications\ComplaintStatusUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserComplaintController extends Controller
{
    /**
     * Display a listing of the logged-in user's complaints.
     */
    public function index(Request $request): Response
    {
        $query = Complaint::query()
            ->where('user_id', $request->user()->id)
            ->with(['progressLogs.user'])
            ->orderBy('created_at', 'desc');

        if ($request->has('type') && !empty($request->type) && $request->type !== 'all') {
            if ($request->type === 'bedulang') {
                $query->where('service_slug', 'pengaduan-bedulang');
            } elseif ($request->type === 'layanan_info') {
                $query->where('service_slug', '!=', 'pengaduan-bedulang');
            }
        }

        $complaints = $query->paginate(10)
            ->onEachSide(100)
            ->withQueryString();

        return Inertia::render('Front/MyComplaints', [
            'complaints' => $complaints,
            'filters' => [
                'type' => $request->type ?? 'all',
            ],
        ]);
    }

    /**
     * Store complaint review / rating by pelapor.
     */
    public function storeReview(Request $request, Complaint $complaint): RedirectResponse
    {
        if ($complaint->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($complaint->status !== 'resolved') {
            return redirect()->back()->withErrors([
                'review' => 'Ulasan hanya dapat diberikan pada pengaduan yang sudah Selesai.',
            ]);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
        ]);

        $complaint->update([
            'rating' => $validated['rating'],
            'review' => $validated['review'],
        ]);

        $complaint->progressLogs()->create([
            'user_id' => $request->user()->id,
            'status' => 'resolved',
            'notes' => "Pelapor memberikan ulasan: Rating {$validated['rating']}/5. Keterangan: \"{$validated['review']}\"",
        ]);

        return redirect()->back()->with('success', 'Ulasan Anda berhasil dikirim.');
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

        public function createBedulang()
{
    $user = Auth::user();


    if (
        !$user->isPimpinan()
        &&
        !$user->isAdmin()
        &&
        !$user->isSuperAdmin()
    ) {

        return redirect('/')
            ->with(
                'warning',
                'Layanan Pengaduan Bedulang hanya dapat diakses oleh Pimpinan.'
            );

    }


    return Inertia::render('Complaints/BedulangComplaint', [
        'user'=>$user,
    ]);
}

    public function storeBedulang(Request $request)
    {
        $validated = $request->validate([

            'judul' => [
                'required',
                'string',
                'max:255'
            ],

            'kategori' => [
            'required',
            'string'
        ],

        'keterangan_kategori' => [
            'nullable',
            'string'
        ],

            'deskripsi' => [
                'required',
                'string'
            ],

        ]);

        $complaint = Complaint::create([

            'user_id' => Auth::id(),

            'service_slug' => 'pengaduan-bedulang',

            'title' => $request->judul,

            'category' => $validated['kategori'],

            'description' => 
                $request->deskripsi .
                (
                    $request->kategori === 'LAIN-LAIN'
                    ?
                    "\n\nKeterangan Kategori: ".$request->keterangan_kategori
                    :
                    ""
                ),

            'contact_person_name' => Auth::user()->name,

            'contact_email' => Auth::user()->email,

            'contact_phone' => null,

            'whatsapp_phone' => '6283128848371',

            'status' => 'pending',

        ]);

        $complaint->progressLogs()->create([
            
            'user_id' => Auth::id(),
            'status' => 'pending',
            'notes' => 'Pengaduan Bedulang dibuat dan menunggu konfirmasi pengiriman WhatsApp.',
        ]);



        // sementara nomor dummy
        // nanti diganti berdasarkan OPD

        $nomorWhatsapp = "6283128848371";


        $pesan = urlencode(
        "Halo! Saya ingin menyampaikan pengaduan.
        Judul:
        {$complaint->title}
        Kategori
        {$complaint->category}
        Deskripsi:
        {$complaint->description}"
            );


        $whatsappUrl =
            "https://wa.me/".$nomorWhatsapp."?text=".$pesan;



        return response()->json([

            'id' => $complaint->id,

            'whatsapp_url'=>$whatsappUrl

        ]);

    }
    public function markAsSent(Request $request, Complaint $complaint)
{
    if ($complaint->service_slug !== 'pengaduan-bedulang') {
        abort(403);
    }


    if ($complaint->user_id !== $request->user()->id) {
        abort(403);
    }


    if ($complaint->status !== 'pending') {
        return response()->json([
            'message' => 'Status pengaduan tidak valid.'
        ], 422);
    }


    $complaint->update([
        'status' => 'terkirim',
        'whatsapp_sent_at' => now(),
    ]);


    $complaint->progressLogs()->create([
        'user_id' => $request->user()->id,
        'status' => 'terkirim',
        'notes' => 'Pelapor mengkonfirmasi bahwa pesan WhatsApp telah dikirim.',
    ]);

    // Notify all admins (role = admin)
    $admins = User::where('role', 'admin')->get();
    foreach ($admins as $admin) {
        $admin->notify(new \App\Notifications\NewComplaintNotification($complaint));
    }


    return response()->json([
        'message' => 'Status berhasil diperbarui'
    ]);
}
}
