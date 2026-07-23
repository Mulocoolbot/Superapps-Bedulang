<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PertanianService implements InformationServiceInterface
{
    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Pertanian',
            'icon' => '🌾',
            'description' => 'Dashboard informasi Dinas Pertanian Kabupaten Bangka Tengah.',
            'filters' => [],
            'source' => 'Dinas Pertanian Kab. Bangka Tengah',
            'source_url' => 'https://dpkp.bangkatengahkab.go.id/',
        ];
    }

    /**
     * Get the dashboard data for Pertanian.
     * Returns live TBS prices data used by InformasiPublikService or fallback.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request): array
    {
        return Cache::remember('pertanian_tbs_prices', 1800, function () {
            $parsedData = $this->fetchDataFromApi();
            if ($parsedData) {
                return $parsedData;
            }
            return $this->getMockData();
        });
    }

    /**
     * Fetch and parse live data from the official Pertanian & Ketahanan Pangan portal.
     *
     * @return array|null
     */
    public function fetchDataFromApi(): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->withOptions([
                'verify' => false,
            ])->timeout(10)->get('https://dpkp.bangkatengahkab.go.id/');

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();
            if (empty($html)) {
                return null;
            }

            // Extract date badge
            $formattedDate = 'Kamis, 23 Juli 2026';
            if (preg_match('/<div class="tbs-tanggal-badge">\s*📅\s*([^<]+)<\/div>/i', $html, $dateMatch)) {
                $formattedDate = trim($dateMatch[1]);
            }

            // Extract Factory Table Rows
            $factoryPrices = [];
            if (preg_match('/<h3>🏭 Harga di Pabrik Kelapa Sawit<\/h3>.*?<table class="tbs-table">.*?<tbody>(.*?)<\/tbody>/s', $html, $factorySection)) {
                preg_match_all('/<tr>\s*<td>\s*<strong>(.*?)<\/strong>(?:\s*<br><small[^>]*>(.*?)<\/small>)?.*?<td class="text-right">Rp([0-9\.]+)<\/td>.*?<span class="harga-hari-ini">Rp([0-9\.]+)<\/span>/s', $factorySection[1], $matches, PREG_SET_ORDER);

                $id = 1;
                foreach ($matches as $m) {
                    $name = trim(strip_tags($m[1]));
                    $location = isset($m[2]) ? trim(strip_tags($m[2])) : '';
                    $yesterday = (int) str_replace('.', '', $m[3]);
                    $today = (int) str_replace('.', '', $m[4]);

                    $factoryPrices[] = [
                        'id' => $id++,
                        'name' => $name,
                        'location' => $location,
                        'yesterday' => $yesterday,
                        'today' => $today,
                    ];
                }
            }

            // Extract District Table Rows
            $districtPrices = [];
            if (preg_match('/<h3>👨‍🌾 Rerata Harga Pekebun Non Mitra<\/h3>.*?<table class="tbs-table">.*?<tbody>(.*?)<\/tbody>/s', $html, $districtSection)) {
                preg_match_all('/<tr>\s*<td><strong>(.*?)<\/strong><\/td>.*?<td class="text-right">Rp([0-9\.]+)<\/td>.*?<span class="harga-hari-ini[^"]*">Rp([0-9\.]+)<\/span>/s', $districtSection[1], $matches, PREG_SET_ORDER);

                $id = 1;
                foreach ($matches as $m) {
                    $districtName = trim(strip_tags($m[1]));
                    if (!str_starts_with(strtolower($districtName), 'kecamatan')) {
                        $districtName = 'Kecamatan ' . $districtName;
                    }
                    $yesterday = (int) str_replace('.', '', $m[2]);
                    $today = (int) str_replace('.', '', $m[3]);

                    $districtPrices[] = [
                        'id' => $id++,
                        'district' => $districtName,
                        'yesterday' => $yesterday,
                        'today' => $today,
                    ];
                }
            }

            // Extract PDF Links
            $previewPdf = 'https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf';
            $downloadPdf = 'https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf';

            if (preg_match('/href="([^"]*harga-tbs\/preview-pdf[^"]*)"/i', $html, $prevMatch)) {
                $previewPdf = $prevMatch[1];
            }
            if (preg_match('/href="([^"]*harga-tbs\/download-pdf[^"]*)"/i', $html, $dlMatch)) {
                $downloadPdf = $dlMatch[1];
            }

            if (empty($factoryPrices) || empty($districtPrices)) {
                return null;
            }

            return [
                'metadata' => [
                    'source' => 'Dinas Pertanian Kab. Bangka Tengah',
                    'source_url' => 'https://dpkp.bangkatengahkab.go.id/',
                    'updated_at' => $formattedDate,
                    'preview_pdf' => $previewPdf,
                    'download_pdf' => $downloadPdf,
                ],
                'factory_prices' => $factoryPrices,
                'district_prices' => $districtPrices,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fallback mock data matching official entries.
     *
     * @return array
     */
    public function getMockData(): array
    {
        return [
            'metadata' => [
                'source' => 'Dinas Pertanian Kab. Bangka Tengah',
                'source_url' => 'https://dpkp.bangkatengahkab.go.id/',
                'updated_at' => 'Kamis, 23 Juli 2026',
                'preview_pdf' => 'https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf?tanggal=2026-07-23',
                'download_pdf' => 'https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf?tanggal=2026-07-23',
            ],
            'factory_prices' => [
                ['id' => 1, 'name' => 'PT Putra Bangka Tani', 'location' => 'Desa Belilik', 'today' => 3100, 'yesterday' => 3100],
                ['id' => 2, 'name' => 'PT Bangka Agro Mandiri', 'location' => 'Desa Pinang Sebatang', 'today' => 3050, 'yesterday' => 3050],
                ['id' => 3, 'name' => 'PT Perlang Sawitindo Mas', 'location' => 'Desa Perlang', 'today' => 3030, 'yesterday' => 3030],
                ['id' => 4, 'name' => 'PT Swarna Nusa Sentosa', 'location' => 'Desa Kemingking', 'today' => 3000, 'yesterday' => 3000],
            ],
            'district_prices' => [
                ['id' => 1, 'district' => 'Kecamatan Sungaiselan', 'today' => 2750, 'yesterday' => 2750],
                ['id' => 2, 'district' => 'Kecamatan Lubuk Besar', 'today' => 2820, 'yesterday' => 2820],
                ['id' => 3, 'district' => 'Kecamatan Simpangkatis', 'today' => 2827, 'yesterday' => 2827],
                ['id' => 4, 'district' => 'Kecamatan Pangkalan Baru', 'today' => 2817, 'yesterday' => 2817],
                ['id' => 5, 'district' => 'Kecamatan Koba', 'today' => 2850, 'yesterday' => 2850],
                ['id' => 6, 'district' => 'Kecamatan Namang', 'today' => 2857, 'yesterday' => 2857],
            ],
        ];
    }
}
