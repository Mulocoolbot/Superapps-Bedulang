<?php

namespace Tests\Feature;

use App\Services\KepegawaianService;
use App\Services\InformationServiceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KepegawaianServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepegawaian_service_is_registered_in_registry(): void
    {
        $registry = new InformationServiceRegistry();
        $this->assertTrue($registry->has('kepegawaian'));

        $service = $registry->resolve('kepegawaian');
        $this->assertInstanceOf(KepegawaianService::class, $service);
    }

    public function test_kepegawaian_service_returns_metadata(): void
    {
        $service = new KepegawaianService();
        $metadata = $service->getMetadata();

        $this->assertSame('Kepegawaian', $metadata['name']);
        $this->assertSame('https://apiipeelawann.bangkatengahkab.go.id/v5/25jsonpegawai.php', $metadata['source_url']);
    }

    public function test_kepegawaian_service_parses_live_json_correctly(): void
    {
        $mockJson = [
            [
                "nama_upd" => "Dinas Kesehatan",
                "kode" => "19.09",
                "jumlah_pns" => 44,
                "jumlah_p3k" => 16,
                "jumlah_p3k_pw" => 20,
                "total" => 80
            ],
            [
                "nama_upd" => "Dinas Komunikasi, Informatika dan Statistik",
                "kode" => "19.21",
                "jumlah_pns" => 31,
                "jumlah_p3k" => 3,
                "jumlah_p3k_pw" => 12,
                "total" => 46
            ],
        ];

        Http::fake([
            'https://apiipeelawann.bangkatengahkab.go.id/v5/25jsonpegawai.php' => Http::response($mockJson, 200),
        ]);

        $service = new KepegawaianService();
        $data = $service->getDashboardData(new Request());

        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('records', $data);
        
        $this->assertSame(126, $data['metadata']['grand_total']);
        $this->assertSame(75, $data['metadata']['total_pns']);
        $this->assertSame(19, $data['metadata']['total_p3k']);
        $this->assertSame(32, $data['metadata']['total_p3k_pw']);
        
        // Assert sorting by total descending
        $this->assertSame('Dinas Kesehatan', $data['records'][0]['nama_upd']);
        $this->assertSame(80, $data['records'][0]['total']);
        $this->assertSame('19.09', $data['records'][0]['kode_upd']);
        
        $this->assertSame('Dinas Komunikasi, Informatika dan Statistik', $data['records'][1]['nama_upd']);
        $this->assertSame(46, $data['records'][1]['total']);
    }

    public function test_kepegawaian_service_falls_back_to_mock_data_on_http_failure(): void
    {
        Http::fake([
            'https://apiipeelawann.bangkatengahkab.go.id/v5/25jsonpegawai.php' => Http::response(null, 500),
        ]);

        $service = new KepegawaianService();
        $data = $service->getDashboardData(new Request());

        $this->assertNotEmpty($data['records']);
        $this->assertGreaterThan(0, $data['metadata']['grand_total']);
    }
}
