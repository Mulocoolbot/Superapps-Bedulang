<?php

namespace App\Services;

use Illuminate\Http\Request;

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
            'source_url' => 'https://dinpendik.bangkatengahkab.go.id/',
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

        // School totals
        $totalPaudSchools = collect($schoolsList)->where('jenjang', 'PAUD')->count();
        $totalSdSchools = collect($schoolsList)->where('jenjang', 'SD')->count();
        $totalSmpSchools = collect($schoolsList)->where('jenjang', 'SMP')->count();

        // Student totals
        $totalPaudStudents = collect($schoolsList)->where('jenjang', 'PAUD')->sum('jumlah_siswa');
        $totalSdStudents = collect($schoolsList)->where('jenjang', 'SD')->sum('jumlah_siswa');
        $totalSmpStudents = collect($schoolsList)->where('jenjang', 'SMP')->sum('jumlah_siswa');

        // Teacher totals
        $totalPaudTeachers = collect($schoolsList)->where('jenjang', 'PAUD')->sum('jumlah_guru');
        $totalSdTeachers = collect($schoolsList)->where('jenjang', 'SD')->sum('jumlah_guru');
        $totalSmpTeachers = collect($schoolsList)->where('jenjang', 'SMP')->sum('jumlah_guru');

        $availableKecamatans = collect($schoolsList)->pluck('kecamatan')->unique()->values()->all();
        $availableJenjangs = ['PAUD', 'SD', 'SMP'];
        $availableStatus = ['Negeri', 'Swasta'];

        return [
            'metadata' => [
                'source' => 'Dinas Pendidikan Kabupaten Bangka Tengah',
                'source_url' => 'https://dinpendik.bangkatengahkab.go.id/',
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
            'available_status' => $availableStatus,
        ];
    }

    /**
     * Get static data of schools in Bangka Tengah.
     *
     * @return array
     */
    protected function getSchoolsList(): array
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
            [
                'id' => 3,
                'nama' => 'KB Permata Hati Namang',
                'jenjang' => 'PAUD',
                'status' => 'Swasta',
                'kecamatan' => 'Namang',
                'jumlah_siswa' => 25,
                'jumlah_guru' => 3,
            ],
            [
                'id' => 4,
                'nama' => 'SDN 1 Koba',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kecamatan' => 'Koba',
                'jumlah_siswa' => 180,
                'jumlah_guru' => 14,
            ],
            [
                'id' => 5,
                'nama' => 'SDN 12 Pangkalan Baru',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kecamatan' => 'Pangkalan Baru',
                'jumlah_siswa' => 210,
                'jumlah_guru' => 16,
            ],
            [
                'id' => 6,
                'nama' => 'SDN 3 Lubuk Besar',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kecamatan' => 'Lubuk Besar',
                'jumlah_siswa' => 120,
                'jumlah_guru' => 10,
            ],
            [
                'id' => 7,
                'nama' => 'SD IT Al-Qudwah Simpang Katis',
                'jenjang' => 'SD',
                'status' => 'Swasta',
                'kecamatan' => 'Simpang Katis',
                'jumlah_siswa' => 160,
                'jumlah_guru' => 12,
            ],
            [
                'id' => 8,
                'nama' => 'SDN 5 Sungai Selan',
                'jenjang' => 'SD',
                'status' => 'Negeri',
                'kecamatan' => 'Sungai Selan',
                'jumlah_siswa' => 140,
                'jumlah_guru' => 11,
            ],
            [
                'id' => 9,
                'nama' => 'SMPN 1 Koba',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'kecamatan' => 'Koba',
                'jumlah_siswa' => 320,
                'jumlah_guru' => 24,
            ],
            [
                'id' => 10,
                'nama' => 'SMP Swasta Bakti Namang',
                'jenjang' => 'SMP',
                'status' => 'Swasta',
                'kecamatan' => 'Namang',
                'jumlah_siswa' => 95,
                'jumlah_guru' => 8,
            ],
            [
                'id' => 11,
                'nama' => 'SMPN 2 Pangkalan Baru',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'kecamatan' => 'Pangkalan Baru',
                'jumlah_siswa' => 280,
                'jumlah_guru' => 20,
            ],
            [
                'id' => 12,
                'nama' => 'SMPN 1 Simpang Katis',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'kecamatan' => 'Simpang Katis',
                'jumlah_siswa' => 210,
                'jumlah_guru' => 16,
            ],
            [
                'id' => 13,
                'nama' => 'SMPN 3 Lubuk Besar',
                'jenjang' => 'SMP',
                'status' => 'Negeri',
                'kecamatan' => 'Lubuk Besar',
                'jumlah_siswa' => 150,
                'jumlah_guru' => 12,
            ],
        ];
    }
}
