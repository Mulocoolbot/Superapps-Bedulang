<?php

namespace App\Services;

use Illuminate\Http\Request;

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
            'description' => 'Dashboard informasi Kesehatan Kabupaten Bangka Tengah.',
            'filters' => [],
            'source' => 'Dinas Kesehatan Kabupaten Bangka Tengah',
            'source_url' => 'https://dinkes.bangkatengahkab.go.id/',
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
        $faskesList = $this->getFaskesList();

        // Calculate card summaries
        $totalFaskes = count($faskesList);
        $totalRsud = collect($faskesList)->where('jenis', 'Rumah Sakit Daerah')->count();
        $totalPuskesmas = collect($faskesList)->where('jenis', 'Puskesmas')->count();
        $totalRsSwasta = collect($faskesList)->where('jenis', 'Rumah Sakit Swasta')->count();

        // Get unique lists of jenis faskes and jenis pelayanan for filter options
        $availableJenisFaskes = collect($faskesList)->pluck('jenis')->unique()->values()->all();
        
        $availableJenisPelayanan = collect($faskesList)
            ->pluck('pelayanan')
            ->flatten()
            ->unique()
            ->values()
            ->all();

        return [
            'metadata' => [
                'source' => 'Dinas Kesehatan Kabupaten Bangka Tengah',
                'source_url' => 'https://dinkes.bangkatengahkab.go.id/',
                'updated_at' => date('d F Y'),
            ],
            'summary' => [
                'total_faskes' => $totalFaskes,
                'total_rsud' => $totalRsud,
                'total_puskesmas' => $totalPuskesmas,
                'total_rs_swasta' => $totalRsSwasta,
            ],
            'faskes_list' => $faskesList,
            'available_jenis_faskes' => $availableJenisFaskes,
            'available_jenis_pelayanan' => $availableJenisPelayanan,
        ];
    }

    /**
     * Get static data of health facilities in Bangka Tengah.
     *
     * @return array
     */
    protected function getFaskesList(): array
    {
        return [
            [
                'id' => 1,
                'nama' => 'RSUD Drs. H. Abu Hanifah',
                'jenis' => 'Rumah Sakit Daerah',
                'kecamatan' => 'Koba',
                'alamat' => 'Jl. Bypass Koba, Koba, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Inap', 'Rawat Jalan', 'Poli Anak', 'Poli Gigi', 'Farmasi', 'Laboratorium', 'Radiologi', 'Bedah', 'Poli Penyakit Dalam'],
            ],
            [
                'id' => 2,
                'nama' => 'RS Pratama Bangka Tengah',
                'jenis' => 'Rumah Sakit Daerah',
                'kecamatan' => 'Sungai Selan',
                'alamat' => 'Jl. Raya Sungai Selan, Sungai Selan, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Inap', 'Rawat Jalan', 'Farmasi', 'Laboratorium'],
            ],
            [
                'id' => 3,
                'nama' => 'Puskesmas Koba',
                'jenis' => 'Puskesmas',
                'kecamatan' => 'Koba',
                'alamat' => 'Jl. Kenanga No. 12, Koba, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Jalan', 'KIA/KB', 'Farmasi', 'Poli Gigi', 'Laboratorium'],
            ],
            [
                'id' => 4,
                'nama' => 'Puskesmas Pangkalan Baru',
                'jenis' => 'Puskesmas',
                'kecamatan' => 'Pangkalan Baru',
                'alamat' => 'Jl. Raya Pangkalan Baru No. 45, Pangkalan Baru, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Jalan', 'KIA/KB', 'Poli Gigi', 'Farmasi'],
            ],
            [
                'id' => 5,
                'nama' => 'Puskesmas Namang',
                'jenis' => 'Puskesmas',
                'kecamatan' => 'Namang',
                'alamat' => 'Jl. Raya Namang No. 8, Namang, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Jalan', 'KIA/KB', 'Farmasi'],
            ],
            [
                'id' => 6,
                'nama' => 'Puskesmas Lubuk Besar',
                'jenis' => 'Puskesmas',
                'kecamatan' => 'Lubuk Besar',
                'alamat' => 'Jl. Raya Lubuk Besar No. 22, Lubuk Besar, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Inap', 'Rawat Jalan', 'KIA/KB', 'Farmasi'],
            ],
            [
                'id' => 7,
                'nama' => 'Puskesmas Simpang Katis',
                'jenis' => 'Puskesmas',
                'kecamatan' => 'Simpang Katis',
                'alamat' => 'Jl. Raya Simpang Katis No. 17, Simpang Katis, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Jalan', 'KIA/KB', 'Farmasi', 'Poli Gigi'],
            ],
            [
                'id' => 8,
                'nama' => 'Puskesmas Sungai Selan',
                'jenis' => 'Puskesmas',
                'kecamatan' => 'Sungai Selan',
                'alamat' => 'Jl. Raya Sungai Selan No. 5, Sungai Selan, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Inap', 'Rawat Jalan', 'KIA/KB', 'Farmasi', 'Laboratorium'],
            ],
            [
                'id' => 9,
                'nama' => 'RS Siloam Bangka',
                'jenis' => 'Rumah Sakit Swasta',
                'kecamatan' => 'Pangkalan Baru',
                'alamat' => 'Jl. Soekarno Hatta Km. 5, Pangkalan Baru, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Inap', 'Rawat Jalan', 'Poli Anak', 'Poli Gigi', 'Poli Penyakit Dalam', 'Bedah', 'Radiologi', 'Laboratorium', 'Farmasi'],
            ],
            [
                'id' => 10,
                'nama' => 'Klinik Pratama Medika Koba',
                'jenis' => 'Rumah Sakit Swasta',
                'kecamatan' => 'Koba',
                'alamat' => 'Jl. Jenderal Sudirman No. 88, Koba, Bangka Tengah',
                'pelayanan' => ['IGD', 'Rawat Jalan', 'Poli Gigi', 'Farmasi'],
            ],
        ];
    }
}
