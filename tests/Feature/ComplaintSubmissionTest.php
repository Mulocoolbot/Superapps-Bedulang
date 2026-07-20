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
                'service_slug', 'title', 'description', 'category',
                'contact_person_name', 'contact_email', 'contact_phone',
            ]);

        $this->assertSame(0, Complaint::count());
    }
}
