<?php

namespace App\Services;

use App\Models\KependudukanImportLog;
use App\Models\KependudukanRecord;
use Illuminate\Http\Request;

class KependudukanService implements InformationServiceInterface
{
    /**
     * Get the service metadata.
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'name' => 'Kependudukan',
            'icon' => '🪪',
            'description' => 'Dashboard Data & Demografi Kependudukan Kabupaten Bangka Tengah.',
            'filters' => [
                ['label' => 'Periode Semester', 'value' => 'semester'],
                ['label' => 'Kecamatan', 'value' => 'kecamatan'],
                ['label' => 'Desa / Kelurahan', 'value' => 'desa'],
            ],
            'source' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Bangka Tengah',
            'source_url' => 'https://dukcapil.bangkatengahkab.go.id/',
            'updated_at' => date('d F Y'),
        ];
    }

    /**
     * Get the complete dashboard dataset from database.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request): array
    {
        // Get all unique period keys from import logs in DB
        $logs = KependudukanImportLog::select('tahun', 'semester')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        $periodOptions = [];
        $periods = [];

        if ($logs->count() > 0) {
            foreach ($logs as $log) {
                $key = "{$log->tahun}-{$log->semester}";
                $periodOptions[] = [
                    'key' => $key,
                    'label' => "Tahun {$log->tahun} - Semester {$log->semester}"
                ];
                $periods[$key] = $this->getRawDemographicData($log->tahun, $log->semester);
            }
        } else {
            // Default 2026-1 when no files uploaded yet
            $key = '2026-1';
            $periodOptions[] = [
                'key' => $key,
                'label' => 'Tahun 2026 - Semester 1 (Silakan Unggah File Input)'
            ];
            $periods[$key] = $this->getRawDemographicData(2026, 1);
        }

        $activeKey = $periodOptions[0]['key'];

        return [
            'metadata' => $this->getMetadata(),
            'period_options' => $periodOptions,
            'periods' => $periods,
            'districts' => $periods[$activeKey],
        ];
    }

    /**
     * Get real demographic records from DB per year and semester.
     * Dynamically extracts all Kecamatan, Desa/Kelurahan, and variables present in uploaded files.
     *
     * @param int $tahun
     * @param int $semester
     * @return array
     */
    protected function getRawDemographicData(int $tahun = 2026, int $semester = 1): array
    {
        $records = KependudukanRecord::where('tahun', $tahun)
            ->where('semester', $semester)
            ->get();

        if ($records->isEmpty()) {
            return $this->getEmptyDistrictStructure();
        }

        $districtMap = [];

        // Names that identify a kabupaten-level total/rekapitulasi row (not a real kecamatan).
        // These rows exist in some Excel formats as a grand-total footer and must be skipped.
        $kabupatenAliases = [
            'bangka tengah',
            'kabupaten bangka tengah',
            'kab. bangka tengah',
            'kab bangka tengah',
            'bangka tengah (kab)',
            'total',
            'jumlah',
            'jumlah total',
        ];

        // Dynamically build Kecamatan and Desa/Kelurahan structure from DB records
        foreach ($records as $record) {
            $kecName = $this->normalizeDistrictName($record->kecamatan);

            // Skip grand-total / kabupaten-level rows — they are not a real kecamatan
            if (in_array(strtolower(trim($kecName)), $kabupatenAliases, true)) {
                continue;
            }

            $desaName = $this->normalizeVillageName($record->desa_kelurahan);
            $categoryKey = $record->category_key;
            $payload = is_array($record->payload) ? $record->payload : (json_decode($record->payload, true) ?? []);

            if (!isset($districtMap[$kecName])) {
                $districtMap[$kecName] = [];
            }

            if (!isset($districtMap[$kecName][$desaName])) {
                $districtMap[$kecName][$desaName] = [
                    'name' => $desaName,
                    'kk_stat' => ['kk' => 0, 'anggota' => 0],
                    'agama' => [],
                    'golongan_darah' => [],
                    'jenis_kelamin' => [],
                    'kelompok_umur' => [],
                    'pekerjaan' => [],
                    'pendidikan' => [],
                    'status_perkawinan' => [],
                ];
            }

            $this->applyPayloadToVillage($districtMap[$kecName][$desaName], $categoryKey, $payload);
        }

        $result = [];
        foreach ($districtMap as $kecName => $villagesMap) {
            $villagesList = array_values($villagesMap);
            $result[] = [
                'name' => $kecName,
                'villages' => $villagesList,
            ];
        }

        return $result;
    }

