<?php

namespace App\Http\Controllers\Kdmp;

use App\Http\Controllers\Controller;
use App\Models\Kmp;
use App\Models\KmpImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KmpImportController extends Controller
{
    public function index(Request $request)
    {
        $logs = KmpImportLog::with('user:id,name,email')
            ->latest()
            ->get();

        $query = Kmp::query();

        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }

        $kmps = $query->latest()->paginate(15)->withQueryString();

        $totalCount = Kmp::count();
        $kecamatans = ['Koba', 'Pangkalan Baru', 'Namang', 'Lubuk Besar', 'Simpang Katis', 'Sungai Selan'];

        return Inertia::render('Kdmp/Admin/ImportKmp', [
            'logs' => $logs,
            'kmps' => $kmps,
            'totalCount' => $totalCount,
            'kecamatans' => $kecamatans,
            'filters' => $request->only(['kecamatan']),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel/CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat CSV, XLS, atau XLSX.',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];

        if (in_array($extension, ['csv', 'txt'])) {
            $rows = $this->parseCsv($path);
        } else {
            $rows = $this->parseXlsxOrCsvFallback($path);
        }

        if (empty($rows)) {
            return back()->withErrors([
                'file' => 'File Excel/CSV kosong atau format header tidak sesuai. Pastikan file memuat kolom NAMA_KOPERASI, KECAMATAN, dan DESA_KELURAHAN.',
            ]);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();

            $log = KmpImportLog::create([
                'user_id' => $user->id,
                'file_name' => $originalName,
                'total_rows' => count($rows),
                'status' => 'success',
                'notes' => "Import (" . count($rows) . " data KMP) berhasil.",
            ]);

            foreach ($rows as $row) {
                Kmp::create([
                    'user_id' => null,
                    'import_log_id' => $log->id,
                    'kecamatan' => trim($row['kecamatan']),
                    'desa_kelurahan' => trim($row['desa_kelurahan']),
                    'jenis' => $row['jenis'] ?? 'Desa',
                    'nama_koperasi' => trim($row['nama_koperasi']),
                    'nomor_induk' => $row['nomor_induk'] ?? null,
                    'no_ahu' => $row['no_ahu'] ?? null,
                    'tanggal_ahu' => $row['tanggal_ahu'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'telp' => $row['telp'] ?? null,
                    'status_operasional' => false,
                    'status_pembentukan' => 'verified',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.kdmp.import_kmp')->with('success', "Berhasil mengimpor " . count($rows) . " data KMP.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Gagal mengimpor data: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Kmp $kmp)
    {
        $request->validate([
            'nama_koperasi' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'desa_kelurahan' => 'required|string|max:255',
            'jenis' => 'required|in:Desa,Kelurahan',
            'alamat' => 'nullable|string',
            'nomor_induk' => 'nullable|string|max:255',
            'no_ahu' => 'nullable|string|max:255',
        ], [
            'nama_koperasi.required' => 'Nama koperasi wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'desa_kelurahan.required' => 'Desa / Kelurahan wajib diisi.',
        ]);

        $kmp->update([
            'nama_koperasi' => trim($request->nama_koperasi),
            'kecamatan' => trim($request->kecamatan),
            'desa_kelurahan' => trim($request->desa_kelurahan),
            'jenis' => $request->jenis,
            'alamat' => $request->alamat ? trim($request->alamat) : null,
            'nomor_induk' => $request->nomor_induk ? trim($request->nomor_induk) : null,
            'no_ahu' => $request->no_ahu ? trim($request->no_ahu) : null,
        ]);

        return redirect()->back()->with('success', 'Data KMP berhasil diperbarui.');
    }

    public function destroy(Kmp $kmp)
    {
        $kmp->delete();

        return redirect()->back()->with('success', 'Data KMP berhasil dihapus.');
    }

    public function destroyLog(KmpImportLog $log)
    {
        DB::beginTransaction();
        try {
            Kmp::where('import_log_id', $log->id)->delete();
            $log->delete();
            DB::commit();

            return redirect()->back()->with('success', "Seluruh data import KMP berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['file' => 'Gagal menghapus log import: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $csvHeader = ["NO", "KOPERASI", "ALAMAT", "DESA/KELURAHAN", "KECAMATAN", "KABUPATEN", "PROPINSI"];
        $sampleRows = [
            ["1", "KOPERASI KELURAHAN MERAH PUTIH ARUNG DALAM", "JALAN SOEKARNO HATTA II", "ARUNG DALAM", "KOBA", "KAB. BANGKA TENGAH", "KEPULAUAN BANGKA BELITUNG"],
            ["2", "KOPERASI KELURAHAN MERAH PUTIH BEROK", "JALAN KANTOR KELURAHAN BEROK", "BEROK", "KOBA", "KAB. BANGKA TENGAH", "KEPULAUAN BANGKA BELITUNG"],
            ["3", "KOPERASI DESA MERAH PUTIH GUNTUNG KECAMATAN KOBA", "KANTOR KEPALA DESA GUNTUNG, JALAN RAYA DESA GUNTUNG", "GUNTUNG", "KOBA", "KAB. BANGKA TENGAH", "KEPULAUAN BANGKA BELITUNG"],
            ["4", "KOPERASI DESA MERAH PUTIH AIR MESU", "JALAN AMANAH", "AIR MESU", "PANGKALAN BARU", "KAB. BANGKA TENGAH", "KEPULAUAN BANGKA BELITUNG"],
            ["5", "KOPERASI DESA MERAH PUTIH KEMINGKING", "KANTOR DESA KEMINGKING", "KEMINGKING", "SUNGAI SELAN", "KAB. BANGKA TENGAH", "KEPULAUAN BANGKA BELITUNG"],
        ];

        $output = implode(",", $csvHeader) . "\n";
        foreach ($sampleRows as $row) {
            $output .= implode(",", array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="TEMPLATE_DATA_KDMP_BANGKA_TENGAH.csv"',
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

    private function formatKecamatan(string $raw): string
    {
        $clean = trim($raw);
        $validMap = [
            'koba' => 'Koba',
            'pangkalan baru' => 'Pangkalan Baru',
            'pangkalanbaru' => 'Pangkalan Baru',
            'namang' => 'Namang',
            'lubuk besar' => 'Lubuk Besar',
            'simpang katis' => 'Simpang Katis',
            'simpangkatis' => 'Simpang Katis',
            'sungai selan' => 'Sungai Selan',
            'sungaiselan' => 'Sungai Selan',
        ];

        $lower = strtolower($clean);
        if (isset($validMap[$lower])) {
            return $validMap[$lower];
        }

        return ucwords(strtolower($clean));
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
        $desaIdx = $this->findColumnIndex($headers, ['desa_kelurahan', 'desa_kel', 'desa', 'kelurahan']);
        $jenisIdx = $this->findColumnIndex($headers, ['jenis', 'tipe', 'type']);
        $alamatIdx = $this->findColumnIndex($headers, ['alamat', 'address']);

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
                $jenis = 'Desa';
                if ($jenisIdx !== false && !empty(trim($data[$jenisIdx] ?? ''))) {
                    $rawJenis = trim($data[$jenisIdx]);
                    if (strtolower($rawJenis) === 'kelurahan') {
                        $jenis = 'Kelurahan';
                    }
                } else if (str_contains(strtoupper($nama), 'KELURAHAN')) {
                    $jenis = 'Kelurahan';
                }

                $rows[] = [
                    'nama_koperasi' => $nama,
                    'kecamatan' => $this->formatKecamatan($kec),
                    'desa_kelurahan' => strtoupper($desa),
                    'jenis' => $jenis,
                    'alamat' => ($alamatIdx !== false) ? trim($data[$alamatIdx] ?? '') : null,
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
                $desaIdx = $this->findColumnIndex($headers, ['desa_kelurahan', 'desa_kel', 'desa', 'kelurahan']);
                $jenisIdx = $this->findColumnIndex($headers, ['jenis', 'tipe', 'type']);
                $alamatIdx = $this->findColumnIndex($headers, ['alamat', 'address']);

                if ($namaIdx === false || $kecIdx === false || $desaIdx === false) {
                    return [];
                }

                $rows = [];
                foreach ($data as $row) {
                    $nama = trim((string)($row[$namaIdx] ?? ''));
                    $kec = trim((string)($row[$kecIdx] ?? ''));
                    $desa = trim((string)($row[$desaIdx] ?? ''));

                    if (!empty($nama) && !empty($kec) && !empty($desa)) {
                        $jenis = 'Desa';
                        if ($jenisIdx !== false && !empty(trim((string)($row[$jenisIdx] ?? '')))) {
                            $rawJenis = trim((string)($row[$jenisIdx]));
                            if (strtolower($rawJenis) === 'kelurahan') {
                                $jenis = 'Kelurahan';
                            }
                        } else if (str_contains(strtoupper($nama), 'KELURAHAN')) {
                            $jenis = 'Kelurahan';
                        }

                        $rows[] = [
                            'nama_koperasi' => $nama,
                            'kecamatan' => $this->formatKecamatan($kec),
                            'desa_kelurahan' => strtoupper($desa),
                            'jenis' => $jenis,
                            'alamat' => ($alamatIdx !== false) ? trim((string)($row[$alamatIdx] ?? '')) : null,
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
                            $desaIdx = $this->findColumnIndex($headers, ['desa_kelurahan', 'desa_kel', 'desa', 'kelurahan']);
                            $jenisIdx = $this->findColumnIndex($headers, ['jenis', 'tipe', 'type']);
                            $alamatIdx = $this->findColumnIndex($headers, ['alamat', 'address']);

                            if ($namaIdx !== false && $kecIdx !== false && $desaIdx !== false) {
                                $rows = [];
                                foreach ($parsedSheetRows as $dataRow) {
                                    $nama = trim((string)($dataRow[$namaIdx] ?? ''));
                                    $kec = trim((string)($dataRow[$kecIdx] ?? ''));
                                    $desa = trim((string)($dataRow[$desaIdx] ?? ''));

                                    if (!empty($nama) && !empty($kec) && !empty($desa)) {
                                        $jenis = 'Desa';
                                        if ($jenisIdx !== false && !empty(trim((string)($dataRow[$jenisIdx] ?? '')))) {
                                            $rawJenis = trim((string)($dataRow[$jenisIdx]));
                                            if (strtolower($rawJenis) === 'kelurahan') {
                                                $jenis = 'Kelurahan';
                                            }
                                        } else if (str_contains(strtoupper($nama), 'KELURAHAN')) {
                                            $jenis = 'Kelurahan';
                                        }

                                        $rows[] = [
                                            'nama_koperasi' => $nama,
                                            'kecamatan' => $this->formatKecamatan($kec),
                                            'desa_kelurahan' => strtoupper($desa),
                                            'jenis' => $jenis,
                                            'alamat' => ($alamatIdx !== false) ? trim((string)($dataRow[$alamatIdx] ?? '')) : null,
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
