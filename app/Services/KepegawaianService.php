<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class KepegawaianService implements InformationServiceInterface
{
    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Kepegawaian',
            'icon' => '👔',
            'description' => 'Dashboard informasi Kepegawaian & Aparatur Sipil Negara Kabupaten Bangka Tengah.',
            'filters' => [],
            'source' => 'BKPSDMD Kabupaten Bangka Tengah',
            'source_url' => 'https://apiipeelawann.bangkatengahkab.go.id/v5/25jsonpegawai.php',
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
        return Cache::remember('kepegawaian_dashboard_data', 1800, function () {
            $parsedData = $this->fetchDataFromApi();
            if ($parsedData) {
                return $parsedData;
            }
            return $this->getMockData();
        });
    }

    /**
     * Fetch and parse live data from the official Kepegawaian API.
     *
     * @return array|null
     */
    protected function fetchDataFromApi(): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->timeout(10)->get('https://apiipeelawann.bangkatengahkab.go.id/v5/25jsonpegawai.php');

            if (!$response->successful()) {
                return null;
            }

            $rawPegawai = $response->json();
            if (empty($rawPegawai) || !is_array($rawPegawai)) {
                return null;
            }

            return $this->processPegawaiData($rawPegawai);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Process raw JSON array to calculate statistics and clean records.
     *
     * @param array $rawPegawai
     * @return array
     */
    protected function processPegawaiData(array $rawPegawai): array
    {
        $totalPns = 0;
        $totalP3k = 0;
        $totalP3kPw = 0;
        $grandTotal = 0;
        $processedRecords = [];

        foreach ($rawPegawai as $index => $item) {
            $pns = (int) ($item['jumlah_pns'] ?? 0);
            $p3k = (int) ($item['jumlah_p3k'] ?? 0);
            $p3kPw = (int) ($item['jumlah_p3k_pw'] ?? 0);
            $total = (int) ($item['total'] ?? ($pns + $p3k + $p3kPw));

            $totalPns += $pns;
            $totalP3k += $p3k;
            $totalP3kPw += $p3kPw;
            $grandTotal += $total;

            $processedRecords[] = [
                'id' => $index + 1,
                'nama_upd' => trim($item['nama_upd'] ?? 'OPD Tidak Diketahui'),
                'kode_upd' => trim($item['kode'] ?? ''), // Hidden in frontend display
                'jumlah_pns' => $pns,
                'jumlah_p3k' => $p3k,
                'jumlah_p3k_pw' => $p3kPw,
                'total' => $total,
            ];
        }

        // Sort by total descending by default
        usort($processedRecords, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Re-index IDs after sorting
        foreach ($processedRecords as $i => &$record) {
            $record['id'] = $i + 1;
        }

        return [
            'metadata' => [
                'source' => 'BKPSDMD Kabupaten Bangka Tengah',
                'updated_at' => date('d F Y'),
                'total_pns' => $totalPns,
                'total_p3k' => $totalP3k,
                'total_p3k_pw' => $totalP3kPw,
                'grand_total' => $grandTotal,
                'total_upd' => count($processedRecords),
            ],
            'records' => $processedRecords,
        ];
    }

    /**
     * Fallback mock data matching real entries from the API.
     *
     * @return array
     */
    protected function getMockData(): array
    {
        $mockRaw = [
            [
                "nama_upd" => "Badan Kepegawaian dan Pengembangan Sumber Daya Manusia Daerah",
                "kode" => "19.07",
                "jumlah_pns" => 33,
                "jumlah_p3k" => 8,
                "jumlah_p3k_pw" => 9,
                "total" => 50
            ],
            [
                "nama_upd" => "Badan Kesatuan Bangsa dan Politik",
                "kode" => "19.32",
                "jumlah_pns" => 17,
                "jumlah_p3k" => 5,
                "jumlah_p3k_pw" => 4,
                "total" => 26
            ],
            [
                "nama_upd" => "Badan Penanggulangan Bencana Daerah",
                "kode" => "19.34",
                "jumlah_pns" => 11,
                "jumlah_p3k" => 21,
                "jumlah_p3k_pw" => 1,
                "total" => 33
            ],
            [
                "nama_upd" => "Badan Pengelolaan Keuangan dan Aset Daerah",
                "kode" => "19.05",
                "jumlah_pns" => 39,
                "jumlah_p3k" => 5,
                "jumlah_p3k_pw" => 7,
                "total" => 51
            ],
            [
                "nama_upd" => "Badan Pengelolaan Pajak dan Retribusi Daerah",
                "kode" => "19.06",
                "jumlah_pns" => 22,
                "jumlah_p3k" => 5,
                "jumlah_p3k_pw" => 22,
                "total" => 49
            ],
            [
                "nama_upd" => "Badan Perencanaan Pembangunan dan Penelitian Pengembangan Daerah",
                "kode" => "19.04",
                "jumlah_pns" => 37,
                "jumlah_p3k" => 6,
                "jumlah_p3k_pw" => 10,
                "total" => 53
            ],
            [
                "nama_upd" => "Dinas Kearsipan dan Perpustakaan",
                "kode" => "19.25",
                "jumlah_pns" => 19,
                "jumlah_p3k" => 10,
                "jumlah_p3k_pw" => 8,
                "total" => 37
            ],
            [
                "nama_upd" => "Dinas Kebudayaan, Pariwisata, Kepemudaan dan Olah Raga",
                "kode" => "19.13",
                "jumlah_pns" => 42,
                "jumlah_p3k" => 3,
                "jumlah_p3k_pw" => 14,
                "total" => 59
            ],
            [
                "nama_upd" => "Dinas Kependudukan dan Pencatatan Sipil",
                "kode" => "19.18",
                "jumlah_pns" => 21,
                "jumlah_p3k" => 14,
                "jumlah_p3k_pw" => 27,
                "total" => 62
            ],
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
            [
                "nama_upd" => "Dinas Lingkungan Hidup",
                "kode" => "19.12",
                "jumlah_pns" => 25,
                "jumlah_p3k" => 7,
                "jumlah_p3k_pw" => 35,
                "total" => 67
            ],
        ];

        return $this->processPegawaiData($mockRaw);
    }
}
