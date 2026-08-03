<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PendidikanService implements InformationServiceInterface
{
    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Pendidikan',
            'icon' => '🎓',
            'description' => 'Dashboard informasi Pendidikan Kabupaten Bangka Tengah.',
            'filters' => [],
            'source' => 'Dinas Pendidikan Kabupaten Bangka Tengah',
            'source_url' => 'https://dindik.bangkatengahkab.go.id/',
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
        $schoolsList = $this->getSchoolsList();

        // Calculate totals dynamically per jenjang
        $totalPaudSchools = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'PAUD' || str_contains($s['jenjang'], 'PAUD') || str_contains($s['jenjang'], 'TK'))->count();
        $totalSdSchools = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'SD')->count();
        $totalSmpSchools = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'SMP')->count();

        // Student totals
        $totalPaudStudents = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'PAUD' || str_contains($s['jenjang'], 'PAUD') || str_contains($s['jenjang'], 'TK'))->sum('jumlah_siswa');
        $totalSdStudents = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'SD')->sum('jumlah_siswa');
        $totalSmpStudents = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'SMP')->sum('jumlah_siswa');

        // Teacher totals
        $totalPaudTeachers = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'PAUD' || str_contains($s['jenjang'], 'PAUD') || str_contains($s['jenjang'], 'TK'))->sum('jumlah_guru');
        $totalSdTeachers = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'SD')->sum('jumlah_guru');
        $totalSmpTeachers = collect($schoolsList)->filter(fn($s) => $s['jenjang'] === 'SMP')->sum('jumlah_guru');

        $availableKecamatans = collect($schoolsList)->pluck('kecamatan')->filter()->unique()->values()->all();
        $availableJenjangs = collect($schoolsList)->pluck('jenjang')->filter()->unique()->values()->all();
        if (empty($availableJenjangs)) {
            $availableJenjangs = ['PAUD', 'SD', 'SMP'];
        }
        return [
            'metadata' => [
                'source' => 'Dinas Pendidikan Kabupaten Bangka Tengah',
                'source_url' => 'https://dindik.bangkatengahkab.go.id/',
                'updated_at' => date('d F Y'),
            ],
            'summary' => [
                // Schools counts
                'total_paud_schools' => $totalPaudSchools,
                'total_sd_schools' => $totalSdSchools,
                'total_smp_schools' => $totalSmpSchools,
                // Students counts
                'total_paud_students' => $totalPaudStudents,
                'total_sd_students' => $totalSdStudents,
                'total_smp_students' => $totalSmpStudents,
                // Teachers counts
                'total_paud_teachers' => $totalPaudTeachers,
                'total_sd_teachers' => $totalSdTeachers,
                'total_smp_teachers' => $totalSmpTeachers,
            ],
            'schools_list' => $schoolsList,
            'available_kecamatans' => $availableKecamatans,
            'available_jenjangs' => $availableJenjangs,
        ];
    }

    /**
     * Get data of schools from Dinas Pendidikan API with caching and fallback.
     *
     * @return array
     */
    protected function getSchoolsList(): array
    {
        return Cache::remember('pendidikan_api_datasekolah', 3600, function () {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(10)
                    ->get('https://dindik.bangkatengahkab.go.id/api/datasekolah');

                if ($response->successful()) {
                    $json = $response->json();
                    $items = $json['data'] ?? [];

                    if (!empty($items) && is_array($items)) {
                        return array_values(array_map(function ($item, $index) {
                            $name = trim($item['nama'] ?? '');
                            $nameUpper = strtoupper($name);

                            // Determine status (Negeri vs Swasta)
                            $isNegeri = str_contains($nameUpper, 'NEGERI') ||
                                        str_contains($nameUpper, 'SDN ') ||
                                        str_contains($nameUpper, 'SMPN ') ||
                                        str_contains($nameUpper, 'TKN ') ||
                                        str_starts_with($nameUpper, 'SDN') ||
                                        str_starts_with($nameUpper, 'SMPN') ||
                                        str_starts_with($nameUpper, 'TKN');

                            // Normalize jenjang
                            $rawJenjang = trim($item['jenjang'] ?? 'PAUD');
                            if (str_contains(strtoupper($rawJenjang), 'PAUD') || str_contains(strtoupper($rawJenjang), 'TK')) {
                                $jenjang = 'PAUD';
                            } elseif (str_contains(strtoupper($rawJenjang), 'SD')) {
                                $jenjang = 'SD';
                            } elseif (str_contains(strtoupper($rawJenjang), 'SMP')) {
                                $jenjang = 'SMP';
                            } else {
                                $jenjang = $rawJenjang;
                            }

                            // Normalize kecamatan
                            $rawKec = trim($item['kecamatan'] ?? '');
                            $kec = $this->normalizeKecamatan($rawKec);

                            // Clean address formatting (replace tabs with commas)
                            $alamatRaw = $item['alamat'] ?? '';
                            $alamatClean = trim(preg_replace('/\s+/', ' ', str_replace("\t", ', ', $alamatRaw)));

                            return [
                                'id' => $item['school_id'] ?? ($index + 1),
                                'nama' => $name,
                                'jenjang' => $jenjang,
                                'raw_jenjang' => $rawJenjang,
                                'kecamatan' => $kec,
                                'desa' => trim($item['desa'] ?? ''),
                                'alamat' => $alamatClean,
                                'jumlah_siswa' => (int) ($item['siswa'] ?? 0),
                                'jumlah_guru' => (int) ($item['guru'] ?? 0),
                                'foto' => $item['foto'] ?? null,
                                'lat' => (float) ($item['lat'] ?? 0),
                                'long' => (float) ($item['long'] ?? 0),
                            ];
                        }, $items, array_keys($items)));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error fetching datasekolah API: ' . $e->getMessage());
            }

            return $this->getFallbackSchoolsList();
        });
    }

    /**
     * Normalize kecamatan name to standard display title.
     */
    private function normalizeKecamatan(string $kec): string
    {
        if (strcasecmp($kec, 'sungaiselan') === 0 || strcasecmp($kec, 'sungai selan') === 0) return 'Sungai Selan';
        if (strcasecmp($kec, 'simpangkatis') === 0 || strcasecmp($kec, 'simpang katis') === 0) return 'Simpang Katis';
        if (strcasecmp($kec, 'pangkalanbaru') === 0 || strcasecmp($kec, 'pangkalan baru') === 0) return 'Pangkalan Baru';
        if (strcasecmp($kec, 'lubukbesar') === 0 || strcasecmp($kec, 'lubuk besar') === 0) return 'Lubuk Besar';
        if (strcasecmp($kec, 'koba') === 0) return 'Koba';
        if (strcasecmp($kec, 'namang') === 0) return 'Namang';
        if (empty($kec)) return 'Bangka Tengah';

        return ucwords(strtolower($kec));
    }

    /**
     * Fallback static data of schools in Bangka Tengah.
     *
     * @return array
     */
    protected function getFallbackSchoolsList(): array
    {
        return [
            [
                'id' => 1,
                'nama' => 'TK Negeri Pembina Koba',
                'jenjang' => 'PAUD',
                'status' => 'Negeri',
                'kecamatan' => 'Koba',
                'jumlah_siswa' => 45,
                'jumlah_guru' => 5,
            ],
            [
                'id' => 2,
                'nama' => 'TK Aisyiyah Bustanul Athfal',
                'jenjang' => 'PAUD',
                'status' => 'Swasta',
                'kecamatan' => 'Pangkalan Baru',
                'jumlah_siswa' => 35,
                'jumlah_guru' => 4,
            ],
        ];
    }
}
