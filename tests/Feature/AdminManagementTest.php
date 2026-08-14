<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_admin_kependudukan_with_slug(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->post('/admin/users', [
            'name' => 'Admin Dukcapil Test',
            'email' => 'dukcapil.test@bangkatengahkab.go.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin_kependudukan',
            'service_slug' => 'kependudukan',
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'dukcapil.test@bangkatengahkab.go.id',
            'role' => 'admin_kependudukan',
            'service_slug' => 'kependudukan',
        ]);

        $createdUser = User::where('email', 'dukcapil.test@bangkatengahkab.go.id')->first();
        $this->assertTrue($createdUser->isAdmin());
        $this->assertTrue($createdUser->isAdminKependudukan());
    }

    public function test_admin_kependudukan_can_access_kependudukan_panel_and_redirected_from_admin_root(): void
    {
        $adminKependudukan = User::factory()->create([
            'role' => 'admin_kependudukan',
            'service_slug' => 'kependudukan',
        ]);

        // Accessing /admin should redirect to /admin/kependudukan
        $response = $this->actingAs($adminKependudukan)->get('/admin');
        $response->assertRedirect(route('admin.kependudukan.index'));

        // Accessing /admin/kependudukan should be allowed (status 200)
        $panelResponse = $this->actingAs($adminKependudukan)->get('/admin/kependudukan');
        $panelResponse->assertStatus(200);
    }

    public function test_super_admin_can_update_admin_role_and_slug(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'service_slug' => 'kesehatan',
        ]);

        $response = $this->actingAs($superAdmin)->put("/admin/users/{$admin->id}", [
            'name' => 'Admin Dukcapil Updated',
            'email' => $admin->email,
            'role' => 'admin_kependudukan',
            'service_slug' => 'kependudukan',
        ]);

        $response->assertRedirect('/admin/users');

        $admin->refresh();
        $this->assertEquals('admin_kependudukan', $admin->role);
        $this->assertEquals('kependudukan', $admin->service_slug);
        $this->assertTrue($admin->isAdminKependudukan());
    }

    public function test_non_admin_cannot_access_kependudukan_import_panel(): void
    {
        $publicUser = User::factory()->create([
            'role' => 'publik',
        ]);

        $response = $this->actingAs($publicUser)->get('/admin/kependudukan');
        $response->assertStatus(403);
    }
}
