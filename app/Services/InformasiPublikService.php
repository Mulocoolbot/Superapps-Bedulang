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

        $bloodStocks = \App\Models\BloodStock::orderBy('blood_type')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'blood_type' => $item->blood_type,
                'amount' => $item->amount,
                'unit' => $item->unit,
                'updated_at' => $item->updated_at ? $item->updated_at->format('d M Y, H:i') : null,
            ];
        });

        $totalBloodStock = \App\Models\BloodStock::sum('amount');

        // Fetch recent blood stock logs (latest 30)
        $bloodLogs = \App\Models\BloodStockLog::with('updater')
            ->latest()
            ->take(30)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'blood_type' => $log->blood_type,
                    'old_amount' => $log->old_amount,
                    'new_amount' => $log->new_amount,
                    'delta' => $log->delta,
                    'updater_name' => $log->updater ? $log->updater->name : 'System Admin',
                    'updated_at' => $log->created_at ? $log->created_at->format('d M Y, H:i') : null,
                ];
            });

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
                'stocks' => $bloodStocks,
                'logs' => $bloodLogs,
            ],
        ];
    }
}