    /**
     * Helper to normalize village names and prevent duplicates caused by spacing or casing differences.
     */
    protected function normalizeVillageName(string $name): string
    {
        $clean = str_replace(["\xc2\xa0", "\xa0", "&nbsp;"], ' ', $name);
        $clean = preg_replace('/\s+/', ' ', trim($clean));
        $lower = strtolower($clean);

        if (str_contains($lower, 'kurau') && str_contains($lower, 'timur')) {
            return 'Kurau (Timur)';
        }

        $formatted = ucwords($lower);
        $formatted = str_replace([' Iii', ' (kelurahan)', ' (kel)'], [' III', '', ''], $formatted);
        return trim($formatted);
    }

    /**
     * Helper to normalize district/kecamatan names.
     */
    protected function normalizeDistrictName(string $name): string
    {
        $clean = str_replace(["\xc2\xa0", "\xa0", "&nbsp;"], ' ', $name);
        $clean = preg_replace('/\s+/', ' ', trim($clean));
        return ucwords(strtolower($clean));
    }

    /**
     * Helper to apply payload metrics dynamically to a village record.
     */
    protected function applyPayloadToVillage(array &$village, string $categoryKey, array $payload): void
    {
        if ($categoryKey === 'kepala_keluarga') {
            $kk = 0;
            $anggota = 0;
            if (!isset($village['kepala_keluarga']) || !is_array($village['kepala_keluarga'])) {
                $village['kepala_keluarga'] = [];
            }
            foreach ($payload as $k => $v) {
                $cleanK = strtolower(str_replace(['_', ' '], '', (string)$k));
                if (str_contains($cleanK, 'anggota') || str_contains($cleanK, 'jiwa')) {
                    $anggota += (int)$v;
                } else {
                    $kk += (int)$v;
                }
                $label = $this->formatDynamicLabel((string)$k, $categoryKey);
                $village['kepala_keluarga'][$label] = ($village['kepala_keluarga'][$label] ?? 0) + (int)$v;
            }
            $village['kk_stat'] = ['kk' => $kk, 'anggota' => $anggota];
            return;
        }

        if (!isset($village[$categoryKey]) || !is_array($village[$categoryKey])) {
            $village[$categoryKey] = [];
        }

        foreach ($payload as $rawKey => $value) {
            $label = $this->formatDynamicLabel((string)$rawKey, $categoryKey);
            $val = (int)$value;
            if (!isset($village[$categoryKey][$label])) {
                $village[$categoryKey][$label] = 0;
            }
            $village[$categoryKey][$label] += $val;
        }
    }

