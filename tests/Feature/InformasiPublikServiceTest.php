<?php

namespace Tests\Feature;

use App\Services\InformasiPublikService;
use App\Services\InformationServiceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class InformasiPublikServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_informasi_publik_service_is_registered_in_registry(): void
    {
        $registry = app(InformationServiceRegistry::class);

        $this->assertTrue($registry->has('informasi-publik'));
        $this->assertInstanceOf(InformasiPublikService::class, $registry->resolve('informasi-publik'));
    }

    public function test_informasi_publik_service_returns_metadata(): void
    {
        $service = app(InformasiPublikService::class);
        $metadata = $service->getMetadata();

        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('icon', $metadata);
        $this->assertArrayHasKey('description', $metadata);
        $this->assertEquals('Informasi Publik', $metadata['name']);
    }

    public function test_informasi_publik_service_returns_combined_dashboard_data(): void
    {
        $service = app(InformasiPublikService::class);
        $data = $service->getDashboardData(new Request());

        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('perindag', $data);
        $this->assertArrayHasKey('sawit', $data);
        $this->assertArrayHasKey('umkm', $data);
        $this->assertArrayHasKey('utd', $data);

        $this->assertEquals('empty', $data['umkm']['status']);
        $this->assertEquals('active', $data['utd']['status']);
    }
}
