<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Complaint::class);

        $query = Complaint::query();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by service
        if ($request->has('service') && $request->service) {
            $query->where('service_slug', $request->service);
        }

        // Search
        if ($request->has('search') && $request->search) {
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
            ->withQueryString();

        return Inertia::render('Admin/Complaints/Index', [
            'complaints' => $complaints,
            'filters' => [
                'status' => $request->status ?? 'all',
                'service' => $request->service ?? '',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_slug' => ['required', 'string', Rule::in([
                'pemerintahan', 'pendidikan', 'kesehatan', 'perindagkop-ukm',
                'hukum', 'sosial', 'kependudukan', 'kepegawaian', 'lingkungan',
                'geografis', 'pariwisata', 'data-sektoral', 'makanan-bergizi-gratis',
            ])],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:50',
            'contact_person_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
        ]);

        $whatsappPhone = preg_replace('/\D+/', '', config('services.whatsapp.complaint_phone'));

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'service_slug' => $validated['service_slug'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'contact_person_name' => $validated['contact_person_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'whatsapp_phone' => $whatsappPhone,
            'status' => 'pending',
        ]);

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
        $this->authorize('view', $complaint);

        return Inertia::render('Admin/Complaints/Show', [
            'complaint' => $complaint->load('user'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,resolved,rejected',
            'notes' => 'nullable|string',
        ]);

        $complaint->update($validated);

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
     * Remove the specified resource from storage.
     */
    public function destroy(Complaint $complaint)
    {
        $this->authorize('delete', $complaint);

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
        return "🔔 *PENGADUAN BARU*\n\n".
            "*Layanan:* {$complaint->service_name}\n".
            "*Judul:* {$complaint->title}\n".
            "*Kategori:* {$complaint->category}\n".
            "*Nama:* {$complaint->contact_person_name}\n".
            "*Email:* {$complaint->contact_email}\n".
            "*Telepon:* {$complaint->contact_phone}\n".
            "*Deskripsi:*\n{$complaint->description}\n\n".
            'Link: '.url("/admin/complaints/{$complaint->id}");
    }

    private function whatsAppUrl(Complaint $complaint, string $whatsappPhone): string
    {
        return "https://wa.me/{$whatsappPhone}?text=".rawurlencode($this->formatWhatsAppMessage($complaint));
    }
}
