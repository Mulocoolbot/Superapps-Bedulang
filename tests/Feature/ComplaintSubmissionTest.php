<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_a_valid_complaint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/complaints', [
            'service_slug' => 'kesehatan',
            'title' => 'Layanan perlu diperbaiki',
            'description' => 'Informasi layanan tidak dapat diakses sejak pagi.',
            'category' => 'layanan',
            'contact_person_name' => 'Budi Santoso',
            'contact_email' => 'budi@example.test',
            'contact_phone' => '08123456789',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('whatsapp_url', fn (string $url) => str_starts_with($url, 'https://wa.me/6283128848371?text='));

        $this->assertDatabaseHas('complaints', [
            'service_slug' => 'kesehatan',
            'title' => 'Layanan perlu diperbaiki',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('complaint_progress_logs', [
            'status' => 'pending',
            'notes' => 'Pengaduan diajukan melalui WhatsApp',
        ]);
    }

    public function test_admin_updating_complaint_creates_progress_log(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Layanan perlu diperbaiki',
            'description' => 'Informasi layanan tidak dapat diakses sejak pagi.',
            'category' => 'layanan',
            'contact_person_name' => 'Budi Santoso',
            'contact_email' => 'budi@example.test',
            'contact_phone' => '08123456789',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put("/admin/complaints/{$complaint->id}", [
            'status' => 'processing',
            'notes' => 'Tim petugas telah dikirim ke lokasi.',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'processing',
            'notes' => 'Tim petugas telah dikirim ke lokasi.',
        ]);

        $this->assertDatabaseHas('complaint_progress_logs', [
            'complaint_id' => $complaint->id,
            'user_id' => $admin->id,
            'status' => 'processing',
            'notes' => 'Tim petugas telah dikirim ke lokasi.',
        ]);
    }

    public function test_admin_cannot_resolve_complaint_without_proof_image(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Layanan perlu diperbaiki',
            'description' => 'Informasi layanan tidak dapat diakses sejak pagi.',
            'category' => 'layanan',
            'contact_person_name' => 'Budi Santoso',
            'contact_email' => 'budi@example.test',
            'contact_phone' => '08123456789',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($admin)->put("/admin/complaints/{$complaint->id}", [
            'status' => 'resolved',
            'notes' => 'Pekerjaan selesai.',
        ]);

        $response->assertSessionHasErrors('followup_image');
        $this->assertEquals('processing', $complaint->fresh()->status);
    }

    public function test_admin_can_resolve_complaint_with_proof_image(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'service_slug' => 'kesehatan',
            'title' => 'Layanan perlu diperbaiki',
            'description' => 'Informasi layanan tidak dapat diakses sejak pagi.',
            'category' => 'layanan',
            'contact_person_name' => 'Budi Santoso',
            'contact_email' => 'budi@example.test',
            'contact_phone' => '08123456789',
            'status' => 'processing',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($admin)->put("/admin/complaints/{$complaint->id}", [
            'status' => 'resolved',
            'notes' => 'Pekerjaan selesai dengan bukti foto.',
            'followup_image' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $complaint->refresh();
        $this->assertEquals('resolved', $complaint->status);
        $this->assertNotNull($complaint->followup_image);
    }

    public function test_guest_cannot_submit_a_complaint(): void
    {
        $this->postJson('/complaints', [])->assertUnauthorized();
    }

    public function test_complaint_requires_complete_contact_information(): void
    {
        $this->actingAs(User::factory()->create())->postJson('/complaints', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'service_slug', 'title', 'description', 'contact_person_name',
            ]);

        $this->assertSame(0, Complaint::count());
    }
}
