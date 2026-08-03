<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CctvService
{
    private static string $apiUrl = 'http://36.91.205.82:9308/monitoring/data';
    private static string $baseUrl = 'http://36.91.205.82:9308';

    /**
     * Get the list of live CCTV streams.
     */
    public static function getLiveCctvs(): array
    {
        return Cache::remember('cctv_live_list', 120, function () {
            try {
                $response = Http::timeout(5)->get(self::$apiUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && count($data) > 0) {
                        return array_map(function ($item) {
                            $httpUrl = $item['http_url'] ?? '';
                            $fullStreamUrl = str_starts_with($httpUrl, 'http')
                                ? $httpUrl
                                : self::$baseUrl . '/' . ltrim($httpUrl, '/');

                            return [
                                'id'       => $item['id'] ?? null,
                                'kode'     => $item['kode'] ?? '',
                                'name'     => strtoupper($item['nama'] ?? 'CCTV'),
                                'ip'       => '36.91.205.82',
                                'stream'   => $fullStreamUrl,
                                'lat'      => isset($item['latitude']) ? (float) $item['latitude'] : 0,
                                'lng'      => isset($item['longitude']) ? (float) $item['longitude'] : 0,
                                'status'   => $item['status'] ?? 'Online',
                                'alamat'   => $item['alamat'] ?? '',
                                'location' => $item['keterangan'] ?? ($item['alamat'] ?? ''),
                            ];
                        }, $data);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("CctvService: Failed to fetch live data from " . self::$apiUrl . ": " . $e->getMessage());
            }

            // Fallback list pointing directly to 36.91.205.82 streams
            return self::getFallbackCctvs();
        });
    }

    /**
     * Fallback CCTV list if public API is temporarily unreachable.
     */
    public static function getFallbackCctvs(): array
    {
        return [
            [
                'id'       => 3,
                'kode'     => 'CAM001',
                'name'     => 'GERBANG KOTA KOBA',
                'ip'       => '36.91.205.82',
                'stream'   => 'http://36.91.205.82:9308/streams/cam001/index.m3u8',
                'lat'      => -2.4687320,
                'lng'      => 106.3620528,
                'status'   => 'Online',
                'alamat'   => 'Kelurahan Arung Dalam',
                'location' => 'Tugu Kota Koba',
            ],
            [
                'id'       => 4,
                'kode'     => 'CAM002',
                'name'     => 'ALUN-ALUN KOBA',
                'ip'       => '36.91.205.82',
                'stream'   => 'http://36.91.205.82:9308/streams/cam002/index.m3u8',
                'lat'      => -2.4898127,
                'lng'      => 106.4174033,
                'status'   => 'Online',
                'alamat'   => 'Taman Alun-alun Kota Koba',
                'location' => 'Taman alun-alun',
            ],
            [
                'id'       => 5,
                'kode'     => 'CAM003',
                'name'     => 'BUNDARAN KOTA KOBA',
                'ip'       => '36.91.205.82',
                'stream'   => 'http://36.91.205.82:9308/streams/cam003/index.m3u8',
                'lat'      => -2.4897556,
                'lng'      => 106.4087327,
                'status'   => 'Online',
                'alamat'   => 'Depan Polsek Koba',
                'location' => 'Bundaran Kota Koba',
            ],
            [
                'id'       => 6,
                'kode'     => 'CAM004',
                'name'     => 'SIMPANG NAMANG',
                'ip'       => '36.91.205.82',
                'stream'   => 'http://36.91.205.82:9308/streams/cam004/index.m3u8',
                'lat'      => -2.3218366,
                'lng'      => 106.1877915,
                'status'   => 'Online',
                'alamat'   => 'Desa Namang',
                'location' => 'Simpang Namang',
            ],
            [
                'id'       => 7,
                'kode'     => 'CAM005',
                'name'     => 'KECAMATAN PANGKALAN BARU',
                'ip'       => '36.91.205.82',
                'stream'   => 'http://36.91.205.82:9308/streams/cam005/index.m3u8',
                'lat'      => -2.1621020,
                'lng'      => 106.1324697,
                'status'   => 'Online',
                'alamat'   => 'Kampung Dul',
                'location' => 'Kampung Dul',
            ],
            [
                'id'       => 8,
                'kode'     => 'CAM006',
                'name'     => 'GERBANG PEMDA BANGKA TENGAH',
                'ip'       => '36.91.205.82',
                'stream'   => 'http://36.91.205.82:9308/streams/cam006/index.m3u8',
                'lat'      => -2.4886475,
                'lng'      => 106.4186422,
                'status'   => 'Online',
                'alamat'   => 'Depan RSUD Bangka Tengah',
                'location' => 'Gerbang Pemda',
            ],
        ];
    }
}