    /**
     * Formats raw CSV column key into clean human readable display label.
     */
    protected function formatDynamicLabel(string $key, string $categoryKey): string
    {
        $clean = trim($key);
        if ($clean === '') return 'Lainnya';

        // For Golongan Darah, merge (+) and (-) into A, B, AB, O as requested
        if ($categoryKey === 'golongan_darah') {
            $cleanBlood = strtoupper(trim(str_replace(['GOLONGAN', 'DARAH', '+', '-'], '', $clean)));
            if (in_array($cleanBlood, ['A', 'B', 'AB', 'O'], true)) {
                return $cleanBlood;
            }
            if (str_contains(strtolower($clean), 'tahu') || str_contains(strtolower($clean), 'tidak')) {
                return 'Tidak Tahu';
            }
            return strtoupper($clean);
        }

        // For Jenis Kelamin
        if ($categoryKey === 'jenis_kelamin') {
            $l = strtolower($clean);
            if (str_contains($l, 'laki')) return 'Laki-Laki';
            if (str_contains($l, 'perempuan') || str_contains($l, 'wanita')) return 'Perempuan';
        }

        // For Kelompok Umur
        if ($categoryKey === 'kelompok_umur') {
            $u = str_replace(['umur', 'thn', 'tahun', '_'], [' ', ' ', ' ', '-'], strtolower($clean));
            $u = preg_replace('/\s+/', ' ', trim($u));
            $u = preg_replace('/(\d+)\s*-\s*(\d+)/', '$1 - $2', $u);
            return strtoupper($u);
        }

        // Convert snake_case or SCREAMING_SNAKE_CASE to clean Title Case
        $clean = str_replace(['_', '/'], ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);

        $words = explode(' ', strtolower($clean));
        $words = array_map(function($w) {
            $upperWords = ['pns', 'tni', 'polri', 'sd', 'smp', 'sma', 'smk', 'kk', 's1', 's2', 's3', 'd1', 'd2', 'd3', 'd4'];
            if (in_array($w, $upperWords, true)) {
                return strtoupper($w);
            }
            return ucfirst($w);
        }, $words);

        return implode(' ', $words);
    }

    /**
     * Default Fallback Structure of all 6 Kecamatan and 63 Desa/Kelurahan with ZERO initial counts when DB is empty.
     *
     * @return array
     */
    protected function getEmptyDistrictStructure(): array
    {
        $rawStructure = [
            'Koba' => [
                'Koba', 'Berok', 'Arung Dalam', 'Simpang Perlang',
                'Padang Mulia', 'Nibung', 'Penyak', 'Kurau (Timur)', 'Guntung', 'Kurau Barat', 'Terentang III'
            ],
            'Pangkalan Baru' => [
                'Dul (Kelurahan)', 'Air Mesu', 'Air Mesu Timur', 'Batu Belubang', 'Beluluk', 'Benteng',
                'Jeruk', 'Kebintik', 'Mangkol', 'Padang Baru', 'Pedindang', 'Tanjung Gunung'
            ],
            'Namang' => [
                'Namang', 'Belilik', 'Bhaskara Bhakti', 'Bukit Kijang', 'Cambai', 'Jelutung', 'Kayu Besi', 'Cambai Selatan'
            ],
            'Lubuk Besar' => [
                'Lubuk Besar', 'Batu Beriga', 'Belimbing', 'Kulur', 'Kulur Ilir', 'Lubuk Lingkuk',
                'Lubuk Pabrik', 'Perlang', 'Trubus'
            ],
            'Simpang Katis' => [
                'Simpang Katis', 'Beruas', 'Terak', 'Celuak', 'Pasir Garam', 'Puput', 'Pinang Sebatang', 'Sungkap', 'Katis', 'Teru'
            ],
            'Sungai Selan' => [
                'Sungai Selan (Kelurahan)', 'Lampur', 'Sungai Selan Atas', 'Romadhon', 'Kerantai', 'Kerakas',
                'Kemingking', 'Keretak', 'Keretak Atas', 'Sarang Mandi', 'Tanjung Pura', 'Munggu', 'Melabun'
            ],
        ];

        $result = [];
        foreach ($rawStructure as $kecName => $villages) {
            $vList = [];
            foreach ($villages as $vName) {
                $vList[] = [
                    'name' => $vName,
                    'kk_stat' => ['kk' => 0, 'anggota' => 0],
                    'agama' => [],
                    'golongan_darah' => [],
                    'jenis_kelamin' => [],
                    'kelompok_umur' => [],
                    'pekerjaan' => [],
                    'pendidikan' => [],
                    'status_perkawinan' => [],
                ];
            }
            $result[] = [
                'name' => $kecName,
                'villages' => $vList,
            ];
        }

        return $result;
    }
}
