<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KependudukanImportLog;
use App\Models\KependudukanRecord;
use Illuminate\Support\Facades\DB;

class SyncKependudukanSpreadsheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kependudukan:sync-spreadsheets {--tahun=2026} {--semester=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and sync demographic dataset from 8 Google Spreadsheets into database';

    /**
     * Spreadsheet mappings.
     */
    protected array $spreadsheets = [
        'kepala_keluarga' => [
            'name' => '1. Kepala Keluarga',
            'id' => '19TYw-NMkCkyZ9fJRRApTY60kGoCRiVwUzKYVqnahMk8'
        ],
        'agama' => [
            'name' => '2. Agama',
            'id' => '1fbtM_nUPSZf5APXpzHHaSk_4b-DOkFEXtJ0rMmdGUuk'
        ],
        'golongan_darah' => [
            'name' => '3. Golongan Darah',
            'id' => '1e3XFhyBPWpbH796BeNB1baHIwFmnvu0ImLPrqb9vRQo'
        ],
        'jenis_kelamin' => [
            'name' => '4. Jenis Kelamin',
            'id' => '1N-jVckaO_aMJTNZv_yFVOeM_ck6duAWNGk4sCJO_NaA'
        ],
        'kelompok_umur' => [
            'name' => '5. Kelompok Umur',
            'id' => '1_uE2tGYjyceyy1IB0aErxCT8lacAmjBnc8uVSDnlizc'
        ],
        'pendidikan' => [
            'name' => '6. Pendidikan',
            'id' => '1QiDMMqUwfcKWTslFV0M4WyyH26u5HbK9FDu75HSV-us'
        ],
        'pekerjaan' => [
            'name' => '7. Pekerjaan',
            'id' => '1us1koV_G5h5xcfbYgAuoYHabwEFTk9kTJfoSEgW2eFU'
        ],
        'status_perkawinan' => [
            'name' => '8. Status Perkawinan',
            'id' => '1PrzXSeGsTgpsI7kC6yAZtHxCVyXJEGTg_ZIC4YMd4-0'
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahun = (int) $this->option('tahun');
        $semester = (int) $this->option('semester');

        $this->info("Mengimpor data Kependudukan dari 8 Google Spreadsheets untuk Tahun {$tahun} Semester {$semester}...");

        foreach ($this->spreadsheets as $categoryKey => $info) {
            $this->importCategory($categoryKey, $info['name'], $info['id'], $tahun, $semester);
        }

        $this->info("✅ Seluruh 8 Google Spreadsheets berhasil diimpor ke dalam database!");
        return 0;
    }

    protected function importCategory(string $categoryKey, string $categoryName, string $spreadsheetId, int $tahun, int $semester)
    {
        $url = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv";
        $csvContent = @file_get_contents($url);

        if (!$csvContent) {
            $this->error("Gagal mengunduh spreadsheet untuk {$categoryName}");
            return;
        }

        $csvData = [];
        $lines = explode("\n", str_replace("\r", "", $csvContent));
        foreach ($lines as $line) {
            if (trim($line) === "") continue;
            $csvData[] = str_getcsv($line);
        }

        // Find header rows
        $topHeaderRow = null;
        $subHeaderRow = null;
        $startDataRowIndex = 0;

        for ($i = 0; $i < min(10, count($csvData)); $i++) {
            $rowStr = implode(" ", $csvData[$i]);
            if (str_contains(strtoupper($rowStr), "WILAYAH")) {
                $topHeaderRow = $csvData[$i];
                $subHeaderRow = $csvData[$i+1] ?? [];

                $topRowCombined = strtoupper(implode(" ", $topHeaderRow));
                if (str_contains($topRowCombined, "LAKI") || str_contains($topRowCombined, "PEREMPUAN")) {
                    $subHeaderRow = $topHeaderRow;
                    $startDataRowIndex = $i + 1;
                } else {
                    $startDataRowIndex = $i + 2;
                }
                break;
            }
        }

        // Build column mapping
        $jumlahColumns = []; // colIndex => categoryName
        if ($topHeaderRow) {
            if ($categoryKey === 'jenis_kelamin') {
                for ($c = 2; $c < count($subHeaderRow); $c++) {
                    $subVal = strtoupper(trim($subHeaderRow[$c] ?? ""));
                    if (str_contains($subVal, "LAKI")) {
                        $jumlahColumns[$c] = "Laki-Laki";
                    } elseif (str_contains($subVal, "PEREMPUAN") || str_contains($subVal, "WANITA")) {
                        $jumlahColumns[$c] = "Perempuan";
                    }
                }
            } else {
                $currentCategory = "";
                for ($c = 2; $c < count($topHeaderRow); $c++) {
                    $topVal = trim($topHeaderRow[$c] ?? "");
                    if ($topVal !== "") {
                        $currentCategory = $topVal;
                    }
                    $subVal = strtoupper(trim($subHeaderRow[$c] ?? ""));

                    // Only keep the JUMLAH sub-column
                    if ($subVal === "JUMLAH") {
                        $jumlahColumns[$c] = $currentCategory;
                    }
                }

                // Fallback: if no JUMLAH sub-headers found, take all columns
                if (empty($jumlahColumns)) {
                    $currentCategory = "";
                    for ($c = 2; $c < count($topHeaderRow); $c++) {
                        $topVal = trim($topHeaderRow[$c] ?? "");
                        if ($topVal !== "") {
                            $currentCategory = $topVal;
                        }
                        $subVal = trim($subHeaderRow[$c] ?? "");
                        $label = $subVal !== "" ? $subVal : ($currentCategory ?: "col_$c");
                        $jumlahColumns[$c] = $label;
                    }
                }
            }
        }

        $parsedRows = [];
        $currentKec = "";
        for ($i = $startDataRowIndex; $i < count($csvData); $i++) {
            $row = $csvData[$i];
            $col0 = trim($row[0] ?? "");
            $col1 = trim($row[1] ?? "");

            if ($col0 === "BANGKA TENGAH" || str_contains($col0, "KABUPATEN")) continue;

            // Kecamatan header row — skip
            if ($col0 !== "" && $col1 === "") {
                $currentKec = ucwords(strtolower($col0));
                continue;
            }

            // Desa/Kelurahan data row
            if ($col1 !== "" && $currentKec !== "") {
                $desaName = $this->normalizeVillageName($col1);
                $payload = [];
                foreach ($jumlahColumns as $cIdx => $categoryLabel) {
                    $rawVal = str_replace([".", ","], "", trim($row[$cIdx] ?? "0"));
                    $payload[$categoryLabel] = is_numeric($rawVal) ? (int)$rawVal : $rawVal;
                }
                $parsedRows[] = [
                    'kecamatan' => $currentKec,
                    'desa_kelurahan' => $desaName,
                    'payload' => $payload,
                ];
            }
        }

        DB::beginTransaction();
        try {
            KependudukanRecord::where('category_key', $categoryKey)
                ->where('tahun', $tahun)
                ->where('semester', $semester)
                ->delete();

            $log = KependudukanImportLog::create([
                'user_id' => null,
                'category_key' => $categoryKey,
                'category_name' => $categoryName,
                'tahun' => $tahun,
                'semester' => $semester,
                'file_name' => "Google_Spreadsheet_{$categoryKey}.csv",
                'total_rows' => count($parsedRows),
                'status' => 'success',
                'notes' => "Import Google Spreadsheet {$categoryName} Tahun {$tahun} Semester {$semester} (" . count($parsedRows) . " Desa) berhasil.",
            ]);

            foreach ($parsedRows as $r) {
                KependudukanRecord::create([
                    'import_log_id' => $log->id,
                    'tahun' => $tahun,
                    'semester' => $semester,
                    'category_key' => $categoryKey,
                    'kecamatan' => $r['kecamatan'],
                    'desa_kelurahan' => $r['desa_kelurahan'],
                    'payload' => $r['payload'],
                ]);
            }

            DB::commit();
            $this->line("  ✓ {$categoryName}: " . count($parsedRows) . " baris Desa terimpor.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal mengimpor {$categoryName}: " . $e->getMessage());
        }
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
}
