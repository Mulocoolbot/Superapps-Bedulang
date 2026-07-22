<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintStatusUpdatedNotification extends Notification
{
    use Queueable;

    public Complaint $complaint;
    public string $status;
    public ?string $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Complaint $complaint, string $status, ?string $notes = null)
    {
        $this->complaint = $complaint;
        $this->status = $status;
        $this->notes = $notes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $labels = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'resolved' => 'Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        $statusLabel = $labels[$this->status] ?? $this->status;

        return [
            'type' => 'status_updated',
            'complaint_id' => $this->complaint->id,
            'title' => 'Pembaruan Status Pengaduan',
            'status' => $this->status,
            'status_label' => $statusLabel,
            'complaint_title' => $this->complaint->title,
            'notes' => $this->notes,
            'message' => "Status pengaduan Anda '{$this->complaint->title}' telah diperbarui menjadi {$statusLabel}.",
            'url' => '/my-complaints',
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
