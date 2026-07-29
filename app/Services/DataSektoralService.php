<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DataSektoralService
{
    /**
     * Base URL for the sikokdata API.
     */
    protected string $baseUrl = 'https://sikokdata.bangkatengahkab.go.id/api/dataseturusan';

    /**
     * Get list of urusan (level 1).
     *
     * @return array
     */
    public function getUrusan(): array
    {
        return Cache::remember('data_sektoral_urusan', 3600, function () {
            return $this->fetchFromApi($this->baseUrl) ?? [];
        });
    }

    /**
     * Get list of sub urusan for a given urusan (level 2).
     *
     * @param string $urusan
     * @return array
     */
    public function getSubUrusan(string $urusan): array
    {
        $cacheKey = 'data_sektoral_sub_urusan_' . md5($urusan);

        return Cache::remember($cacheKey, 3600, function () use ($urusan) {
            $url = $this->baseUrl . '/' . rawurlencode($urusan);
            return $this->fetchFromApi($url) ?? [];
        });
    }

    /**
     * Get list of indikator for a given urusan and sub_urusan (level 3).
     *
     * @param string $urusan
     * @param string $subUrusan
     * @return array
     */
    public function getIndikator(string $urusan, string $subUrusan): array
    {
        $cacheKey = 'data_sektoral_indikator_' . md5($urusan . '_' . $subUrusan);

        return Cache::remember($cacheKey, 3600, function () use ($urusan, $subUrusan) {
            $url = $this->baseUrl . '/' . rawurlencode($urusan) . '/' . rawurlencode($subUrusan);
            return $this->fetchFromApi($url) ?? [];
        });
    }

    /**
     * Get sectoral data for a given urusan, sub_urusan, indikator, and year range (level 4).
     *
     * @param string $urusan
     * @param string $subUrusan
     * @param string $indikator
     * @param int $tahunAwal
     * @param int $tahunAkhir
     * @return array
     */
    public function getData(string $urusan, string $subUrusan, string $indikator, int $tahunAwal, int $tahunAkhir): array
    {
        $startYear = min((int) $tahunAwal, (int) $tahunAkhir);
        $endYear = max((int) $tahunAwal, (int) $tahunAkhir);

        $cacheKey = 'data_sektoral_data_' . md5(implode('_', [$urusan, $subUrusan, $indikator, $startYear, $endYear]));

        return Cache::remember($cacheKey, 1800, function () use ($urusan, $subUrusan, $indikator, $startYear, $endYear) {
            $url = $this->baseUrl . '/'
                . rawurlencode($urusan) . '/'
                . rawurlencode($subUrusan) . '/'
                . rawurlencode($indikator) . '/'
                . $startYear . '/'
                . $endYear;

            return $this->fetchFromApi($url, true) ?? [];
        });
    }

    /**
     * Download or stream PDF report for a given dataset.
     *
     * @param string $urusan
     * @param string $subUrusan
     * @param string $indikator
     * @param int $tahunAwal
     * @param int $tahunAkhir
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(string $urusan, string $subUrusan, string $indikator, int $tahunAwal, int $tahunAkhir)
    {
        $reportUrl = "https://sikokdata.bangkatengahkab.go.id/report/" 
            . rawurlencode($urusan) . "/" 
            . rawurlencode($subUrusan) . "/" 
            . rawurlencode($indikator) . "/" 
            . $tahunAwal . "/" 
            . $tahunAkhir;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->withOptions([
                'verify' => false,
            ])->timeout(30)->get($reportUrl);

            if ($response->successful()) {
                $filename = 'Data-Sektoral-' . \Illuminate\Support\Str::slug($indikator) . '-' . $tahunAwal . '-' . $tahunAkhir . '.pdf';

                return response($response->body(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

            Log::warning('DataSektoralService: Failed to fetch PDF report', ['status' => $response->status(), 'url' => $reportUrl]);
            abort(404, 'PDF report not found');
        } catch (\Throwable $e) {
            Log::error('DataSektoralService: Exception fetching PDF report', ['error' => $e->getMessage()]);
            abort(500, 'Error downloading PDF report');
        }
    }

    /**
     * Fetch data from the sikokdata API.
     *
     * @param string $url
     * @param bool $includeMetadata Whether to include jumlah, tahun_awal, tahun_akhir, downloadURL
     * @return array|null
     */
    protected function fetchFromApi(string $url, bool $includeMetadata = false): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning('DataSektoralService: API returned non-success status', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $json = $response->json();

            if (empty($json) || !isset($json['success']) || $json['success'] !== true) {
                Log::warning('DataSektoralService: API returned unsuccessful response', [
                    'url' => $url,
                    'response' => $json,
                ]);
                return null;
            }

            if ($includeMetadata) {
                return [
                    'jumlah' => $json['jumlah'] ?? 0,
                    'tahun_awal' => $json['tahun_awal'] ?? null,
                    'tahun_akhir' => $json['tahun_akhir'] ?? null,
                    'download_url' => $json['downloadURL'] ?? null,
                    'data' => $json['data'] ?? [],
                ];
            }

            return $json['data'] ?? [];
        } catch (\Throwable $e) {
            Log::error('DataSektoralService: Exception fetching API', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
