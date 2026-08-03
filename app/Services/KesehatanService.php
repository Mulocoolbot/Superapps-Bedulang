<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\HealthUnit;
use App\Models\HealthService;

class KesehatanService implements InformationServiceInterface
{
    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Kesehatan',
            'icon' => '🏥',
            'description' => 'Dashboard informasi & Pelayanan Kesehatan Kabupaten Bangka Tengah.',
            'filters' => [],
            'source' => 'Dinas Kesehatan Kabupaten Bangka Tengah',
            'source_url' => 'https://dinkes.bangkatengahkab.go.id/',
        ];
    }

    /**
     * Get the dashboard data dynamically from database.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request): array
    {
        $units = HealthUnit::with('services')->get();

        $totalUnits = $units->count();
        $totalRsud = $units->where('jenis', 'Rumah Sakit Daerah')->count();
        $totalPuskesmas = $units->where('jenis', 'Puskesmas')->count();
        $totalServices = HealthService::count();

        $availableJenisFaskes = $units->pluck('jenis')->unique()->values()->all();
        $availableKecamatan = $units->pluck('kecamatan')->filter()->unique()->values()->all();
        $availableKelompokUtama = HealthService::whereNotNull('kelompok_utama')
            ->pluck('kelompok_utama')
            ->unique()
            ->values()
            ->all();

        $formattedUnits = $units->map(function ($unit) {
            $servicesList = $unit->services->map(function ($s) {
                return [
                    'id' => $s->id,
                    'kode' => $s->kode_layanan,
                    'kelompok_utama' => $s->kelompok_utama,
                    'subkelompok' => $s->subkelompok,
                    'no_item' => $s->no_item,
                    'jenis_layanan' => $s->jenis_layanan,
                ];
            })->values()->all();

            $serviceNames = $unit->services->pluck('jenis_layanan')->values()->all();

            return [
                'id' => $unit->id,
                'unit_no' => $unit->unit_no,
                'nama' => $unit->nama,
                'jenis' => $unit->jenis,
                'alamat' => $unit->alamat,
                'desa' => $unit->desa,
                'kecamatan' => $unit->kecamatan,
                'wilayah' => $unit->wilayah,
                'kode_pos' => $unit->kode_pos,
                'total_layanan' => count($servicesList),
                'pelayanan' => $serviceNames,
                'services' => $servicesList,
            ];
        })->values()->all();

        $allServices = HealthService::with('healthUnit')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'kode_layanan' => $s->kode_layanan,
                'unit_nama' => $s->healthUnit->nama ?? '-',
                'unit_jenis' => $s->healthUnit->jenis ?? '-',
                'kecamatan' => $s->healthUnit->kecamatan ?? '-',
                'kelompok_utama' => $s->kelompok_utama ?? 'Layanan Kesehatan',
                'subkelompok' => $s->subkelompok,
                'no_item' => $s->no_item,
                'jenis_layanan' => $s->jenis_layanan,
            ];
        })->values()->all();

        return [
            'metadata' => [
                'source' => 'Dinas Kesehatan Kabupaten Bangka Tengah',
                'source_url' => 'https://dinkes.bangkatengahkab.go.id/',
                'updated_at' => date('d F Y'),
            ],
            'summary' => [
                'total_faskes' => $totalUnits,
                'total_rsud' => $totalRsud,
                'total_puskesmas' => $totalPuskesmas,
                'total_services' => $totalServices,
            ],
            'faskes_list' => $formattedUnits,
            'all_services' => $allServices,
            'available_jenis_faskes' => $availableJenisFaskes,
            'available_kecamatan' => $availableKecamatan,
            'available_kelompok_utama' => $availableKelompokUtama,
        ];
    }
}
