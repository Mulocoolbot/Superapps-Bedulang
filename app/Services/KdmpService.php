<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Kmp;

class KdmpService implements InformationServiceInterface
{
    public function getMetadata(): array
    {
        return [
            'name' => 'Koperasi Desa Merah Putih',
            'icon' => '🤝',
            'description' => 'Dashboard informasi Koperasi Desa Merah Putih Kabupaten Bangka Tengah.',
            'filters' => [],
        ];
    }

    public function getDashboardData(Request $request): array
    {
        $kecamatans = ['Koba', 'Pangkalan Baru', 'Namang', 'Lubuk Besar', 'Simpang Katis', 'Sungai Selan'];
        
        $tableData = [];
        $totalUnit = 0;
        
        foreach ($kecamatans as $kecamatan) {
            $koperasiDesa = Kmp::where('kecamatan', $kecamatan)
                ->where('status_pembentukan', 'verified')
                ->where('jenis', 'Desa')
                ->count();
                
            $koperasiLurah = Kmp::where('kecamatan', $kecamatan)
                ->where('status_pembentukan', 'verified')
                ->where('jenis', 'Kelurahan')
                ->count();
                
            $total = $koperasiDesa + $koperasiLurah;
            $totalUnit += $total;
            
            $tableData[] = [
                'kecamatan' => $kecamatan,
                'koperasi_desa' => $koperasiDesa,
                'koperasi_lurah' => $koperasiLurah,
                'total_koperasi' => $total,
            ];
        }

        // Mock SDM data since we don't have this in KMP table yet, or we could leave it as mock
        $sdm = [
            'pengawas' => 135,
            'pengurus' => 240,
            'anggota' => 1500,
        ];
        
        $totalSdm = $sdm['pengawas'] + $sdm['pengurus'] + $sdm['anggota'];

        return [
            'metadata' => [
                'updated_at' => now()->format('Y-m-d H:i:s'),
                'source' => 'Data Koperasi KDMP Bangka Tengah',
            ],
            'summary' => [
                'total_unit' => $totalUnit,
                'total_sdm' => $totalSdm,
                'sdm_details' => $sdm,
            ],
            'table_data' => $tableData,
        ];
    }

    public function getKecamatanDetails(string $kecamatan): ?array
    {
        $validKecamatans = ['Koba', 'Pangkalan Baru', 'Namang', 'Lubuk Besar', 'Simpang Katis', 'Sungai Selan'];
        
        $kecamatan = urldecode($kecamatan);
        $isValid = false;
        foreach ($validKecamatans as $valid) {
            if (strtolower($valid) === strtolower($kecamatan)) {
                $kecamatan = $valid;
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            return null;
        }

        $kmps = Kmp::where('kecamatan', $kecamatan)
            ->where('status_pembentukan', 'verified')
            ->get();
            
        $koperasiList = [];
        foreach ($kmps as $kmp) {
            $koperasiList[] = [
                'id' => $kmp->id,
                'koperasi' => $kmp->nama_koperasi,
                'type' => $kmp->jenis,
                'operasional' => $kmp->status_operasional ? 'Sudah' : 'Belum',
                'no_ahu' => $kmp->no_ahu ?? 'Belum ada',
                'anggota' => rand(20, 150), // Mock anggota since it's not in schema yet
                'file_sertifikat' => $kmp->file_sertifikat,
            ];
        }

        return $koperasiList;
    }

    public function getKoperasiDetail(string $id): ?array
    {
        $kmp = Kmp::where('id', $id)->where('status_pembentukan', 'verified')->first();
        
        if (!$kmp) {
            return null;
        }

        return [
            'id' => $kmp->id,
            'title' => "Koperasi {$kmp->jenis} Merah Putih {$kmp->desa_kelurahan} Kecamatan {$kmp->kecamatan}",
            'koperasi' => $kmp->nama_koperasi,
            'jenis' => "Koperasi {$kmp->jenis}",
            'nomor_induk' => $kmp->nomor_induk ?? '-',
            'no_ahu' => $kmp->no_ahu ?? '-',
            'tanggal_ahu' => $kmp->tanggal_ahu ? date('d M Y', strtotime($kmp->tanggal_ahu)) : '-',
            'alamat' => $kmp->alamat ?? '-',
            'desa_kelurahan' => $kmp->desa_kelurahan,
            'kecamatan' => $kmp->kecamatan,
            'telp' => $kmp->telp ?? '-',
            'operasional' => $kmp->status_operasional ? 'Sudah' : 'Belum',
            'sertifikat' => $kmp->status_sertifikat === 'verified' ? 'Sudah' : 'Belum',
            'pelatihan' => $kmp->status_pelatihan ? 'Sudah' : 'Belum',
            'file_sertifikat' => $kmp->file_sertifikat,
            // Keep pengurus and keuangan as mock for now, as requested schema doesn't cover them yet
            'pengurus' => [
                'kepala' => ['nama' => 'Budi Santoso', 'foto' => null],
                'sekretaris' => ['nama' => 'Siti Aminah', 'foto' => null],
                'bendahara' => ['nama' => 'Ahmad Faisal', 'foto' => null],
            ],
            'keuangan' => [
                'simpanan_pokok' => rand(10, 50) * 1000000,
                'simpanan_wajib' => rand(5, 20) * 1000000,
            ],
        ];
    }
}
