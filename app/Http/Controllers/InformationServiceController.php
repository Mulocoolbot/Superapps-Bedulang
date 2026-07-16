<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class InformationServiceController extends Controller
{
    protected function buildPriceTrends(Request $request, $slug): array
    {
        if ($slug !== 'perindagkop-ukm') {
            return [];
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->get('https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga');
        } catch (\Throwable) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $records = $response->json() ?? [];
        if (! is_array($records)) {
            return [];
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $pasarId = $request->query('pasar_id');

        $filtered = collect($records)
            ->filter(function ($item) use ($startDate, $endDate, $pasarId) {
                if (! is_array($item)) {
                    return false;
                }

                $date = $item['tanggal'] ?? $item['tanggal_format'] ?? null;
                if (! $date) {
                    return false;
                }

                if ($startDate && $date < $startDate) {
                    return false;
                }

                if ($endDate && $date > $endDate) {
                    return false;
                }

                if ($pasarId !== null && $pasarId !== '' && ($item['pasar_id'] ?? null) != $pasarId) {
                    return false;
                }

                return true;
            })
            ->values();

        if ($filtered->isEmpty()) {
            return [];
        }

        $latestDate = $filtered->max('tanggal');
        $latestRecords = $filtered->filter(fn ($item) => ($item['tanggal'] ?? null) === $latestDate)->values();
        $previousRecords = $filtered->filter(fn ($item) => ($item['tanggal'] ?? null) !== $latestDate)->values();

        $results = [];
        $groupedLatest = $latestRecords->groupBy(fn ($item) => ($item['komoditi_id'] ?? 'unknown') . ':' . ($item['nama_barang'] ?? 'unknown'));

        foreach ($groupedLatest as $items) {
            $firstItem = $items->first();
            $commodityName = $firstItem['nama_barang'] ?? 'Tidak diketahui';
            $commodityId = $firstItem['komoditi_id'] ?? null;

            $currentMarketEntries = $items->filter(fn ($item) => ! empty($item['nominal']) && is_numeric($item['nominal']))->values();
            $currentAverage = $currentMarketEntries->isEmpty() ? 0 : round($currentMarketEntries->avg('nominal'), 2);
            $currentCount = $currentMarketEntries->count();

            $previousEntries = $previousRecords->filter(function ($item) use ($commodityName, $commodityId) {
                return ($item['nama_barang'] ?? null) === $commodityName && ($item['komoditi_id'] ?? null) == $commodityId;
            })->values();

            $previousLatestDate = $previousEntries->max('tanggal');
            $previousMarketEntries = $previousEntries
                ->filter(fn ($item) => ($item['tanggal'] ?? null) === $previousLatestDate && ! empty($item['nominal']) && is_numeric($item['nominal']))
                ->values();

            $previousAverage = $previousMarketEntries->isEmpty() ? null : round($previousMarketEntries->avg('nominal'), 2);

            if ($previousAverage === null || $currentAverage === 0) {
                continue;
            }

            $difference = round($currentAverage - $previousAverage, 2);
            $percentage = round(($difference / $previousAverage) * 100, 2);
            $status = $difference > 0 ? 'naik' : ($difference < 0 ? 'turun' : 'tetap');

            $results[] = [
                'commodity' => $commodityName,
                'price_i' => $previousAverage,
                'price_ii' => $currentAverage,
                'selisih' => $difference,
                'persentase' => $percentage,
                'status' => $status,
                'date' => $latestDate,
                'market_count' => $currentCount,
            ];
        }

        usort($results, fn ($a, $b) => $b['persentase'] <=> $a['persentase']);

        return $results;
    }

    public function show($slug)
    {
        $services = [
            'pemerintahan' => [
                'name' => 'Pemerintahan',
                'icon' => '🏛️',
                'description' => 'Dashboard informasi Pemerintahan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Harga Cabe', 'value' => 'harga-cabe'],
                    ['label' => 'Harga Terigu', 'value' => 'harga-terigu'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                ],
            ],
            'pendidikan' => [
                'name' => 'Pendidikan',
                'icon' => '🎓',
                'description' => 'Dashboard informasi Pendidikan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Jumlah Siswa', 'value' => 'jumlah-siswa'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'kesehatan' => [
                'name' => 'Kesehatan',
                'icon' => '🏥',
                'description' => 'Dashboard informasi Kesehatan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'perindagkop-ukm' => [
                'name' => 'Perindagkop-UKM',
                'icon' => '📈',
                'description' => 'Dashboard informasi Perindagkop dan UKM Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Telur', 'value' => 'harga-cabe'],
                    ['label' => 'Terigu', 'value' => 'harga-terigu'],
                    ['label' => 'Minyak Goreng', 'value' => 'gender'],
                ],
            ],
            'hukum' => [
                'name' => 'Hukum',
                'icon' => '⚖️',
                'description' => 'Dashboard informasi Hukum Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kasus', 'value' => 'kasus'],
                    ['label' => 'Gender', 'value' => 'gender'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                ],
            ],
            'sosial' => [
                'name' => 'Sosial',
                'icon' => '🤝',
                'description' => 'Dashboard informasi Sosial Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'kependudukan' => [
                'name' => 'Kependudukan',
                'icon' => '🪪',
                'description' => 'Dashboard informasi Kependudukan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'kepegawaian' => [
                'name' => 'Kepegawaian',
                'icon' => '👔',
                'description' => 'Dashboard informasi Kepegawaian Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Gender', 'value' => 'gender'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                ],
            ],
            'lingkungan' => [
                'name' => 'Lingkungan',
                'icon' => '🌿',
                'description' => 'Dashboard informasi Lingkungan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Harga Cabe', 'value' => 'harga-cabe'],
                ],
            ],
            'geografis' => [
                'name' => 'Geografis',
                'icon' => '🗺️',
                'description' => 'Dashboard informasi Geografis Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                    ['label' => 'Harga Terigu', 'value' => 'harga-terigu'],
                ],
            ],
            'pariwisata' => [
                'name' => 'Pariwisata',
                'icon' => '🏖️',
                'description' => 'Dashboard informasi Pariwisata Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Harga Cabe', 'value' => 'harga-cabe'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'data-sektoral' => [
                'name' => 'Data Sektoral',
                'icon' => '📊',
                'description' => 'Dashboard informasi Data Sektoral Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Harga Terigu', 'value' => 'harga-terigu'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
        ];

        if (! isset($services[$slug])) {
            abort(404);
        }

        $priceTrends = $this->buildPriceTrends(request(), $slug);
        $commodityChart = [];

        if ($slug === 'perindagkop-ukm') {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get('https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga');

                if ($response->successful()) {
                    $records = $response->json() ?? [];
                    if (is_array($records)) {
                        $filtered = collect($records)
                            ->filter(function ($item) {
                                return is_array($item) && ! empty($item['komoditi_id']);
                            })
                            ->values();

                        $grouped = $filtered->groupBy(fn ($item) => $item['komoditi_id'] ?? 'unknown');
                        $commodityChart = [
                            'labels' => $grouped->keys()->map(fn ($key) => 'Komoditi ' . $key)->values()->all(),
                            'series' => $grouped->map(fn ($items) => $items->count())->values()->all(),
                        ];
                    }
                }
            } catch (\Throwable) {
                $commodityChart = [];
            }
        }

        $dashboard = [
            'summary' => [
                ['title' => 'Jumlah Data', 'value' => '1.248', 'meta' => 'Data terupdate hari ini'],
                ['title' => 'Layanan Aktif', 'value' => '42', 'meta' => 'Transaksi berjalan'],
                ['title' => 'Kepuasan', 'value' => '94%', 'meta' => 'Rata-rata pengguna'],
            ],
            'chart' => [
                'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                'series' => [18, 24, 19, 27, 31, 29, 35],
            ],
            'topData' => [
                ['title' => 'Data Terbanyak', 'value' => '412', 'note' => 'Laporan harian'],
                ['title' => 'Permintaan', 'value' => '89', 'note' => 'Dalam 24 jam'],
            ],
            'info' => [
                'title' => 'Status Sinkronisasi',
                'message' => 'Integrasi dengan sumber data OPD akan segera dipublikasikan secara real time.',
            ],
            'rows' => [
                ['name' => 'Layanan Digital', 'value' => '320', 'date' => '2026-07-12', 'status' => 'Aktif'],
                ['name' => 'Dashboard OPD', 'value' => '184', 'date' => '2026-07-11', 'status' => 'Baru'],
                ['name' => 'Peta Data', 'value' => '97', 'date' => '2026-07-10', 'status' => 'Rutin'],
            ],
            'price_trends' => $priceTrends,
            'commodity_chart' => $commodityChart,
        ];

        return Inertia::render('Front/Services/Show', [
            'service' => [
                ...$services[$slug],
                'slug' => $slug,
            ],
            'dashboard' => $dashboard,
        ]);
    }
}
