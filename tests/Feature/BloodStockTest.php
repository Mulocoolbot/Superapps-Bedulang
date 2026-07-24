<?php

namespace Tests\Feature;

use App\Models\BloodStock;
use App\Models\BloodStockLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BloodStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_utd_admin_can_update_blood_stocks(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'service_slug' => 'informasi-publik',
        ]);

        $response = $this->actingAs($admin)->post('/admin/blood-stocks', [
            'stocks' => [
                ['component' => 'Whole Blood (WB)', 'blood_type' => 'A+', 'amount' => 50],
                ['component' => 'Packed Red Blood (PRC)', 'blood_type' => 'O-', 'amount' => 100],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blood_stocks', [
            'component' => 'Whole Blood (WB)',
            'blood_type' => 'A+',
            'amount' => 50,
        ]);
        $this->assertDatabaseHas('blood_stocks', [
            'component' => 'Packed Red Blood (PRC)',
            'blood_type' => 'O-',
            'amount' => 100,
        ]);

        $this->assertDatabaseHas('blood_stock_logs', [
            'component' => 'Whole Blood (WB)',
            'blood_type' => 'A+',
            'new_amount' => 50,
            'delta' => 50,
        ]);
    }

    public function test_regular_user_cannot_update_blood_stocks(): void
    {
        $user = User::factory()->create([
            'role' => 'publik',
        ]);

        $response = $this->actingAs($user)->post('/admin/blood-stocks', [
            'stocks' => [
                ['component' => 'Whole Blood (WB)', 'blood_type' => 'A+', 'amount' => 50],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_utd_admin_can_access_history_page_with_ten_logs_per_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'service_slug' => 'informasi-publik',
        ]);

        // Create 25 dummy logs
        for ($i = 1; $i <= 25; $i++) {
            BloodStockLog::create([
                'component' => 'Whole Blood (WB)',
                'blood_type' => 'A+',
                'old_amount' => $i,
                'new_amount' => $i + 5,
                'delta' => 5,
                'description' => "Penambahan stok +5 kantong (#{$i})",
                'updated_by' => $admin->id,
            ]);
        }

        $response = $this->actingAs($admin)->get('/utd/history');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Front/Services/BloodStockHistory')
                 ->has('logs.data', 10)
                 ->where('logs.total', 25)
                 ->where('logs.per_page', 10)
        );
    }

    public function test_regular_public_user_cannot_access_history_page(): void
    {
        $user = User::factory()->create([
            'role' => 'publik',
        ]);

        $response = $this->actingAs($user)->get('/utd/history');
        $response->assertStatus(403);

        $guestResponse = $this->get('/utd/history');
        $guestResponse->assertStatus(403);
    }
}
