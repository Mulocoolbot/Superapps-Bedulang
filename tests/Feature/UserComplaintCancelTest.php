<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserComplaintCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_complaint_history(): void
    {
        $user = User::factory()->create();
        Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Tes Pengaduan Saya',
            'description' => 'Deskripsi tes pengaduan.',
            'contact_person_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/my-complaints');
        $response->assertOk();
    }

    public function test_user_can_cancel_their_own_pending_complaint(): void
    {
        $user = User::factory()->create();
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Pengaduan Untuk Dibatalkan',
            'description' => 'Salah isi deskripsi.',
            'contact_person_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->patch("/my-complaints/{$complaint->id}/cancel");

        $response->assertRedirect();
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('complaint_progress_logs', [
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_user_cannot_cancel_another_users_complaint(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $complaint = Complaint::create([
            'user_id' => $user1->id,
            'service_slug' => 'kesehatan',
            'title' => 'Pengaduan User 1',
            'description' => 'Deskripsi user 1.',
            'contact_person_name' => 'User One',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)->patch("/my-complaints/{$complaint->id}/cancel");
        $response->assertForbidden();
    }

    public function test_user_cannot_cancel_processing_complaint(): void
    {
        $user = User::factory()->create();
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Pengaduan Sedang Diproses',
            'description' => 'Petugas sedang menindaklanjuti.',
            'contact_person_name' => 'Budi Santoso',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($user)->patch("/my-complaints/{$complaint->id}/cancel");

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'processing',
        ]);
    }

    public function test_admin_cannot_alter_status_of_cancelled_complaint(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Pengaduan Dibatalkan',
            'description' => 'Deskripsi.',
            'contact_person_name' => 'Budi Santoso',
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($admin)->put("/admin/complaints/{$complaint->id}", [
            'status' => 'processing',
            'notes' => 'Mencoba merubah status.',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'cancelled',
        ]);
    }
}
