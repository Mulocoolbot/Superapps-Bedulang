<?php

namespace Tests\Feature;

use App\Models\BloodStock;
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
                ['blood_type' => 'A', 'amount' => 50],
                ['blood_type' => 'O', 'amount' => 100],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('blood_stocks', [
            'blood_type' => 'A',
            'amount' => 50,
        ]);
        $this->assertDatabaseHas('blood_stocks', [
            'blood_type' => 'O',
            'amount' => 100,
        ]);

        $this->assertDatabaseHas('blood_stock_logs', [
            'blood_type' => 'A',
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
                ['blood_type' => 'A', 'amount' => 50],
            ],
        ]);

        $response->assertStatus(403);
    }
}
