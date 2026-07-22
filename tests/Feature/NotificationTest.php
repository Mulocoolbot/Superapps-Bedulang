<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintStatusUpdatedNotification;
use App\Notifications\NewComplaintNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_complaint_notifies_only_domain_admins(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'publik']);
        $domainAdmin = User::factory()->create([
            'role' => 'admin',
            'service_slug' => 'kesehatan',
        ]);
        $otherDomainAdmin = User::factory()->create([
            'role' => 'admin',
            'service_slug' => 'pendidikan',
        ]);
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'service_slug' => null,
        ]);

        $response = $this->actingAs($user)->post('/complaints', [
            'service_slug' => 'kesehatan',
            'title' => 'Laporan Kesehatan',
            'description' => 'Fasilitas perlu diperbaiki.',
            'contact_person_name' => 'Budi',
        ]);

        $response->assertStatus(201);

        // Assert domain admin received notification
        Notification::assertSentTo(
            [$domainAdmin],
            NewComplaintNotification::class
        );

        // Assert other domain admin and super admin did NOT receive notification
        Notification::assertNotSentTo(
            [$otherDomainAdmin, $superAdmin],
            NewComplaintNotification::class
        );
    }

    public function test_admin_updating_complaint_status_notifies_complaint_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'publik']);
        $admin = User::factory()->create([
            'role' => 'admin',
            'service_slug' => 'kesehatan',
        ]);

        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Pengaduan Layanan',
            'description' => 'Deskripsi.',
            'contact_person_name' => 'Budi',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put("/admin/complaints/{$complaint->id}", [
            'status' => 'processing',
            'notes' => 'Sedang ditindaklanjuti.',
        ]);

        $response->assertSessionHasNoErrors();

        // Assert user who owns complaint received ComplaintStatusUpdatedNotification
        Notification::assertSentTo(
            [$user],
            ComplaintStatusUpdatedNotification::class
        );
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new ComplaintStatusUpdatedNotification(
            Complaint::create([
                'user_id' => $user->id,
                'service_slug' => 'kesehatan',
                'title' => 'Tes Pengaduan',
                'description' => 'Deskripsi',
                'contact_person_name' => 'Budi',
                'status' => 'pending',
            ]),
            'processing'
        ));

        $notification = $user->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Tes Pengaduan',
            'description' => 'Deskripsi',
            'contact_person_name' => 'Budi',
            'status' => 'pending',
        ]);

        $user->notify(new ComplaintStatusUpdatedNotification($complaint, 'processing'));
        $user->notify(new ComplaintStatusUpdatedNotification($complaint, 'resolved'));

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->post('/notifications/read-all');

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}
