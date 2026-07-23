<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewComplaintNotification extends Notification
{
    use Queueable;

    public Complaint $complaint;

    /**
     * Create a new notification instance.
     */
    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
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
        $serviceName = ucwords(str_replace('-', ' ', $this->complaint->service_slug));

        return [
            'type' => 'new_complaint',
            'complaint_id' => $this->complaint->id,
            'title' => 'Pengaduan Baru Masuk',
            'service_name' => $serviceName,
            'service_slug' => $this->complaint->service_slug,
            'complaint_title' => $this->complaint->title,
            'message' => "Pengaduan baru '{$this->complaint->title}' diajukan pada bidang {$serviceName}.",
            'url' => '/admin/complaints/' . $this->complaint->id,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
