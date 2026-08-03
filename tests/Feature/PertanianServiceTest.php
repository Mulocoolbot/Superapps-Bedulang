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
        $this->assertEquals('Desa', $metadata['name']);
    }

    public function test_pertanian_service_parses_json_api_correctly(): void
    {
        $mockJson = [
            'success' => true,
            'tanggal' => '2026-07-31',
            'data' => [
                'harga_tbs_pabrik' => [
                    [
                        'id' => 241,
                        'tanggal' => '2026-07-31T00:00:00.000000Z',
                        'hari' => 'Jumat',
                        'pabrik_id' => 1,
                        'harga_pabrik_kemarin' => 3000,
                        'harga_pabrik_hari_ini' => 3100,
                        'pabrik' => [
                            'id' => 1,
                            'nama' => 'PT Putra Bangka Tani',
                            'singkatan' => 'PBT',
                            'desa' => 'Desa Belilik',
                        ],
                    ],
                ],
                'harga_tbs_pekebun' => [
                    [
                        'id' => 245,
                        'tanggal' => '2026-07-31T00:00:00.000000Z',
                        'hari' => 'Jumat',
                        'kecamatan_id' => 1,
                        'harga_pekebun_kemarin' => 2700,
                        'harga_pekebun_hari_ini' => 2750,
                        'kecamatan' => [
                            'id' => 1,
                            'nama' => 'Sungaiselan',
                        ],
                    ],
                ],
            ],
        ];

        Http::fake([
            'https://dpkp.bangkatengahkab.go.id/api/datatbs' => Http::response($mockJson, 200),
        ]);

        $service = app(PertanianService::class);
        $data = $service->fetchDataFromApi();

        $this->assertNotNull($data);
        $this->assertStringContainsString('31 Juli 2026', $data['metadata']['updated_at']);
        $this->assertCount(1, $data['factory_prices']);
        $this->assertEquals('PT Putra Bangka Tani', $data['factory_prices'][0]['name']);
        $this->assertEquals(3100, $data['factory_prices'][0]['today']);
        $this->assertEquals(3000, $data['factory_prices'][0]['yesterday']);
        $this->assertEquals(100, $data['factory_prices'][0]['diff']);
        $this->assertEquals(3.33, $data['factory_prices'][0]['change_percentage']);
        
        $this->assertCount(1, $data['district_prices']);
        $this->assertEquals('Kecamatan Sungaiselan', $data['district_prices'][0]['district']);
        $this->assertEquals(2750, $data['district_prices'][0]['today']);
        
        $this->assertArrayHasKey('summary', $data);
        $this->assertEquals(3100, $data['summary']['avg_factory_today']);
        $this->assertEquals(2750, $data['summary']['avg_district_today']);
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
            'https://dpkp.bangkatengahkab.go.id/api/datatbs' => Http::response(null, 404),
            'https://dpkp.bangkatengahkab.go.id/' => Http::response($mockHtml, 200),
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
