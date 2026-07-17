<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
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

        if ($slug === 'perindagkop-ukm') {
            $startDate = request('start_date');
            $endDate = request('end_date');
            $pasarId = request('pasar_id');

            // Always fetch the absolute latest date available from the API
            $latestDate = '2026-07-17'; // default fallback
            try {
                $latestResponse = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get('https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga');
                if ($latestResponse->successful()) {
                    $latestData = $latestResponse->json();
                    if (!empty($latestData['data']) && is_array($latestData['data'])) {
                        $latestDate = $latestData['data'][0]['tanggal'];
                    }
                }
            } catch (\Throwable) {
                // Keep default
            }

            // Find D_latest and D_prev if not specified
            if (!$startDate || !$endDate) {
                $endDate = $latestDate;
                $startDate = date('Y-m-d', strtotime($latestDate . ' -1 day'));
            } else {
                // Ensure startDate is at least -1 day from latestDate
                $maxStartDate = date('Y-m-d', strtotime($latestDate . ' -1 day'));
                if ($startDate > $maxStartDate) {
                    $startDate = $maxStartDate;
                }
                // Ensure endDate is strictly greater than startDate, up to latestDate
                if ($endDate <= $startDate) {
                    $endDate = date('Y-m-d', strtotime($startDate . ' +1 day'));
                    if ($endDate > $latestDate) {
                        $endDate = $latestDate;
                        $startDate = date('Y-m-d', strtotime($latestDate . ' -1 day'));
                    }
                }
            }

            // Generate cache key based on query params
            $cacheKey = "perindagkop_api_{$startDate}_{$endDate}_" . ($pasarId ?? 'all');
            
            $dashboard = Cache::remember($cacheKey, 600, function () use ($startDate, $endDate, $pasarId, $latestDate) {
                $rawItems = [];

                if ($pasarId) {
                    // Fetch for single market
                    try {
                        $response = Http::withOptions(['verify' => false])
                            ->timeout(15)
                            ->get("https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga/{$startDate}/{$endDate}/{$pasarId}");
                        if ($response->successful()) {
                            $resData = $response->json();
                            if (!empty($resData['data']) && is_array($resData['data'])) {
                                $rawItems = $resData['data'];
                            }
                        }
                    } catch (\Throwable) {
                        $rawItems = [];
                    }
                } else {
                    // Fetch for active markets only
                    $activePasars = [1, 2, 3, 4, 5, 6, 7];
                    foreach ($activePasars as $p) {
                        try {
                            $response = Http::withOptions(['verify' => false])
                                ->timeout(15)
                                ->get("https://disperindagkop-ukm.bangkatengahkab.go.id/api/updateharga/{$startDate}/{$endDate}/{$p}");
                            if ($response->successful()) {
                                $resData = $response->json();
                                if (!empty($resData['data']) && is_array($resData['data'])) {
                                    $rawItems = array_merge($rawItems, $resData['data']);
                                }
                            }
                        } catch (\Throwable) {
                            // Continue on individual market failure
                        }
                    }
                }

                // Aggregate data by commodity
                $commodityGroups = [];
                foreach ($rawItems as $item) {
                    $name = $item['nama_barang'] ?? null;
                    if (!$name) continue;

                    if (!isset($commodityGroups[$name])) {
                        $commodityGroups[$name] = [
                            'name' => $name,
                            'price_i_sum' => 0,
                            'price_i_count' => 0,
                            'price_ii_sum' => 0,
                            'price_ii_count' => 0,
                        ];
                    }

                    // Check if market reported price_i
                    if (isset($item['price_i']) && $item['price_i'] > 0) {
                        $commodityGroups[$name]['price_i_sum'] += $item['price_i'];
                        $commodityGroups[$name]['price_i_count']++;
                    }

                    // Check if market reported price_ii
                    if (isset($item['price_ii']) && $item['price_ii'] > 0) {
                        $commodityGroups[$name]['price_ii_sum'] += $item['price_ii'];
                        $commodityGroups[$name]['price_ii_count']++;
                    }
                    
                    // Fallback for single-date queries or if the API only has nominal
                    if (!isset($item['price_i']) && isset($item['nominal']) && $item['nominal'] > 0) {
                        $commodityGroups[$name]['price_ii_sum'] += $item['nominal'];
                        $commodityGroups[$name]['price_ii_count']++;
                    }
                }

                $priceTrends = [];
                $chartLabels = [];
                $chartSeriesI = [];
                $chartSeriesII = [];
                
                foreach ($commodityGroups as $name => $g) {
                    $avgPriceI = $g['price_i_count'] > 0 ? round($g['price_i_sum'] / $g['price_i_count']) : 0;
                    $avgPriceII = $g['price_ii_count'] > 0 ? round($g['price_ii_sum'] / $g['price_ii_count']) : 0;
                    
                    // If either price on the start date or end date is missing (0 or below)
                    if ($avgPriceI <= 0 || $avgPriceII <= 0) {
                        $priceTrends[] = [
                            'commodity' => $name,
                            'price_i' => $avgPriceI > 0 ? 'Rp ' . number_format($avgPriceI, 0, ',', '.') : 'data tidak tersedia',
                            'price_ii' => $avgPriceII > 0 ? 'Rp ' . number_format($avgPriceII, 0, ',', '.') : 'data tidak tersedia',
                            'selisih' => '-',
                            'status' => '-',
                            'raw_price_i' => $avgPriceI,
                            'raw_price_ii' => $avgPriceII,
                        ];
                    } else {
                        $diff = $avgPriceII - $avgPriceI;
                        $status = 'tetap';
                        if ($diff > 0) {
                            $status = 'naik';
                        } elseif ($diff < 0) {
                            $status = 'turun';
                        }

                        $priceTrends[] = [
                            'commodity' => $name,
                            'price_i' => 'Rp ' . number_format($avgPriceI, 0, ',', '.'),
                            'price_ii' => 'Rp ' . number_format($avgPriceII, 0, ',', '.'),
                            'selisih' => $diff,
                            'status' => $status,
                            'raw_price_i' => $avgPriceI,
                            'raw_price_ii' => $avgPriceII,
                        ];
                    }
                }

                // Sort commodities by price_ii descending to show top/main items
                usort($priceTrends, function ($a, $b) {
                    return $b['raw_price_ii'] <=> $a['raw_price_ii'];
                });

                // Populate ALL commodities in the chart
                foreach ($priceTrends as $c) {
                    $chartLabels[] = $c['commodity'];
                    $chartSeriesI[] = $c['raw_price_i'];
                    $chartSeriesII[] = $c['raw_price_ii'];
                }

                $latestFormattedDate = date('d F Y', strtotime($endDate));
                $translatedMonths = [
                    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
                ];
                $latestFormattedDate = strtr($latestFormattedDate, $translatedMonths);

                return [
                    'metadata' => [
                        'total' => count($priceTrends),
                        'updated_at' => $latestFormattedDate,
                        'latest_date' => $latestDate,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'pasar_id' => $pasarId,
                        'source' => 'Disperindagkop-UKM Bangka Tengah',
                    ],
                    'info' => [
                        'title' => 'Sinkronisasi Harga Pasar',
                        'message' => 'Data menampilkan rata-rata harga pasar Koba, Sungaiselan, Kayu Besi, dll. dibanding hari sebelumnya.',
                    ],
                    'commodity_chart' => [
                        'labels' => $chartLabels,
                        'series_i' => $chartSeriesI,
                        'series_ii' => $chartSeriesII,
                    ],
                    'price_trends' => $priceTrends,
                ];
            });
        } else {
            $priceTrends = $this->buildPriceTrends(request(), $slug);
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
            ];
        }

        return Inertia::render('Front/Services/Show', [
            'service' => [
                ...$services[$slug],
                'slug' => $slug,
            ],
            'dashboard' => $dashboard,
        ]);
    }
}
