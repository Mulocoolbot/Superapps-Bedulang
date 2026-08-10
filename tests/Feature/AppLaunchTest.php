<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AppLaunchTest extends TestCase
{
    public function test_launch_redirects_when_target_url_is_online()
    {
        Http::fake([
            'https://nibung-bateng.desa.id*' => Http::response('OK', 200),
        ]);

        $response = $this->get('/app/launch?name=Website+Desa+Nibung&url=https%3A%2F%2Fnibung-bateng.desa.id');

        $response->assertRedirect('https://nibung-bateng.desa.id');
    }

    public function test_launch_shows_maintenance_when_target_url_fails()
    {
        Http::fake([
            'https://broken-desa.desa.id*' => Http::response(null, 500),
        ]);

        $response = $this->get('/app/launch?name=Website+Desa+Broken&url=https%3A%2F%2Fbroken-desa.desa.id');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Front/Maintenance')
            ->where('appName', 'Website Desa Broken')
            ->where('targetUrl', 'https://broken-desa.desa.id')
        );
    }

    public function test_check_app_status_api_returns_status()
    {
        Http::fake([
            'https://active-desa.desa.id*' => Http::response('OK', 200),
        ]);

        $response = $this->getJson('/api/check-app-status?url=https%3A%2F%2Factive-desa.desa.id');

        $response->assertStatus(200);
        $response->assertJson([
            'is_online' => true,
        ]);
    }
}
