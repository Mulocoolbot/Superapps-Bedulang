<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InformationServiceTest extends TestCase
{
    public function test_service_pages_are_available_for_all_frontend_layanan_slugs(): void
    {
        $slugs = [
            'pemerintahan',
            'pendidikan',
            'kesehatan',
            'perindagkop-ukm',
            'hukum',
            'sosial',
            'kependudukan',
            'kepegawaian',
            'lingkungan',
            'geografis',
            'pariwisata',
            'data-sektoral',
        ];

        foreach ($slugs as $slug) {
            $this->get('/layanan/' . $slug)
                ->assertOk();
        }
    }

    public function test_perindagkop_price_trends_are_processed_from_api_with_filters(): void
    {
        Http::fake([
            'https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga' => Http::sequence()
                ->push([
                    [
                        'pasar_id' => 1,
                        'nama_pasar' => 'Pasar A',
                        'nama_barang' => 'Beras',
                        'nominal' => 12000,
                        'tanggal' => '2026-07-02',
                        'tanggal_format' => '2026-07-02',
                        'komoditi_id' => 10,
                        'price_i' => 10000,
                        'price_ii' => 12000,
                        'selisih' => 2000,
                        'persentase' => 20,
                        'status' => 'naik',
                    ],
                    [
                        'pasar_id' => 2,
                        'nama_pasar' => 'Pasar B',
                        'nama_barang' => 'Beras',
                        'nominal' => 11000,
                        'tanggal' => '2026-07-02',
                        'tanggal_format' => '2026-07-02',
                        'komoditi_id' => 10,
                        'price_i' => 10000,
                        'price_ii' => 11000,
                        'selisih' => 1000,
                        'persentase' => 10,
                        'status' => 'naik',
                    ],
                    [
                        'pasar_id' => 1,
                        'nama_pasar' => 'Pasar A',
                        'nama_barang' => 'Beras',
                        'nominal' => 10000,
                        'tanggal' => '2026-07-01',
                        'tanggal_format' => '2026-07-01',
                        'komoditi_id' => 10,
                        'price_i' => 10000,
                        'price_ii' => 10000,
                        'selisih' => 0,
                        'persentase' => 0,
                        'status' => 'tetap',
                    ],
                ], 200),
        ]);

        $request = Request::create('/layanan/perindagkop-ukm', 'GET', [
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-02',
            'pasar_id' => '1',
        ]);

        $controller = new \App\Http\Controllers\InformationServiceController;
        $method = new \ReflectionMethod($controller, 'buildPriceTrends');
        $method->setAccessible(true);
        $trends = $method->invoke($controller, $request, 'perindagkop-ukm');

        $this->assertNotEmpty($trends);
        $this->assertSame('Beras', $trends[0]['commodity']);
        $this->assertSame('naik', $trends[0]['status']);
        $this->assertSame(12000.0, $trends[0]['price_ii']);
        $this->assertSame(10000.0, $trends[0]['price_i']);
    }
}
