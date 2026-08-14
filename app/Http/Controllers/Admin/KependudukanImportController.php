<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KependudukanImportLog;
use App\Models\KependudukanRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KependudukanImportController extends Controller
{
    /**
     * Display admin index page for Kependudukan data management
     */
    public function index(Request $request)
    {
        $logs = KependudukanImportLog::with('user:id,name,email')
            ->orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->latest()
            ->get();

        $categories = [
            'kepala_keluarga' => '1. Kepala Keluarga',
            'agama' => '2. Agama',
            'golongan_darah' => '3. Golongan Darah',
            'jenis_kelamin' => '4. Jenis Kelamin',
            'kelompok_umur' => '5. Kelompok Umur',
            'pekerjaan' => '6. Pekerjaan',
            'pendidikan' => '7. Pendidikan',
            'status_perkawinan' => '8. Status Perkawinan',
        ];

        $totalRecords = KependudukanRecord::count();

        return Inertia::render('Admin/Kependudukan/Index', [
            'logs' => $logs,
            'categories' => $categories,
            'totalRecords' => $totalRecords,
        ]);
    }

    /**
     * Import CSV file for a specific Category, Semester, and Year
     */
    public function import(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2099',
            'semester' => 'required|integer|in:1,2',
            'category_key' => 'required|string|in:kepala_keluarga,agama,golongan_darah,jenis_kelamin,kelompok_umur,pekerjaan,pendidikan,status_perkawinan',
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'tahun.required' => 'Tahun data wajib diisi.',
            'semester.required' => 'Semester wajib dipilih (1 atau 2).',
            'category_key.required' => 'Kategori data kependudukan wajib dipilih.',
            'file.required' => 'File CSV/Excel wajib diunggah.',
        ]);

        $tahun = (int) $request->input('tahun');
        $semester = (int) $request->input('semester');
        $categoryKey = $request->input('category_key');

        $categoriesNames = [
            'kepala_keluarga' => 'Kepala Keluarga',
            'agama' => 'Agama',
            'golongan_darah' => 'Golongan Darah',
            'jenis_kelamin' => 'Jenis Kelamin',
            'kelompok_umur' => 'Kelompok Umur',
            'pekerjaan' => 'Pekerjaan',
            'pendidikan' => 'Pendidikan',
            'status_perkawinan' => 'Status Perkawinan',
        ];
        $categoryName = $categoriesNames[$categoryKey] ?? $categoryKey;

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $path = $file->getRealPath();

        $parsedRows = $this->parseUploadedFile($path, $extension, $categoryKey);

        if (empty($parsedRows)) {
            return back()->withErrors([
                'file' => 'File CSV/Excel kosong atau format kolom tidak valid. Pastikan terdapat kolom KECAMATAN dan DESA/KELURAHAN.',
            ]);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();

            // Delete previous data for the exact same category, semester, and year to keep data fresh
            KependudukanRecord::where('category_key', $categoryKey)
                ->where('tahun', $tahun)
                ->where('semester', $semester)
                ->delete();

            $log = KependudukanImportLog::create([
                'user_id' => $user ? $user->id : null,
                'category_key' => $categoryKey,
                'category_name' => $categoryName,
                'tahun' => $tahun,
                'semester' => $semester,
                'file_name' => $originalName,
                'total_rows' => count($parsedRows),
                'status' => 'success',
                'notes' => "Import {$categoryName} Tahun {$tahun} Semester {$semester} (" . count($parsedRows) . " baris) berhasil.",
            ]);

            foreach ($parsedRows as $row) {
                $kecamatan = trim($row['kecamatan'] ?? 'Unknown');
                $desa = trim($row['desa_kelurahan'] ?? 'Unknown');

                // Payload contains all remaining breakdown metrics in row
                $payload = $row;
                unset($payload['kecamatan'], $payload['desa_kelurahan']);

                KependudukanRecord::create([
                    'import_log_id' => $log->id,
                    'tahun' => $tahun,
                    'semester' => $semester,
                    'category_key' => $categoryKey,
                    'kecamatan' => $kecamatan,
                    'desa_kelurahan' => $desa,
                    'payload' => $payload,
                    'created_by' => $user ? $user->id : null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.kependudukan.index')->with('success', "Berhasil mengimpor data {$categoryName} ({$tahun} Semester {$semester}) sebanyak " . count($parsedRows) . " data Desa/Kelurahan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Gagal mengimpor data: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete import log and snapshot records
     */
    public function destroyLog(KependudukanImportLog $log)
    {
        DB::beginTransaction();
        try {
            KependudukanRecord::where('import_log_id', $log->id)->delete();
            $log->delete();
            DB::commit();

            return redirect()->back()->with('success', "Log import {$log->category_name} ({$log->tahun} Semester {$log->semester}) berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['file' => 'Gagal menghapus log import: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Excel / CSV Template file for any of the 8 categories
     */
    public function downloadTemplate(string $category)
    {
        $templates = [
            'kepala_keluarga' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'JUMLAH_KK', 'JUMLAH_ANGGOTA'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '3120', '8480'],
                    ['Koba', 'Berok (Kelurahan)', '2450', '6430'],
                    ['Koba', 'Nibung', '1180', '3120'],
                    ['Pangkalan Baru', 'Dul (Kelurahan)', '2890', '7820'],
                ]
            ],
            'agama' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'ISLAM', 'BUDDHA', 'KRISTEN', 'KATOLIK', 'KHONGHUCU', 'LAINNYA'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '9920', '1100', '310', '180', '80', '10'],
                    ['Koba', 'Berok (Kelurahan)', '7750', '710', '260', '110', '40', '10'],
                ]
            ],
            'golongan_darah' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'O', 'A', 'B', 'AB', 'TIDAK_TAHU'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '4408', '3016', '2784', '928', '464'],
                    ['Koba', 'Berok (Kelurahan)', '3374', '2310', '2131', '710', '355'],
                ]
            ],
            'jenis_kelamin' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'LAKI_LAKI', 'PEREMPUAN'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '5971', '5629'],
                    ['Koba', 'Berok (Kelurahan)', '4573', '4307'],
                ]
            ],
            'kelompok_umur' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'UMUR_0_4', 'UMUR_5_14', 'UMUR_15_24', 'UMUR_25_39', 'UMUR_40_59', 'UMUR_60_PLUS'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '928', '1972', '2088', '3016', '2552', '1044'],
                    ['Koba', 'Berok (Kelurahan)', '710', '1510', '1600', '2310', '1954', '796'],
                ]
            ],
            'pekerjaan' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'PETANI', 'SWASTA', 'PELAJAR_MAHASISWA', 'IBU_RUMAH_TANGGA', 'WIRASWASTA', 'PNS_TNI_POLRI', 'BELUM_BEKERJA', 'LAINNYA'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '1856', '1856', '1972', '1740', '1392', '928', '1392', '464'],
                    ['Koba', 'Berok (Kelurahan)', '1510', '1420', '1510', '1332', '976', '533', '1243', '356'],
                ]
            ],
            'pendidikan' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'SD', 'SMP', 'SMA_SMK', 'BELUM_SEKOLAH', 'DIPLOMA', 'SARJANA_S1', 'PASCASARJANA'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '2320', '2552', '3712', '1392', '464', '1044', '116'],
                    ['Koba', 'Berok (Kelurahan)', '1776', '1954', '2842', '1065', '355', '796', '92'],
                ]
            ],
            'status_perkawinan' => [
                'headers' => ['KECAMATAN', 'DESA_KELURAHAN', 'BELUM_KAWIN', 'KAWIN', 'CERAI_HIDUP', 'CERAI_MATI'],
                'samples' => [
                    ['Koba', 'Koba (Kelurahan)', '4872', '6032', '232', '464'],
                    ['Koba', 'Berok (Kelurahan)', '3730', '4618', '178', '354'],
                ]
            ],
        ];

        $tpl = $templates[$category] ?? $templates['kepala_keluarga'];

        $output = implode(",", $tpl['headers']) . "\n";
        foreach ($tpl['samples'] as $row) {
            $output .= implode(",", array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        $filename = "TEMPLATE_KEPENDUDUKAN_" . strtoupper($category) . ".csv";

        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Parse uploaded file (CSV or XLSX) into structured records
     */
    private function parseUploadedFile(string $path, string $extension, string $categoryKey = ''): array
    {
        $matrix = [];

        if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
            $matrix = $this->parseXlsxFile($path);
        }

        if (empty($matrix)) {
            // Fallback to CSV parsing
            $content = @file_get_contents($path);
            if ($content) {
                $lines = explode("\n", str_replace("\r", "", $content));
                foreach ($lines as $line) {
                    if (trim($line) === "") continue;
                    $matrix[] = str_getcsv($line);
                }
            }
        }

        if (empty($matrix)) return [];

        return $this->processDemographicMatrix($matrix, $categoryKey);
    }

    /**
     * Helper to clean string cell values and strip non-breaking spaces (\xC2\xA0 / &nbsp;)
     */
    private function cleanCellStr($str): string
    {
        $s = str_replace(["\xc2\xa0", "\xa0", "&nbsp;"], ' ', (string)$str);
        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    /**
     * Helper to normalize village names and prevent duplicates caused by spacing or casing differences.
     */
    private function normalizeVillageName(string $name): string
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
     * Native XLSX Parser using built-in ZipArchive and XML parsing
     */
    private function parseXlsxFile(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        // 1. Read shared strings case-insensitively
        $sharedStrings = [];
        $sharedStringsXml = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (str_contains(strtolower($entryName), 'sharedstrings.xml')) {
                $sharedStringsXml = $zip->getFromIndex($i);
                break;
            }
        }

        if ($sharedStringsXml) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string)$val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Read first worksheet dynamically
        $sheetXml = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (str_contains(strtolower($entryName), 'worksheets/sheet') && str_ends_with(strtolower($entryName), '.xml')) {
                $sheetXml = $zip->getFromIndex($i);
                break;
            }
        }
        if (!$sheetXml) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (str_contains(strtolower($entryName), 'worksheets/') && str_ends_with(strtolower($entryName), '.xml')) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if (!$sheetXml) {
            return [];
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) {
            return [];
        }

        $matrix = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                preg_match('/([A-Z]+)(\d+)/', $ref, $matches);
                $colStr = $matches[1] ?? 'A';
                
                $colIndex = 0;
                $len = strlen($colStr);
                for ($k = 0; $k < $len; $k++) {
                    $colIndex = $colIndex * 26 + (ord($colStr[$k]) - ord('A') + 1);
                }
                $colIndex -= 1;

                $type = (string)($cell['t'] ?? '');
                $cellVal = '';

                if ($type === 's') {
                    $idx = (int)$cell->v;
                    $cellVal = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'inlineStr' || isset($cell->is)) {
                    if (isset($cell->is->t)) {
                        $cellVal = (string)$cell->is->t;
                    } elseif (isset($cell->is->r)) {
                        $t = '';
                        foreach ($cell->is->r as $r) {
                            $t .= (string)($r->t ?? '');
                        }
                        $cellVal = $t;
                    }
                } elseif (isset($cell->v)) {
                    $cellVal = (string)$cell->v;
                }

                $rowData[$colIndex] = $this->cleanCellStr($cellVal);
            }

            if (!empty($rowData)) {
                $maxCol = max(array_keys($rowData));
                $fullRow = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $fullRow[$c] = $rowData[$c] ?? '';
                }
                $matrix[] = $fullRow;
            }
        }

        return $matrix;
    }

    /**
     * Process 2D matrix into Dukcapil demographic records
     */
    private function processDemographicMatrix(array $matrix, string $categoryKey = ''): array
    {
        // 1. Check if multi-header Dukcapil spreadsheet (contains "WILAYAH" or "KECAMATAN" or "DESA")
        $isMultiHeader = false;
        $topHeaderRow = null;
        $subHeaderRow = null;
        $startDataRowIndex = 0;

        for ($i = 0; $i < min(15, count($matrix)); $i++) {
            $rowStr = implode(" ", array_map('strval', $matrix[$i]));
            $upper = strtoupper($rowStr);
            if (str_contains($upper, "WILAYAH") || str_contains($upper, "KECAMATAN") || str_contains($upper, "DESA")) {
                $isMultiHeader = true;
                $topHeaderRow = $matrix[$i];
                $subHeaderRow = $matrix[$i+1] ?? [];
                
                // Check if topHeaderRow itself contains column headers (like LAKI-LAKI, PEREMPUAN, etc.)
                $topRowCombined = strtoupper(implode(" ", array_map('strval', $topHeaderRow)));
                if (str_contains($topRowCombined, "LAKI") || str_contains($topRowCombined, "PEREMPUAN")) {
                    $subHeaderRow = $topHeaderRow;
                    $startDataRowIndex = $i + 1;
                } else {
                    $startDataRowIndex = $i + 2;
                }
                break;
            }
        }

        if ($isMultiHeader) {
            $jumlahColumns = []; // colIndex => categoryName

            // Check if this is the jenis_kelamin category
            $isJenisKelamin = ($categoryKey === 'jenis_kelamin');
            if (!$isJenisKelamin && $topHeaderRow) {
                $topHeaderStr = implode(" ", array_map('strval', $topHeaderRow));
                if (str_contains(strtoupper($topHeaderStr), "JENIS KELAMIN") || str_contains(strtoupper($topHeaderStr), "JUMLAH PENDUDUK")) {
                    $isJenisKelamin = true;
                }
            }

            if ($isJenisKelamin) {
                // For Jenis Kelamin: Sub-headers are LAKI-LAKI, PEREMPUAN, JUMLAH
                // We keep LAKI-LAKI and PEREMPUAN, and skip JUMLAH (total sum) to avoid double counting
                for ($c = 2; $c < count($subHeaderRow); $c++) {
                    $subVal = strtoupper($this->cleanCellStr($subHeaderRow[$c] ?? ""));
                    if (str_contains($subVal, "LAKI")) {
                        $jumlahColumns[$c] = "Laki-Laki";
                    } elseif (str_contains($subVal, "PEREMPUAN") || str_contains($subVal, "WANITA")) {
                        $jumlahColumns[$c] = "Perempuan";
                    }
                }
            } else {
                // For other categories (Agama, Pekerjaan, Pendidikan, Golongan Darah, Kelompok Umur, Status Perkawinan, Kepala Keluarga)
                // We keep ONLY the JUMLAH column for each category
                $currentCategory = "";
                for ($c = 2; $c < count($topHeaderRow); $c++) {
                    $topVal = $this->cleanCellStr($topHeaderRow[$c] ?? "");
                    if ($topVal !== "") {
                        $currentCategory = $topVal;
                    }
                    $subVal = strtoupper($this->cleanCellStr($subHeaderRow[$c] ?? ""));

                    if ($subVal === "JUMLAH") {
                        $jumlahColumns[$c] = $currentCategory;
                    }
                }

                // Fallback: if no JUMLAH sub-headers found, take all columns
                if (empty($jumlahColumns)) {
                    $currentCategory = "";
                    for ($c = 2; $c < count($topHeaderRow); $c++) {
                        $topVal = $this->cleanCellStr($topHeaderRow[$c] ?? "");
                        if ($topVal !== "") {
                            $currentCategory = $topVal;
                        }
                        $subVal = $this->cleanCellStr($subHeaderRow[$c] ?? "");
                        $label = $subVal !== "" ? $subVal : ($currentCategory ?: "col_$c");
                        $jumlahColumns[$c] = $label;
                    }
                }
            }

            $records = [];
            $currentKec = "";
            for ($i = $startDataRowIndex; $i < count($matrix); $i++) {
                $row = $matrix[$i];
                $col0 = $this->cleanCellStr($row[0] ?? "");
                $col1 = $this->cleanCellStr($row[1] ?? "");

                // Skip kabupaten total rows
                if (str_contains(strtoupper($col0), "BANGKA TENGAH") || str_contains(strtoupper($col0), "KABUPATEN")) continue;

                // Kecamatan header row (col0 has name, col1 is empty) — set currentKec
                if ($col0 !== "" && $col1 === "") {
                    $currentKec = ucwords(strtolower($col0));
                    continue;
                }

                // Desa/Kelurahan data row
                if ($col1 !== "" && $currentKec !== "") {
                    $desaName = $this->normalizeVillageName($col1);
                    $rowData = [
                        'kecamatan' => $currentKec,
                        'desa_kelurahan' => $desaName,
                    ];

                    foreach ($jumlahColumns as $cIdx => $categoryLabel) {
                        $rawVal = str_replace([".", ","], "", $this->cleanCellStr($row[$cIdx] ?? "0"));
                        $rowData[$categoryLabel] = is_numeric($rawVal) ? (int)$rawVal : $rawVal;
                    }
                    $records[] = $rowData;
                }
            }
            return $records;
        } else {
            // Flat CSV format
            $headers = [];
            $headerData = $matrix[0] ?? [];
            foreach ($headerData as $h) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $this->cleanCellStr($h))));
                $clean = str_replace([' ', '/', '-'], '_', $clean);
                $headers[] = $clean;
            }

            $kecIdx = array_search('kecamatan', $headers);
            if ($kecIdx === false) {
                foreach ($headers as $idx => $h) {
                    if (str_contains($h, 'kec')) { $kecIdx = $idx; break; }
                }
            }

            $desaIdx = array_search('desa_kelurahan', $headers);
            if ($desaIdx === false) {
                foreach ($headers as $idx => $h) {
                    if (str_contains($h, 'desa') || str_contains($h, 'kel')) { $desaIdx = $idx; break; }
                }
            }

            if ($kecIdx === false || $desaIdx === false) return [];

            $rows = [];
            for ($i = 1; $i < count($matrix); $i++) {
                $data = $matrix[$i];
                if (count($data) <= max($kecIdx, $desaIdx)) continue;

                $rowData = [];
                foreach ($headers as $colIdx => $colName) {
                    $val = $this->cleanCellStr($data[$colIdx] ?? '');
                    if ($colIdx === $kecIdx) {
                        $rowData['kecamatan'] = $val;
                    } elseif ($colIdx === $desaIdx) {
                        $rowData['desa_kelurahan'] = $val;
                    } else {
                        $rawNum = str_replace([".", ","], "", $val);
                        $rowData[$colName] = is_numeric($rawNum) ? (int)$rawNum : $val;
                    }
                }
                if (!empty($rowData['kecamatan']) && !empty($rowData['desa_kelurahan'])) {
                    $rows[] = $rowData;
                }
            }
        }
    }
}
