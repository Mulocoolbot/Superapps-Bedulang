<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Koperasi;
use App\Models\KoperasiImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KoperasiImportController extends Controller
{
    public function index(Request $request)
    {
        $logs = KoperasiImportLog::with('user:id,name,email')
            ->orderBy('tahun', 'desc')
            ->latest()
            ->get();

        $koperasis = Koperasi::latest()
            ->paginate(15);

        $totalCount = Koperasi::count();
        $importedYears = KoperasiImportLog::pluck('tahun')->sort()->values()->all();

        return Inertia::render('Admin/Koperasi/Index', [
            'logs' => $logs,
            'koperasis' => $koperasis,
            'totalCount' => $totalCount,
            'importedYears' => $importedYears,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2099',
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'tahun.required' => 'Tahun data wajib diisi.',
            'tahun.integer' => 'Tahun data harus berupa angka tahun.',
            'file.required' => 'File Excel/CSV wajib diunggah.',
        ]);

        $tahun = (int) $request->input('tahun');

        // Validation Rule: Cannot import data for a year that has already been imported
        if (KoperasiImportLog::where('tahun', $tahun)->exists()) {
            return back()->withErrors([
                'tahun' => "Tahun {$tahun} sudah pernah di-import. Data tidak boleh ada tahun yang sama.",
            ]);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];

        if (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($path);
        } else {
            // XLSX / XLS parsing
            $rows = $this->parseXlsxOrCsvFallback($path);
        }

        if (empty($rows)) {
            return back()->withErrors([
                'file' => 'File Excel/CSV kosong atau format header tidak sesuai. Pastikan file memuat kolom KOPERASI, DESA/KELURAHAN, dan KECAMATAN.',
            ]);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();

            $log = KoperasiImportLog::create([
                'user_id' => $user->id,
                'file_name' => $originalName,
                'tahun' => $tahun,
                'total_rows' => count($rows),
                'status' => 'success',
                'notes' => "Import {$tahun} (" . count($rows) . " data) berhasil.",
            ]);

            foreach ($rows as $row) {
                Koperasi::create([
                    'nama_koperasi' => trim($row['nama_koperasi']),
                    'kecamatan' => trim($row['kecamatan']),
                    'desa_kelurahan' => trim($row['desa_kelurahan']),
                    'tahun' => $tahun,
                    'import_log_id' => $log->id,
                    'created_by' => $user->id,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.koperasi.index')->with('success', "Berhasil mengimpor " . count($rows) . " data koperasi untuk Tahun {$tahun}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Gagal mengimpor data: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Koperasi $koperasi)
    {
        $request->validate([
            'nama_koperasi' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'desa_kelurahan' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2000|max:2099',
        ], [
            'nama_koperasi.required' => 'Nama koperasi wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'desa_kelurahan.required' => 'Desa / Kelurahan wajib diisi.',
            'tahun.required' => 'Tahun data wajib diisi.',
        ]);

        $koperasi->update([
            'nama_koperasi' => trim($request->nama_koperasi),
            'kecamatan' => trim($request->kecamatan),
            'desa_kelurahan' => trim($request->desa_kelurahan),
            'tahun' => (int) $request->tahun,
        ]);

        return redirect()->back()->with('success', 'Data koperasi berhasil diperbarui.');
    }

    public function destroy(Koperasi $koperasi)
    {
        $koperasi->delete();

        return redirect()->back()->with('success', 'Data koperasi berhasil dihapus.');
    }

    public function destroyLog(KoperasiImportLog $log)
    {
        DB::beginTransaction();
        try {
            Koperasi::where('tahun', $log->tahun)->orWhere('import_log_id', $log->id)->delete();
            $log->delete();
            DB::commit();

            return redirect()->back()->with('success', "Seluruh data snapshot import Tahun {$log->tahun} berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['file' => 'Gagal menghapus log import: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $csvHeader = ["KOPERASI", "NO BADAN HUKUM", "TANGGAL BADAN HUKUM", "ALAMAT", "DESA/KELURAHAN", "KECAMATAN", "KABUPATEN"];
        $sampleRows = [
            ["Koperasi Unit Desa Koba", "00597/BH/PAD/KWK.6/XI/1996", "12 November 1996", "Jl. Raya Arung Dalam", "ARUNG DALAM", "KOBA", "KAB. BANGKA TENGAH"],
            ["Koperasi Produsen Madani", "110/BH/VIII.5/2013", "26 November 2013", "Jl. Raya Koba", "ARUNG DALAM", "KOBA", "KAB. BANGKA TENGAH"],
            ["Koperasi Simpan Pinjam Sehati", "111/BH/VIII.5/2013", "27 November 2013", "Jl. Raya Arung Dalam", "ARUNG DALAM", "KOBA", "KAB. BANGKA TENGAH"],
            ["Koperasi Serba Usaha Putra Mulia", "001/KPTS/BH/518/II/2004", "05 February 2004", "Kampung Berok", "BEROK", "KOBA", "KAB. BANGKA TENGAH"],
            ["Koperasi Nelayan Batu Belubang", "012/BH/VIII.5/2015", "14 March 2015", "Jl. Pantai Batu Belubang", "BATU BELUBANG", "PANGKALAN BARU", "KAB. BANGKA TENGAH"],
        ];

        $output = implode(",", $csvHeader) . "\n";
        foreach ($sampleRows as $row) {
            $output .= implode(",", array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="DATA_KOPERASI_BANGKA_TENGAH_TEMPLATE.csv"',
        ]);
    }

    private function findColumnIndex(array $headers, array $candidates)
    {
        foreach ($candidates as $cand) {
            $idx = array_search($cand, $headers);
            if ($idx !== false) return $idx;
        }
        return false;
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        $headers = [];
        $rows = [];

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';

        if (($headerData = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
            foreach ($headerData as $h) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $h)));
                $clean = str_replace([' ', '/', '-'], '_', $clean);
                $headers[] = $clean;
            }
        }

        $namaIdx = $this->findColumnIndex($headers, ['koperasi', 'nama_koperasi', 'nama']);
        $kecIdx = $this->findColumnIndex($headers, ['kecamatan', 'kec']);
        $desaIdx = $this->findColumnIndex($headers, ['desa_kelurahan', 'desa', 'kelurahan', 'desa_kel']);

        if ($namaIdx === false || $kecIdx === false || $desaIdx === false) {
            fclose($handle);
            return [];
        }

        while (($data = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
            if (count($data) <= max($namaIdx, $kecIdx, $desaIdx)) continue;

            $nama = trim($data[$namaIdx] ?? '');
            $kec = trim($data[$kecIdx] ?? '');
            $desa = trim($data[$desaIdx] ?? '');

            if (!empty($nama) && !empty($kec) && !empty($desa)) {
                $rows[] = [
                    'nama_koperasi' => $nama,
                    'kecamatan' => ucwords(strtolower($kec)),
                    'desa_kelurahan' => ucwords(strtolower($desa)),
                ];
            }
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsxOrCsvFallback(string $path): array
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();
                if (empty($data)) return [];

                $headerData = array_shift($data);
                $headers = [];
                foreach ($headerData as $h) {
                    $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', (string)$h)));
                    $clean = str_replace([' ', '/', '-'], '_', $clean);
                    $headers[] = $clean;
                }

                $namaIdx = $this->findColumnIndex($headers, ['koperasi', 'nama_koperasi', 'nama']);
                $kecIdx = $this->findColumnIndex($headers, ['kecamatan', 'kec']);
                $desaIdx = $this->findColumnIndex($headers, ['desa_kelurahan', 'desa', 'kelurahan', 'desa_kel']);

                if ($namaIdx === false || $kecIdx === false || $desaIdx === false) {
                    return [];
                }

                $rows = [];
                foreach ($data as $row) {
                    $nama = trim((string)($row[$namaIdx] ?? ''));
                    $kec = trim((string)($row[$kecIdx] ?? ''));
                    $desa = trim((string)($row[$desaIdx] ?? ''));

                    if (!empty($nama) && !empty($kec) && !empty($desa)) {
                        $rows[] = [
                            'nama_koperasi' => $nama,
                            'kecamatan' => ucwords(strtolower($kec)),
                            'desa_kelurahan' => ucwords(strtolower($desa)),
                        ];
                    }
                }
                return $rows;
            } catch (\Throwable $e) {
                // Fallback
            }
        }

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($path) === TRUE) {
                $sharedStrings = [];
                if (($xml = $zip->getFromName('xl/sharedStrings.xml')) !== FALSE) {
                    $sxml = simplexml_load_string($xml);
                    if ($sxml) {
                        foreach ($sxml->si as $val) {
                            $sharedStrings[] = (string)($val->t ?? $val->r->t ?? '');
                        }
                    }
                }

                if (($xml = $zip->getFromName('xl/worksheets/sheet1.xml')) !== FALSE) {
                    $sxml = simplexml_load_string($xml);
                    if ($sxml) {
                        $parsedSheetRows = [];
                        foreach ($sxml->sheetData->row as $rowNode) {
                            $r = [];
                            foreach ($rowNode->c as $cellNode) {
                                $v = (string)$cellNode->v;
                                if ((string)$cellNode['t'] === 's' && isset($sharedStrings[(int)$v])) {
                                    $v = $sharedStrings[(int)$v];
                                }
                                $r[] = $v;
                            }
                            $parsedSheetRows[] = $r;
                        }

                        if (!empty($parsedSheetRows)) {
                            $headerData = array_shift($parsedSheetRows);
                            $headers = [];
                            foreach ($headerData as $h) {
                                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', (string)$h)));
                                $clean = str_replace([' ', '/', '-'], '_', $clean);
                                $headers[] = $clean;
                            }

                            $namaIdx = $this->findColumnIndex($headers, ['koperasi', 'nama_koperasi', 'nama']);
                            $kecIdx = $this->findColumnIndex($headers, ['kecamatan', 'kec']);
                            $desaIdx = $this->findColumnIndex($headers, ['desa_kelurahan', 'desa', 'kelurahan', 'desa_kel']);

                            if ($namaIdx !== false && $kecIdx !== false && $desaIdx !== false) {
                                $rows = [];
                                foreach ($parsedSheetRows as $dataRow) {
                                    $nama = trim((string)($dataRow[$namaIdx] ?? ''));
                                    $kec = trim((string)($dataRow[$kecIdx] ?? ''));
                                    $desa = trim((string)($dataRow[$desaIdx] ?? ''));

                                    if (!empty($nama) && !empty($kec) && !empty($desa)) {
                                        $rows[] = [
                                            'nama_koperasi' => $nama,
                                            'kecamatan' => ucwords(strtolower($kec)),
                                            'desa_kelurahan' => ucwords(strtolower($desa)),
                                        ];
                                    }
                                }
                                $zip->close();
                                return $rows;
                            }
                        }
                    }
                }
                $zip->close();
            }
        }

        return $this->parseCsv($path);
    }
}
