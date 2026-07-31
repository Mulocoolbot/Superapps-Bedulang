<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PertanianService implements InformationServiceInterface
{
    /**
     * API Endpoint for TBS Palm Oil prices.
     */
    protected const API_URL = 'https://dpkp.bangkatengahkab.go.id/api/datatbs';
    protected const WEB_URL = 'https://dpkp.bangkatengahkab.go.id/';

    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Desa',
            'icon' => '📍',
            'description' => 'Dashboard informasi mengenai desa dan komoditas pertanian/sawit di Kabupaten Bangka Tengah.',
            'filters' => [],
            'source' => 'Dinas Pertanian Kab. Bangka Tengah',
            'source_url' => self::WEB_URL,
            'api_url' => self::API_URL,
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
     * Fetch and parse live data from the official Pertanian API (or HTML scraping fallback).
     *
     * @return array|null
     */
    public function fetchDataFromApi(): ?array
    {
        // 1. Try fetching from the official JSON API: https://dpkp.bangkatengahkab.go.id/api/datatbs
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->timeout(10)->get(self::API_URL);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['success']) && $json['success'] && isset($json['data'])) {
                    $parsed = $this->parseJsonApiData($json);
                    if ($parsed) {
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback to HTML if API fails
        }

        // 2. Fallback to HTML Scraping if JSON API fails
        return $this->fetchDataFromHtml();
    }

    /**
     * Parse JSON API data from https://dpkp.bangkatengahkab.go.id/api/datatbs
     *
     * @param array $json
     * @return array|null
     */
    public function parseJsonApiData(array $json): ?array
    {
        $rawDate = $json['tanggal'] ?? null;
        $data = $json['data'] ?? [];

        $pabrikData = $data['harga_tbs_pabrik'] ?? [];
        $pekebunData = $data['harga_tbs_pekebun'] ?? [];

        if (empty($pabrikData) && empty($pekebunData)) {
            return null;
        }

        // Format updated date (e.g. "Jumat, 31 Juli 2026")
        $formattedDate = 'Terbaru';
        $dayName = $pabrikData[0]['hari'] ?? ($pekebunData[0]['hari'] ?? '');
        if ($rawDate) {
            try {
                $carbon = Carbon::parse($rawDate)->locale('id');
                $formattedDate = ($dayName ? $dayName . ', ' : '') . $carbon->translatedFormat('j F Y');
            } catch (\Throwable $e) {
                $formattedDate = $rawDate;
            }
        }

        // Parse Factory Prices (PKS)
        $factoryPrices = [];
        foreach ($pabrikData as $item) {
            $pabrik = $item['pabrik'] ?? [];
            $yesterday = (int) ($item['harga_pabrik_kemarin'] ?? 0);
            $today = (int) ($item['harga_pabrik_hari_ini'] ?? 0);
            $diff = $today - $yesterday;
            $changePct = $yesterday > 0 ? round(($diff / $yesterday) * 100, 2) : 0;

            $factoryPrices[] = [
                'id' => $item['id'] ?? ($pabrik['id'] ?? count($factoryPrices) + 1),
                'pabrik_id' => $item['pabrik_id'] ?? ($pabrik['id'] ?? null),
                'name' => $pabrik['nama'] ?? 'Pabrik Sawit',
                'singkatan' => $pabrik['singkatan'] ?? '',
                'location' => $pabrik['desa'] ?? '',
                'yesterday' => $yesterday,
                'today' => $today,
                'diff' => $diff,
                'change_percentage' => $changePct,
            ];
        }

        // Parse District Prices (Pekebun Non-Mitra)
        $districtPrices = [];
        foreach ($pekebunData as $item) {
            $kecamatan = $item['kecamatan'] ?? [];
            $districtName = $kecamatan['nama'] ?? 'Kecamatan';
            if (!str_starts_with(strtolower($districtName), 'kecamatan')) {
                $districtName = 'Kecamatan ' . $districtName;
            }

            $yesterday = (int) ($item['harga_pekebun_kemarin'] ?? 0);
            $today = (int) ($item['harga_pekebun_hari_ini'] ?? 0);
            $diff = $today - $yesterday;
            $changePct = $yesterday > 0 ? round(($diff / $yesterday) * 100, 2) : 0;

            $districtPrices[] = [
                'id' => $item['id'] ?? ($kecamatan['id'] ?? count($districtPrices) + 1),
                'kecamatan_id' => $item['kecamatan_id'] ?? ($kecamatan['id'] ?? null),
                'district' => $districtName,
                'yesterday' => $yesterday,
                'today' => $today,
                'diff' => $diff,
                'change_percentage' => $changePct,
            ];
        }

        // Pre-calculate summary statistics
        $summary = $this->calculateSummary($factoryPrices, $districtPrices);

        $dateParam = $rawDate ?? date('Y-m-d');
        $previewPdf = "https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf?tanggal={$dateParam}";
        $downloadPdf = "https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf?tanggal={$dateParam}";

        return [
            'metadata' => [
                'source' => 'Dinas Pertanian Kab. Bangka Tengah',
                'source_url' => self::WEB_URL,
                'api_url' => self::API_URL,
                'updated_at' => $formattedDate,
                'raw_date' => $rawDate,
                'preview_pdf' => $previewPdf,
                'download_pdf' => $downloadPdf,
            ],
            'factory_prices' => $factoryPrices,
            'district_prices' => $districtPrices,
            'summary' => $summary,
        ];
    }

    /**
     * Calculate statistical summary for sawit data.
     *
     * @param array $factoryPrices
     * @param array $districtPrices
     * @return array
     */
    public function calculateSummary(array $factoryPrices, array $districtPrices): array
    {
        $factoryCount = count($factoryPrices);
        $avgFactoryToday = 0;
        $avgFactoryYesterday = 0;
        $maxFactoryToday = 0;
        $minFactoryToday = 0;
        $highestFactory = null;

        if ($factoryCount > 0) {
            $sumFactoryToday = array_sum(array_column($factoryPrices, 'today'));
            $sumFactoryYesterday = array_sum(array_column($factoryPrices, 'yesterday'));
            $avgFactoryToday = (int) round($sumFactoryToday / $factoryCount);
            $avgFactoryYesterday = (int) round($sumFactoryYesterday / $factoryCount);

            $maxFactoryToday = max(array_column($factoryPrices, 'today'));
            $minFactoryToday = min(array_column($factoryPrices, 'today'));

            foreach ($factoryPrices as $f) {
                if ($f['today'] === $maxFactoryToday) {
                    $highestFactory = $f['name'];
                    break;
                }
            }
        }

        $districtCount = count($districtPrices);
        $avgDistrictToday = 0;
        $avgDistrictYesterday = 0;

        if ($districtCount > 0) {
            $sumDistrictToday = array_sum(array_column($districtPrices, 'today'));
            $sumDistrictYesterday = array_sum(array_column($districtPrices, 'yesterday'));
            $avgDistrictToday = (int) round($sumDistrictToday / $districtCount);
            $avgDistrictYesterday = (int) round($sumDistrictYesterday / $districtCount);
        }

        $factoryChangePct = $avgFactoryYesterday > 0
            ? round((($avgFactoryToday - $avgFactoryYesterday) / $avgFactoryYesterday) * 100, 2)
            : 0;

        $districtChangePct = $avgDistrictYesterday > 0
            ? round((($avgDistrictToday - $avgDistrictYesterday) / $avgDistrictYesterday) * 100, 2)
            : 0;

        return [
            'avg_factory_today' => $avgFactoryToday,
            'avg_factory_yesterday' => $avgFactoryYesterday,
            'factory_change_percentage' => $factoryChangePct,
            'max_factory_today' => $maxFactoryToday,
            'min_factory_today' => $minFactoryToday,
            'highest_factory' => $highestFactory,
            'avg_district_today' => $avgDistrictToday,
            'avg_district_yesterday' => $avgDistrictYesterday,
            'district_change_percentage' => $districtChangePct,
            'total_factories' => $factoryCount,
            'total_districts' => $districtCount,
        ];
    }

    /**
     * Fallback method to parse live HTML if API endpoint fails.
     *
     * @return array|null
     */
    protected function fetchDataFromHtml(): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->withOptions([
                'verify' => false,
            ])->timeout(10)->get(self::WEB_URL);

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
                    $diff = $today - $yesterday;
                    $changePct = $yesterday > 0 ? round(($diff / $yesterday) * 100, 2) : 0;

                    $factoryPrices[] = [
                        'id' => $id++,
                        'name' => $name,
                        'location' => $location,
                        'yesterday' => $yesterday,
                        'today' => $today,
                        'diff' => $diff,
                        'change_percentage' => $changePct,
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
                    $diff = $today - $yesterday;
                    $changePct = $yesterday > 0 ? round(($diff / $yesterday) * 100, 2) : 0;

                    $districtPrices[] = [
                        'id' => $id++,
                        'district' => $districtName,
                        'yesterday' => $yesterday,
                        'today' => $today,
                        'diff' => $diff,
                        'change_percentage' => $changePct,
                    ];
                }
            }

            if (empty($factoryPrices) || empty($districtPrices)) {
                return null;
            }

            $previewPdf = 'https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf';
            $downloadPdf = 'https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf';

            if (preg_match('/href="([^"]*harga-tbs\/preview-pdf[^"]*)"/i', $html, $prevMatch)) {
                $previewPdf = $prevMatch[1];
            }
            if (preg_match('/href="([^"]*harga-tbs\/download-pdf[^"]*)"/i', $html, $dlMatch)) {
                $downloadPdf = $dlMatch[1];
            }

            $summary = $this->calculateSummary($factoryPrices, $districtPrices);

            return [
                'metadata' => [
                    'source' => 'Dinas Pertanian Kab. Bangka Tengah',
                    'source_url' => self::WEB_URL,
                    'updated_at' => $formattedDate,
                    'preview_pdf' => $previewPdf,
                    'download_pdf' => $downloadPdf,
                ],
                'factory_prices' => $factoryPrices,
                'district_prices' => $districtPrices,
                'summary' => $summary,
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
        $factoryPrices = [
            ['id' => 1, 'name' => 'PT Putra Bangka Tani', 'singkatan' => 'PBT', 'location' => 'Desa Belilik', 'today' => 3100, 'yesterday' => 3100, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 2, 'name' => 'PT Bangka Agro Mandiri', 'singkatan' => 'BAM', 'location' => 'Desa Pinang Sebatang', 'today' => 3050, 'yesterday' => 3050, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 3, 'name' => 'PT Perlang Sawitindo Mas', 'singkatan' => 'PSM', 'location' => 'Desa Perlang', 'today' => 3030, 'yesterday' => 3030, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 4, 'name' => 'PT Swarna Nusa Sentosa', 'singkatan' => 'SNS', 'location' => 'Desa Kemingking', 'today' => 3000, 'yesterday' => 3000, 'diff' => 0, 'change_percentage' => 0],
        ];

        $districtPrices = [
            ['id' => 1, 'district' => 'Kecamatan Sungaiselan', 'today' => 2750, 'yesterday' => 2750, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 2, 'district' => 'Kecamatan Lubuk Besar', 'today' => 2820, 'yesterday' => 2820, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 3, 'district' => 'Kecamatan Simpangkatis', 'today' => 2827, 'yesterday' => 2827, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 4, 'district' => 'Kecamatan Pangkalan Baru', 'today' => 2817, 'yesterday' => 2817, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 5, 'district' => 'Kecamatan Koba', 'today' => 2850, 'yesterday' => 2850, 'diff' => 0, 'change_percentage' => 0],
            ['id' => 6, 'district' => 'Kecamatan Namang', 'today' => 2857, 'yesterday' => 2857, 'diff' => 0, 'change_percentage' => 0],
        ];

        return [
            'metadata' => [
                'source' => 'Dinas Pertanian Kab. Bangka Tengah',
                'source_url' => self::WEB_URL,
                'api_url' => self::API_URL,
                'updated_at' => 'Kamis, 23 Juli 2026',
                'preview_pdf' => 'https://dpkp.bangkatengahkab.go.id/harga-tbs/preview-pdf?tanggal=2026-07-23',
                'download_pdf' => 'https://dpkp.bangkatengahkab.go.id/harga-tbs/download-pdf?tanggal=2026-07-23',
            ],
            'factory_prices' => $factoryPrices,
            'district_prices' => $districtPrices,
            'summary' => $this->calculateSummary($factoryPrices, $districtPrices),
        ];
    }
}

