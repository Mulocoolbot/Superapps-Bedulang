<?php

namespace App\Services;

use Illuminate\Http\Request;

class InformasiPublikService implements InformationServiceInterface
{
    protected PerindagService $perindagService;
    protected PertanianService $pertanianService;

    public function __construct(PerindagService $perindagService, PertanianService $pertanianService)
    {
        $this->perindagService = $perindagService;
        $this->pertanianService = $pertanianService;
    }

    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Informasi Publik',
            'icon' => '📰',
            'description' => 'Portal Gabungan Informasi Publik Kabupaten Bangka Tengah (Harga Pasar, Sawit, UMKM, dan Unit Transfusi Darah).',
            'filters' => [],
            'source' => 'Pemerintah Kabupaten Bangka Tengah',
            'source_url' => 'https://bangkatengahkab.go.id/',
        ];
    }

    /**
     * Get the combined dashboard data.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request): array
    {
        $perindagData = $this->perindagService->getDashboardData($request);
        $sawitData = $this->pertanianService->getDashboardData($request);

        $bloodStocks = \App\Models\BloodStock::get()->map(function ($item) {
            return [
                'id' => $item->id,
                'component' => $item->component ?? 'Whole Blood (WB)',
                'blood_type' => $item->blood_type,
                'amount' => $item->amount,
                'unit' => $item->unit,
                'updated_at' => $item->updated_at ? $item->updated_at->format('d M Y, H:i') : null,
            ];
        });

        $latestLog = \App\Models\BloodStockLog::latest()->first();
        $latestStock = \App\Models\BloodStock::latest('updated_at')->first();
        
        $lastUpdatedAt = null;
        if ($latestLog && $latestStock) {
            $lastUpdatedAt = $latestLog->created_at > $latestStock->updated_at ? $latestLog->created_at : $latestStock->updated_at;
        } elseif ($latestLog) {
            $lastUpdatedAt = $latestLog->created_at;
        } elseif ($latestStock) {
            $lastUpdatedAt = $latestStock->updated_at;
        }

        $formattedLastUpdated = $lastUpdatedAt ? $lastUpdatedAt->translatedFormat('d F Y, H:i') . ' WIB' : date('d F Y, H:i') . ' WIB';

        $totalBloodStock = \App\Models\BloodStock::sum('amount');

        return [
            'metadata' => [
                'source' => 'Pemerintah Kabupaten Bangka Tengah',
                'source_url' => 'https://bangkatengahkab.go.id/',
                'updated_at' => date('d F Y'),
            ],
            'perindag' => $perindagData,
            'sawit' => $sawitData,
            'umkm' => [
                'status' => 'empty',
                'title' => 'Data UMKM',
                'message' => 'Data Usaha Mikro, Kecil, dan Menengah (UMKM) Kabupaten Bangka Tengah sedang dalam tahap pendataan dan pengintegrasian sistem.',
            ],
            'utd' => [
                'status' => 'active',
                'title' => 'Unit Transfusi Darah (UTD)',
                'total_stock' => $totalBloodStock,
                'last_updated' => $formattedLastUpdated,
                'components' => ['Whole Blood (WB)', 'Packed Red Blood (PRC)'],
                'blood_types' => ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'],
                'stocks' => $bloodStocks,
            ],
        ];
    }
}
