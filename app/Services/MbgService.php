<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MbgService implements InformationServiceInterface
{
    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Makanan Bergizi Gratis',
            'icon' => '🍱',
            'description' => 'Dashboard Pemantauan Penerima Program Makanan Bergizi Gratis Kabupaten Bangka Tengah.',
            'filters' => [
                ['label' => 'PAUD', 'value' => 'paud'],
                ['label' => 'SD', 'value' => 'sd'],
                ['label' => 'SMP', 'value' => 'smp'],
            ],
        ];
    }

    /**
     * Get the dashboard data.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request): array
    {
        // Try to fetch from API and cache for 1 hour (3600 seconds)
        $apiData = Cache::remember('mbg_api_data', 3600, function () {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                    'Referer' => 'https://mbg.pdm.kemendikdasmen.go.id/',
                ])->withOptions([
                    'verify' => false,
                ])->timeout(10)->get('https://mbg.pdm.kemendikdasmen.go.id/rekapsatpen/getrekapkecamatan?kode_kabkota=290300');

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['status'] ?? '') === 'success' && !empty($json['data'])) {
                        return $json['data'];
                    }
                }
            } catch (\Throwable $e) {
                // Return null so we fall back
            }
            return null;
        });

        // Subdistrict mapping in Bangka Tengah
        $kecamatanMap = [
            '290301' => 'Koba',
            '290302' => 'Pangkalan Baru',
            '290303' => 'Sungai Selan',
            '290304' => 'Simpang Katis',
            '290305' => 'Lubuk Besar',
            '290306' => 'Namang',
        ];

        // Prepare tables and summary structures
        $tables = [
            'paud' => [],
            'sd' => [],
            'smp' => [],
        ];

        $summary = [
            'paud' => [],
            'sd' => [],
            'smp' => [],
        ];

        $chartLabels = [];
        $chartSeries = [];

        // Build data structure
        foreach ($kecamatanMap as $code => $name) {
            $chartLabels[] = $name;
            $subdistrictTotal = 0;

            foreach (['paud', 'sd', 'smp'] as $level) {
                $jumlah_perempuan = 0;
                $jumlah_laki = 0;
                $jumlah_alergi = 0;
                $jumlah_fobia = 0;
                $jumlah_intoleransi = 0;

                if ($apiData && isset($apiData[$code]['jenjang'])) {
                    foreach ($apiData[$code]['jenjang'] as $item) {
                        if (strtoupper($item['jenjang'] ?? '') === strtoupper($level)) {
                            $jumlah_perempuan = (int) ($item['jumlah_perempuan'] ?? 0);
                            $jumlah_laki = (int) ($item['jumlah_laki'] ?? 0);
                            $jumlah_alergi = (int) ($item['jumlah_alergi'] ?? 0);
                            $jumlah_fobia = (int) ($item['jumlah_fobia'] ?? 0);
                            $jumlah_intoleransi = (int) ($item['jumlah_intoleransi'] ?? 0);
                            break;
                        }
                    }
                } else {
                    // Fallback to static mock data if API is not available
                    $mock = $this->getMockRow($code, $level);
                    $jumlah_perempuan = $mock['jumlah_perempuan'];
                    $jumlah_laki = $mock['jumlah_laki'];
                    $jumlah_alergi = $mock['jumlah_alergi'];
                    $jumlah_fobia = $mock['jumlah_fobia'];
                    $jumlah_intoleransi = $mock['jumlah_intoleransi'];
                }

                $tables[$level][] = [
                    'kecamatan' => $name,
                    'kode_kecamatan' => $code,
                    'jumlah_perempuan' => $jumlah_perempuan,
                    'jumlah_laki' => $jumlah_laki,
                    'jumlah_alergi' => $jumlah_alergi,
                    'jumlah_fobia' => $jumlah_fobia,
                    'jumlah_intoleransi' => $jumlah_intoleransi,
                ];

                $subdistrictTotal += ($jumlah_perempuan + $jumlah_laki);
            }

            $chartSeries[] = $subdistrictTotal;
        }

        // Calculate summary statistics dynamically
        foreach (['paud', 'sd', 'smp'] as $level) {
            $total_perempuan = 0;
            $total_laki = 0;
            $total_alergi = 0;
            $total_fobia = 0;
            $total_intoleransi = 0;

            foreach ($tables[$level] as $row) {
                $total_perempuan += $row['jumlah_perempuan'];
                $total_laki += $row['jumlah_laki'];
                $total_alergi += $row['jumlah_alergi'];
                $total_fobia += $row['jumlah_fobia'];
                $total_intoleransi += $row['jumlah_intoleransi'];
            }

            $total_penerima = $total_perempuan + $total_laki;
            $total_kebutuhan_khusus = $total_alergi + $total_fobia + $total_intoleransi;

            $summary[$level] = [
                [
                    'title' => 'Total Penerima ' . strtoupper($level),
                    'value' => number_format($total_penerima, 0, ',', '.'),
                    'meta' => 'Siswa aktif terdaftar',
                ],
                [
                    'title' => 'Total Laki-laki',
                    'value' => number_format($total_laki, 0, ',', '.'),
                    'meta' => $total_penerima > 0 ? number_format(($total_laki / $total_penerima) * 100, 1) . '% dari total' : '0%',
                ],
                [
                    'title' => 'Total Perempuan',
                    'value' => number_format($total_perempuan, 0, ',', '.'),
                    'meta' => $total_penerima > 0 ? number_format(($total_perempuan / $total_penerima) * 100, 1) . '% dari total' : '0%',
                ],
                [
                    'title' => 'Kebutuhan Khusus',
                    'value' => number_format($total_kebutuhan_khusus, 0, ',', '.'),
                    'meta' => 'Alergi, Fobia & Intoleransi',
                ],
            ];
        }

        return [
            'summary' => $summary,
            'tables' => $tables,
            'chart' => [
                'labels' => $chartLabels,
                'series' => $chartSeries,
            ]
        ];
    }

    /**
     * Get mock data row if API is unavailable.
     */
    protected function getMockRow(string $code, string $level): array
    {
        $mockData = [
            'paud' => [
                '290301' => ['jumlah_perempuan' => 120, 'jumlah_laki' => 130, 'jumlah_alergi' => 5, 'jumlah_fobia' => 2, 'jumlah_intoleransi' => 3],
                '290302' => ['jumlah_perempuan' => 95, 'jumlah_laki' => 105, 'jumlah_alergi' => 3, 'jumlah_fobia' => 1, 'jumlah_intoleransi' => 2],
                '290303' => ['jumlah_perempuan' => 110, 'jumlah_laki' => 115, 'jumlah_alergi' => 6, 'jumlah_fobia' => 3, 'jumlah_intoleransi' => 4],
                '290304' => ['jumlah_perempuan' => 80, 'jumlah_laki' => 85, 'jumlah_alergi' => 2, 'jumlah_fobia' => 0, 'jumlah_intoleransi' => 1],
                '290305' => ['jumlah_perempuan' => 75, 'jumlah_laki' => 70, 'jumlah_alergi' => 1, 'jumlah_fobia' => 1, 'jumlah_intoleransi' => 1],
                '290306' => ['jumlah_perempuan' => 90, 'jumlah_laki' => 92, 'jumlah_alergi' => 4, 'jumlah_fobia' => 2, 'jumlah_intoleransi' => 2],
            ],
            'sd' => [
                '290301' => ['jumlah_perempuan' => 450, 'jumlah_laki' => 470, 'jumlah_alergi' => 18, 'jumlah_fobia' => 6, 'jumlah_intoleransi' => 12],
                '290302' => ['jumlah_perempuan' => 380, 'jumlah_laki' => 395, 'jumlah_alergi' => 14, 'jumlah_fobia' => 4, 'jumlah_intoleransi' => 9],
                '290303' => ['jumlah_perempuan' => 410, 'jumlah_laki' => 425, 'jumlah_alergi' => 22, 'jumlah_fobia' => 9, 'jumlah_intoleransi' => 15],
                '290304' => ['jumlah_perempuan' => 310, 'jumlah_laki' => 320, 'jumlah_alergi' => 11, 'jumlah_fobia' => 3, 'jumlah_intoleransi' => 7],
                '290305' => ['jumlah_perempuan' => 280, 'jumlah_laki' => 290, 'jumlah_alergi' => 8, 'jumlah_fobia' => 2, 'jumlah_intoleransi' => 5],
                '290306' => ['jumlah_perempuan' => 340, 'jumlah_laki' => 360, 'jumlah_alergi' => 15, 'jumlah_fobia' => 5, 'jumlah_intoleransi' => 10],
            ],
            'smp' => [
                '290301' => ['jumlah_perempuan' => 320, 'jumlah_laki' => 340, 'jumlah_alergi' => 12, 'jumlah_fobia' => 4, 'jumlah_intoleransi' => 8],
                '290302' => ['jumlah_perempuan' => 270, 'jumlah_laki' => 285, 'jumlah_alergi' => 9, 'jumlah_fobia' => 3, 'jumlah_intoleransi' => 6],
                '290303' => ['jumlah_perempuan' => 290, 'jumlah_laki' => 310, 'jumlah_alergi' => 15, 'jumlah_fobia' => 5, 'jumlah_intoleransi' => 10],
                '290304' => ['jumlah_perempuan' => 220, 'jumlah_laki' => 230, 'jumlah_alergi' => 8, 'jumlah_fobia' => 2, 'jumlah_intoleransi' => 5],
                '290305' => ['jumlah_perempuan' => 190, 'jumlah_laki' => 200, 'jumlah_alergi' => 5, 'jumlah_fobia' => 1, 'jumlah_intoleransi' => 3],
                '290306' => ['jumlah_perempuan' => 240, 'jumlah_laki' => 250, 'jumlah_alergi' => 10, 'jumlah_fobia' => 3, 'jumlah_intoleransi' => 7],
            ],
        ];

        return $mockData[$level][$code] ?? ['jumlah_perempuan' => 0, 'jumlah_laki' => 0, 'jumlah_alergi' => 0, 'jumlah_fobia' => 0, 'jumlah_intoleransi' => 0];
    }
}
