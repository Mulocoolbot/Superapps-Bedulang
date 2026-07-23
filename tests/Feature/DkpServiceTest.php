<?php

namespace Tests\Feature;

use App\Services\DkpService;
use App\Services\InformationServiceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DkpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dkp_service_is_registered_in_registry(): void
    {
        $registry = new InformationServiceRegistry();
        $this->assertTrue($registry->has('lingkungan'));

        $service = $registry->resolve('lingkungan');
        $this->assertInstanceOf(DkpService::class, $service);
    }

    public function test_dkp_service_returns_metadata(): void
    {
        $service = new DkpService();
        $metadata = $service->getMetadata();

        $this->assertSame('DPKP', $metadata['name']);
        $this->assertSame('https://dpkp.bangkatengahkab.go.id/', $metadata['source_url']);
    }

    public function test_dkp_service_parses_live_html_correctly(): void
    {
        $mockHtml = '
            <div class="tbs-tanggal-badge">📅 Rabu, 22 Juli 2026</div>
            <div class="tbs-card">
                <h3>🏭 Harga di Pabrik Kelapa Sawit</h3>
                <table class="tbs-table">
                    <tbody>
                        <tr>
                            <td><strong>PT Putra Bangka Tani</strong><br><small style="color:#999;">Desa Belilik</small></td>
                            <td class="text-right">Rp3.100</td>
                            <td class="text-right"><span class="harga-hari-ini">Rp3.100</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="tbs-card">
                <h3>👨‍🌾 Rerata Harga Pekebun Non Mitra</h3>
                <table class="tbs-table">
                    <tbody>
                        <tr>
                            <td><strong>Sungaiselan</strong></td>
                            <td class="text-right">Rp2.750</td>
                            <td class="text-right"><span class="harga-hari-ini gold">Rp2.750</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <a href="https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf?tanggal=2026-07-22">Preview PDF</a>
            <a href="https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf?tanggal=2026-07-22">Download PDF</a>
        ';

        Http::fake([
            'https://dpkp.bangkatengahkab.go.id/' => Http::response($mockHtml, 200),
        ]);

        $service = new DkpService();
        $data = $service->getDashboardData(new Request());

        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('factory_prices', $data);
        $this->assertArrayHasKey('district_prices', $data);
        $this->assertSame('PT Putra Bangka Tani', $data['factory_prices'][0]['name']);
        $this->assertSame('Desa Belilik', $data['factory_prices'][0]['location']);
        $this->assertSame(3100, $data['factory_prices'][0]['today']);
        $this->assertSame('Kecamatan Sungaiselan', $data['district_prices'][0]['district']);
        $this->assertSame(2750, $data['district_prices'][0]['today']);
    }

    public function test_dkp_service_falls_back_to_mock_data_on_http_failure(): void
    {
        Http::fake([
            'https://dpkp.bangkatengahkab.go.id/' => Http::response(null, 500),
        ]);

        $service = new DkpService();
        $data = $service->getMockData();

        $this->assertNotEmpty($data['factory_prices']);
        $this->assertNotEmpty($data['district_prices']);
    }
}
