<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

use App\Notifications\ComplaintStatusUpdatedNotification;
use App\Notifications\NewComplaintNotification;
use App\Models\User;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Complaint::class);

        $user = $request->user();

        // Base scoped query for user domain (pimpinan and super_admin see all)
        $baseQuery = Complaint::query();

        if (!$user->isSuperAdmin() && !$user->isPimpinan() && !empty($user->service_slug)) {
            $baseQuery->where('service_slug', $user->service_slug);
        }

        // Calculate summary statistics
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'terkirim' => (clone $baseQuery)->where('status', 'terkirim')->count(),
        ];

        // Filtered query for pagination list
        $query = clone $baseQuery;

        // Filter by status
        if ($request->has('status') && $request->status !== 'all' && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by service
        if ($request->has('service') && !empty($request->service)) {
            $query->where('service_slug', $request->service);
        }

        // Filter by type (bedulang vs info_data)
        if ($request->has('type') && !empty($request->type) && $request->type !== 'all') {
            if ($request->type === 'bedulang') {
                $query->where('service_slug', 'pengaduan-bedulang');
            } elseif ($request->type === 'layanan_info') {
                $query->where('service_slug', '!=', 'pengaduan-bedulang');
            }
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('contact_person_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        $complaints = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->onEachSide(100)
            ->withQueryString();

        $servicesList = [
            ['slug' => 'pemerintahan', 'name' => 'PEMERINTAHAN'],
            ['slug' => 'pendidikan', 'name' => 'PENDIDIKAN'],
            ['slug' => 'kesehatan', 'name' => 'KESEHATAN'],
            ['slug' => 'perindagkop-ukm', 'name' => 'PERINDAGKOP-UKM'],
            ['slug' => 'hukum', 'name' => 'HUKUM'],
            ['slug' => 'sosial', 'name' => 'SOSIAL'],
            ['slug' => 'kependudukan', 'name' => 'KEPENDUDUKAN'],
            ['slug' => 'kepegawaian', 'name' => 'KEPEGAWAIAN'],
            ['slug' => 'pertanian', 'name' => 'PERTANIAN'],
            ['slug' => 'informasi-publik', 'name' => 'INFORMASI PUBLIK'],
            ['slug' => 'pariwisata', 'name' => 'PARIWISATA'],
            ['slug' => 'data-sektoral', 'name' => 'DATA SEKTORAL'],
            ['slug' => 'makanan-bergizi-gratis', 'name' => 'MAKANAN BERGIZI GRATIS'],
            ['slug' => 'pengaduan-bedulang', 'name' => 'PENGADUAN BEDULANG'],
        ];

        return Inertia::render('Admin/Complaints/Index', [
            'complaints' => $complaints,
            'stats' => $stats,
            'services' => $servicesList,
            'filters' => [
                'status' => $request->status ?? 'all',
                'service' => $request->service ?? '',
                'search' => $request->search ?? '',
                'type' => $request->type ?? 'all',
            ],
            'userDomain' => $user->isPimpinan() ? '' : $user->service_slug,
            'isSuperAdmin' => $user->isSuperAdmin() || $user->isPimpinan() || empty($user->service_slug),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'pimpinan') {
            abort(403, 'Anda tidak memiliki akses untuk mengajukan pengaduan.');
        }

        $validated = $request->validate([
            'service_slug' => ['required', 'string', Rule::in([
                'pemerintahan', 'pendidikan', 'kesehatan', 'perindagkop-ukm',
                'hukum', 'sosial', 'kependudukan', 'kepegawaian', 'pertanian',
                'informasi-publik', 'pariwisata', 'data-sektoral', 'makanan-bergizi-gratis',
            ])],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:50',
            'contact_person_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $whatsappPhone = preg_replace('/\D+/', '', config('services.whatsapp.complaint_phone'));

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'service_slug' => $validated['service_slug'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'] ?? 'layanan',
            'contact_person_name' => $validated['contact_person_name'],
            'contact_email' => $validated['contact_email'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'whatsapp_phone' => $whatsappPhone,
            'status' => 'pending',
        ]);

        $complaint->progressLogs()->create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            'notes' => 'Pengaduan diajukan melalui WhatsApp',
        ]);

        // Notify domain admins assigned to this service_slug (Exclude SuperAdmins)
        $domainAdmins = User::query()
            ->where('role', 'admin')
            ->where('service_slug', $validated['service_slug'])
            ->get();

        foreach ($domainAdmins as $admin) {
            $admin->notify(new NewComplaintNotification($complaint));
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan berhasil dikirim. Tim kami akan segera memproses pengaduan Anda.',
            'complaint' => $complaint,
            'whatsapp_url' => $this->whatsAppUrl($complaint, $whatsappPhone),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Complaint $complaint)
    {
        Gate::authorize('view', $complaint);

        return Inertia::render('Admin/Complaints/Show', [
            'complaint' => $complaint->load(['user', 'progressLogs.user']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Complaint $complaint)
    {
        Gate::authorize('update', $complaint);

        $request->validate([
            'status' => 'required|in:pending,processing,resolved,rejected,cancelled,terkirim',
            'notes' => 'nullable|string',
            'followup_image' => [
                $request->status === 'resolved' ? 'required' : 'nullable',
                'image',
                'max:2048' // max 2MB
            ],
        ]);

        $currentStatus = $complaint->status;
        $newStatus = $request->status;

        // Locked final states: rejected, resolved, cancelled
        if (in_array($currentStatus, ['rejected', 'resolved', 'cancelled'])) {
            $statusLabel = [
                'rejected' => 'Ditolak',
                'resolved' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ][$currentStatus] ?? $currentStatus;

            return redirect()->back()->withErrors([
                'status' => "Pengaduan berstatus '{$statusLabel}' sudah bersifat final dan tidak dapat diubah lagi.",
            ]);
        }

        // Rule 1: From pending/terkirim -> processing or rejected
        if (in_array($currentStatus, ['pending', 'terkirim']) && !in_array($newStatus, ['processing', 'rejected'])) {
            $statusLabelCur = $currentStatus === 'terkirim' ? 'Terkirim' : 'Menunggu';
            return redirect()->back()->withErrors([
                'status' => "Pengaduan berstatus '{$statusLabelCur}' hanya dapat diubah menjadi 'Diproses' atau 'Ditolak'.",
            ]);
        }

        // Rule 3: From processing -> resolved or rejected (cannot revert to pending/terkirim)
        if ($currentStatus === 'processing' && !in_array($newStatus, ['resolved', 'rejected'])) {
            return redirect()->back()->withErrors([
                'status' => "Pengaduan yang sedang 'Diproses' tidak dapat dikembalikan ke 'Menunggu', hanya dapat diubah ke 'Selesai' atau 'Ditolak'.",
            ]);
        }

        $updateData = [
            'status' => $newStatus,
            'notes' => $request->notes,
        ];

        // Handle image upload
        if ($request->hasFile('followup_image')) {
            $file = $request->file('followup_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/complaints'), $filename);
            $updateData['followup_image'] = 'uploads/complaints/' . $filename;
            $updateData['followup_image_uploaded_at'] = now();
        }

        $complaint->update($updateData);

        $complaint->progressLogs()->create([
            'user_id' => auth()->id(),
            'status' => $newStatus,
            'notes' => $request->notes ?? null,
        ]);

        // Notify user about complaint status update
        if ($complaint->user) {
            $complaint->user->notify(new ComplaintStatusUpdatedNotification(
                $complaint,
                $newStatus,
                $request->notes ?? null
            ));
        }

        if ($request->header('X-Inertia')) {
            return redirect()->back()->with('success', 'Pengaduan berhasil diperbarui.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan berhasil diperbarui.',
            'complaint' => $complaint,
        ]);
    }

    /**
     * Store Bedulang website complaint in storage.
     */
    public function storeBedulang(Request $request)
    {
        $user = auth()->user();
        if (!$user->isPimpinan() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'category_other' => 'required_if:category,lain-lain|nullable|string|max:255',
            'description' => 'required|string',
        ]);

        $categoryValue = $validated['category'];
        if ($categoryValue === 'lain-lain') {
            $categoryValue = 'Lain-lain: ' . $validated['category_other'];
        }

        $whatsappPhone = preg_replace('/\D+/', '', config('services.whatsapp.complaint_phone'));

        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'pengaduan-bedulang',
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $categoryValue,
            'contact_person_name' => $user->name,
            'contact_email' => $user->email,
            'whatsapp_phone' => $whatsappPhone,
            'status' => 'pending', // Starts as pending until confirmed sent
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan berhasil dibuat. Mengarahkan ke WhatsApp...',
            'complaint' => $complaint,
            'whatsapp_url' => $this->whatsAppUrl($complaint, $whatsappPhone),
        ], 201);
    }

    /**
     * Mark complaint as sent to WhatsApp.
     */
    public function markAsSent(Request $request, Complaint $complaint)
    {
        if ($complaint->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($complaint->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Status pengaduan tidak dapat diubah.',
            ], 422);
        }

        $complaint->update([
            'status' => 'terkirim',
            'whatsapp_sent_at' => now(),
            'whatsapp_message_id' => 'msg_'.uniqid(),
        ]);

        $complaint->progressLogs()->create([
            'user_id' => auth()->id(),
            'status' => 'terkirim',
            'notes' => 'Pengaduan telah berhasil dikirim ke WhatsApp.',
        ]);

        // Notify all admins (role = admin)
        $admins = User::query()
            ->where('role', 'admin')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewComplaintNotification($complaint));
        }

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pengaduan tercatat sebagai terkirim.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Complaint $complaint)
    {
        Gate::authorize('delete', $complaint);

        $complaint->delete();

        if ($request->header('X-Inertia')) {
            return redirect()->route('admin.complaints.index')->with('success', 'Pengaduan berhasil dihapus.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan berhasil dihapus.',
        ]);
    }

    /**
     * Send complaint to WhatsApp
     */
    private function sendToWhatsApp(Complaint $complaint)
    {
        try {
            $message = $this->formatWhatsAppMessage($complaint);

            // TODO: Integrate with actual WhatsApp API (e.g., Twilio, WhatsApp Business API)
            // For now, this is a dummy implementation
            $whatsappPhone = config('services.whatsapp.number', '+6283128848371');

            // Update complaint with WhatsApp sent info
            $complaint->update([
                'whatsapp_sent_at' => now(),
                'whatsapp_message_id' => 'msg_'.uniqid(), // Dummy message ID
            ]);

            // Log WhatsApp message for debugging
            \Log::info('WhatsApp message sent', [
                'complaint_id' => $complaint->id,
                'phone' => $whatsappPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send WhatsApp message', [
                'complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format WhatsApp message
     */
    private function formatWhatsAppMessage(Complaint $complaint): string
    {
        return "*Pengaduan Layanan \"{$complaint->service_name}\"*\n\n".
            "nama : {$complaint->contact_person_name}\n".
            "judul : {$complaint->title}\n".
            "deskripsi : {$complaint->description}";
    }

    private function whatsAppUrl(Complaint $complaint, string $whatsappPhone): string
    {
        return "https://wa.me/{$whatsappPhone}?text=".rawurlencode($this->formatWhatsAppMessage($complaint));
    }
}
