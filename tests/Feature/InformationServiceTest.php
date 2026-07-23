<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InformationServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_unauthenticated_user_redirected_to_login_when_accessing_service_pages(): void
    {
        $this->get('/layanan/pemerintahan')
            ->assertRedirect('/login');
    }

    public function test_service_pages_are_available_for_authenticated_users(): void
    {
        $user = User::factory()->create();

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
            'pariwisata',
            'data-sektoral',
            'makanan-bergizi-gratis',
            'perizinan-terpadu',
            'pelayanan-kesehatan',
            'pengaduan-ppid',
            'administrasi-warga',
        ];

        foreach ($slugs as $slug) {
            $this->actingAs($user)
                ->get('/layanan/' . $slug)
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

        $service = new \App\Services\PerindagService;
        $trends = $service->buildPriceTrends($request, 'perindagkop-ukm');

        $this->assertNotEmpty($trends);
        $this->assertSame('Beras', $trends[0]['commodity']);
        $this->assertSame('naik', $trends[0]['status']);
        $this->assertSame(12000.0, $trends[0]['price_ii']);
        $this->assertSame(10000.0, $trends[0]['price_i']);
    }

    public function test_perindagkop_stock_data_is_processed_from_api(): void
    {
        Http::fake([
            'https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga' => Http::response([
                'data' => [
                    [
                        'tanggal' => '2026-07-17',
                        'nama_barang' => 'Daging Sapi',
                        'price_ii' => 130000,
                    ]
                ]
            ], 200),
            'https://disperindagkop-ukm.bangkatengahkab.go.id/api/getstok/*' => Http::response([
                'success' => true,
                'penyedia' => 'Distributor',
                'tanggal_awal' => '2026-07-13',
                'tanggal_akhir' => '2026-07-20',
                'data' => [
                    [
                        'komoditi_id' => 2,
                        'nama_komoditi' => 'Beras Premium',
                        'satuan' => 'Ton',
                        'stok_awal' => 55,
                        'stok_akhir' => 40,
                        'selisih' => -15,
                        'status' => 'Turun',
                    ]
                ]
            ], 200)
        ]);

        $request = Request::create('/layanan/perindagkop-ukm', 'GET', [
            'stok_start_date' => '2026-07-13',
            'stok_end_date' => '2026-07-20',
            'penyedia' => '1',
        ]);

        $service = new \App\Services\PerindagService;
        $dashboard = $service->getDashboardData($request);

        $this->assertArrayHasKey('stok_data', $dashboard);
        $this->assertNotEmpty($dashboard['stok_data']);
        $this->assertSame('Beras Premium', $dashboard['stok_data'][0]['name']);
        $this->assertSame(40.0, $dashboard['stok_data'][0]['qtyStok']);
        $this->assertSame(55.0, $dashboard['stok_data'][0]['qtyComp']);
        $this->assertSame(-15.0, $dashboard['stok_data'][0]['diff']);
        $this->assertSame('turun', $dashboard['stok_data'][0]['status']);
    }
}
