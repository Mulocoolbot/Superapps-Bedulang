<?php

namespace Tests\Feature;

use App\Services\InformationServiceRegistry;
use App\Services\PertanianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PertanianServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pertanian_service_is_registered_in_registry(): void
    {
        $registry = app(InformationServiceRegistry::class);

        $this->assertTrue($registry->has('pertanian'));
        $this->assertInstanceOf(PertanianService::class, $registry->resolve('pertanian'));
    }

    public function test_pertanian_service_returns_metadata(): void
    {
        $service = app(PertanianService::class);
        $metadata = $service->getMetadata();

        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('icon', $metadata);
        $this->assertArrayHasKey('description', $metadata);
        $this->assertArrayHasKey('source', $metadata);
        $this->assertEquals('Pertanian', $metadata['name']);
    }

    public function test_pertanian_service_parses_live_html_correctly(): void
    {
        $mockHtml = '
            <div class="tbs-tanggal-badge">📅 Rabu, 22 Juli 2026</div>
            <h3>🏭 Harga di Pabrik Kelapa Sawit</h3>
            <table class="tbs-table"><tbody>
                <tr>
                    <td><strong>PT Putra Bangka Tani</strong><br><small>Desa Belilik</small></td>
                    <td class="text-right">Rp3.100</td>
                    <td><span class="harga-hari-ini">Rp3.100</span></td>
                </tr>
            </tbody></table>
            <h3>👨‍🌾 Rerata Harga Pekebun Non Mitra</h3>
            <table class="tbs-table"><tbody>
                <tr>
                    <td><strong>Kecamatan Sungaiselan</strong></td>
                    <td class="text-right">Rp2.750</td>
                    <td><span class="harga-hari-ini">Rp2.750</span></td>
                </tr>
            </tbody></table>
            <a href="https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf">PDF</a>
            <a href="https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf">PDF</a>
        ';

        Http::fake([
            'dpkp.bangkatengahkab.go.id/*' => Http::response($mockHtml, 200),
        ]);

        $service = app(PertanianService::class);
        $data = $service->fetchDataFromApi();

        $this->assertNotNull($data);
        $this->assertEquals('Rabu, 22 Juli 2026', $data['metadata']['updated_at']);
        $this->assertCount(1, $data['factory_prices']);
        $this->assertEquals('PT Putra Bangka Tani', $data['factory_prices'][0]['name']);
        $this->assertEquals(3100, $data['factory_prices'][0]['today']);
        $this->assertCount(1, $data['district_prices']);
        $this->assertEquals('Kecamatan Sungaiselan', $data['district_prices'][0]['district']);
    }

    public function test_pertanian_service_falls_back_to_mock_data_on_http_failure(): void
    {
        Http::fake([
            'dpkp.bangkatengahkab.go.id/*' => Http::response(null, 500),
        ]);

        $service = app(PertanianService::class);
        $data = $service->getDashboardData(new Request());

        $this->assertNotNull($data);
        $this->assertArrayHasKey('factory_prices', $data);
        $this->assertArrayHasKey('district_prices', $data);
        $this->assertNotEmpty($data['factory_prices']);
    }
}
