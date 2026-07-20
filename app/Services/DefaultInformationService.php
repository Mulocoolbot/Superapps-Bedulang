<?php

namespace App\Services;

use Illuminate\Http\Request;

class DefaultInformationService implements InformationServiceInterface
{
    protected string $slug;
    protected array $metadata;

    public function __construct(string $slug, array $metadata)
    {
        $this->slug = $slug;
        $this->metadata = $metadata;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getDashboardData(Request $request): array
    {
        return [
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
            'price_trends' => [],
        ];
    }
}
