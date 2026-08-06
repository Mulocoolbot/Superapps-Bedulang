<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kmp;
use Illuminate\Support\Facades\Hash;

class KdmpRealSeeder extends Seeder
{
    /**
     * Seed KDMP records from spreadsheet data (63 items).
     *
     * @return void
     */
    public function run()
    {
        $adminKdmp = User::updateOrCreate(
            ['email' => 'adminkdmp36@gmail.com'],
            [
                'name' => 'Admin KDMP Keseluruhan',
                'password' => Hash::make('adminkdmp12345678'),
                'role' => 'admin',
                'service_slug' => 'kdmp',
                'kdmp_role' => 'admin_kdmp',
                'kdmp_admin_status' => 'approved',
            ]
        );

        $kecamatans = ['Koba', 'Pangkalan Baru', 'Namang', 'Lubuk Besar', 'Simpang Katis', 'Sungai Selan'];

        // Remove old example.com KDMP users and admin_kmp users if any exist
        User::where('kdmp_role', 'admin_kmp')->delete();
        User::where('kdmp_role', 'admin_kecamatan')
            ->where('email', 'like', '%@example.com')
            ->delete();

        foreach ($kecamatans as $kec) {
            $slug = strtolower(str_replace(' ', '', $kec));
            User::updateOrCreate(
                ['email' => "admin_{$slug}@gmail.com"],
                [
                    'name' => "Admin Kecamatan {$kec}",
                    'password' => Hash::make("admin{$slug}12345678"),
                    'role' => 'admin',
                    'service_slug' => 'kdmp',
                    'kdmp_role' => 'admin_kecamatan',
                    'kdmp_kecamatan' => $kec,
                ]
            );
        }

        // Reset existing Kmp table
        Kmp::query()->delete();

        $items = [
            // KOBA (11)
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH ARUNG DALAM',
                'alamat' => 'JALAN SOEKARNO HATTA II',
                'desa_kelurahan' => 'ARUNG DALAM',
                'kecamatan' => 'Koba',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH BEROK',
                'alamat' => 'JALAN KANTOR KELURAHAN BEROK',
                'desa_kelurahan' => 'BEROK',
                'kecamatan' => 'Koba',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH GUNTUNG KECAMATAN KOBA',
                'alamat' => 'KANTOR KEPALA DESA GUNTUNG, JALAN RAYA DESA GUNTUNG',
                'desa_kelurahan' => 'GUNTUNG',
                'kecamatan' => 'Koba',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH KOBA KECAMATAN KOBA',
                'alamat' => 'JALAN DEPATI AMIR',
                'desa_kelurahan' => 'KOBA',
                'kecamatan' => 'Koba',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KURAU BARAT',
                'alamat' => 'KANTOR DESA KURAU BARAT, JALAN RAYA KOBA KM. 30',
                'desa_kelurahan' => 'KURAU BARAT',
                'kecamatan' => 'Koba',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KURAU TIMUR',
                'alamat' => 'JALAN TRANS, DUSUN BERKREASI DESA KURAU (TIMUR)',
                'desa_kelurahan' => 'KURAU (TIMUR)',
                'kecamatan' => 'Koba',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH NIBUNG KECAMATAN KOBA',
                'alamat' => 'JALAN RAYA DESA NIBUNG',
                'desa_kelurahan' => 'NIBUNG',
                'kecamatan' => 'Koba',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH PADANG MULIA',
                'alamat' => 'KANTOR KELURAHAN PADANG MULIA, JALAN SINAR BULAN',
                'desa_kelurahan' => 'PADANG MULIA',
                'kecamatan' => 'Koba',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PENYAK',
                'alamat' => 'KANTOR KADES LAMA',
                'desa_kelurahan' => 'PENYAK',
                'kecamatan' => 'Koba',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH SIMPANG PERLANG',
                'alamat' => 'JALAN SOEKARNO HATTA III',
                'desa_kelurahan' => 'SIMPANG PERLANG',
                'kecamatan' => 'Koba',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH TERENTANG III',
                'alamat' => 'JALAN LINGKAR DESA TERENTANG III',
                'desa_kelurahan' => 'TERENTANG III',
                'kecamatan' => 'Koba',
                'jenis' => 'Desa',
            ],

            // PANGKALAN BARU (12)
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH AIR MESU',
                'alamat' => 'JALAN AMANAH',
                'desa_kelurahan' => 'AIR MESU',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH AIR MESU TIMUR',
                'alamat' => 'JALAN KOBA KM 11, DESA AIR MESU TIMUR, KECAMATAN PANGKALAN BARU, KABUPATEN BANGKA TENGAH',
                'desa_kelurahan' => 'AIR MESU TIMUR',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BATU BELUBANG KECAMATAN PANGKALAN BARU',
                'alamat' => 'DUSUN PANTAI',
                'desa_kelurahan' => 'BATU BELUBANG',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BELULUK',
                'alamat' => 'JALAN KOBA KM. 65',
                'desa_kelurahan' => 'BELULUK',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BENTENG KECAMATAN PANGKALAN BARU',
                'alamat' => 'JALAN RAYA BENTENG DEPAN SD PANCASILA NOMOR 01 DESA BENTENG',
                'desa_kelurahan' => 'BENTENG',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH DUL',
                'alamat' => 'JALAN RAYA DUL DEPAN SMP 1 PANGKALANBARU KECAMATAN PANGKALANBARU KABUPATEN BANGKA TENGAH',
                'desa_kelurahan' => 'DUL',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH JERUK KECAMATAN PANGKALAN BARU',
                'alamat' => 'JALAN RAYA KOBA',
                'desa_kelurahan' => 'JERUK',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH MANGKOL',
                'alamat' => 'JALAN SELAN KM. 5,5 BUKIT BERANG',
                'desa_kelurahan' => 'MANGKOL',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PADANG BARU KECAMATAN PANGKALAN BARU',
                'alamat' => 'JALAN RAYA DESA PADANG BARU',
                'desa_kelurahan' => 'PADANG BARU',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PEDINDANG',
                'alamat' => 'JALAN SUNGAI SELAN KM 7, DESA PEDINDANG, KECAMATAN PANGKALAN BARU, KABUPATEN BANGKA TENGAH',
                'desa_kelurahan' => 'PEDINDANG',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH TANJUNG GUNUNG KECAMATAN PANGKALAN BARU',
                'alamat' => 'JALAN BINJAI PASIR RAYA',
                'desa_kelurahan' => 'TANJUNG GUNUNG',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KEBINTIK',
                'alamat' => 'JALAN KELINTANG NOMOR 1 DUSUN KEBINTIK',
                'desa_kelurahan' => 'ROMADHON',
                'kecamatan' => 'Pangkalan Baru',
                'jenis' => 'Desa',
            ],

            // SUNGAI SELAN (13)
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KEMINGKING',
                'alamat' => 'KANTOR DESA KEMINGKING',
                'desa_kelurahan' => 'KEMINGKING',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KERAKAS',
                'alamat' => 'JALAN RAYA PAYUNG DUSUN KERAKAS BAWAH',
                'desa_kelurahan' => 'KERAKAS',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KERANTAI',
                'alamat' => 'JALAN RAYA DESA KERANTAI',
                'desa_kelurahan' => 'KERANTAI',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KERETAK',
                'alamat' => 'JALAN LAMPUR',
                'desa_kelurahan' => 'KERETAK',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KERETAK ATAS',
                'alamat' => 'JALAN SUNGAI SELAN',
                'desa_kelurahan' => 'KERETAK ATAS',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH LAMPUR',
                'alamat' => 'JALAN DURIAN',
                'desa_kelurahan' => 'LAMPUR',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH MUNGGU KECAMATAN SUNGAI SELAN',
                'alamat' => 'KANTOR DESA MUNGGU, JALAN RAYA DESA MUNGGU',
                'desa_kelurahan' => 'MUNGGU',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH ROMADHON',
                'alamat' => 'KANTOR DESA ROMADHON, JALAN RAYA ROMADHON',
                'desa_kelurahan' => 'ROMADHON',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH SARANGMANDI',
                'alamat' => 'JALAN LINGKAR DESA',
                'desa_kelurahan' => 'SARANG MANDI',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH SUNGAI SELAN ATAS',
                'alamat' => 'KANTOR DESA SUNGAI SELAN ATAS, JALAN DEPATI AMIR KECAMATAN SUNGAI SELAN KABUPATEN BANGKA TENGAH',
                'desa_kelurahan' => 'SUNGAI SELAN ATAS',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI KELURAHAN MERAH PUTIH SUNGAI SELAN',
                'alamat' => 'JALAN BATIN TIKAL',
                'desa_kelurahan' => 'SUNGAI SELAN',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Kelurahan',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH TANJUNG PURA KECAMATAN SUNGAI SELAN',
                'alamat' => 'KANTOR DESA TANJUNG PURA, JALAN BAHAGIA',
                'desa_kelurahan' => 'TANJUNG PURA',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH MELABUN',
                'alamat' => 'KANTOR DESA MELABUN, JALAN BARU DESA',
                'desa_kelurahan' => 'MELABUN',
                'kecamatan' => 'Sungai Selan',
                'jenis' => 'Desa',
            ],

            // SIMPANG KATIS (10)
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BERUAS KECAMATAN SIMPANG KATIS',
                'alamat' => 'JALAN SUNGAISELAN KM.18',
                'desa_kelurahan' => 'BERUAS',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH CELUAK',
                'alamat' => 'JALAN AIR TEBAT',
                'desa_kelurahan' => 'CELUAK',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KATIS',
                'alamat' => 'JALAN MERDEKA NOMOR 2',
                'desa_kelurahan' => 'KATIS',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PASIR GARAM KECAMATAN SIMPANG KATIS',
                'alamat' => 'KANTOR DESA, JALAN SUNGAISELAN KM. 11, DUSUN I',
                'desa_kelurahan' => 'PASIR GARAM',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PINANG SEBATANG KECAMATAN SIMPANG KATIS',
                'alamat' => 'DESA PINANG SEBATANG',
                'desa_kelurahan' => 'PINANG SEBATANG',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PUPUT KECAMATAN SIMPANG KATIS',
                'alamat' => 'JALAN MERDEKA NO. 02, DUSUN BARAT',
                'desa_kelurahan' => 'PUPUT',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH SIMPANG KATIS',
                'alamat' => 'JALAN RAYA DESA SIMPANGKATIS KM.22',
                'desa_kelurahan' => 'SIMPANG KATIS',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH SUNGKAP',
                'alamat' => 'KANTOR DESA, JALAN MERDEKA, DUSUN 02',
                'desa_kelurahan' => 'SUNGKAP',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH TERAK',
                'alamat' => 'JALAN SINAR PAGI',
                'desa_kelurahan' => 'TERAK',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH TERU',
                'alamat' => 'JALAN LAP. BOLA',
                'desa_kelurahan' => 'TERU',
                'kecamatan' => 'Simpang Katis',
                'jenis' => 'Desa',
            ],

            // LUBUK BESAR (9)
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BATU BERIGA',
                'alamat' => 'GEDUNG KESENIAN',
                'desa_kelurahan' => 'BATU BERIGA',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KULUR KECAMATAN LUBUK BESAR',
                'alamat' => 'JALAN RAYA DESA KULUR',
                'desa_kelurahan' => 'KULUR',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KULUR ILIR',
                'alamat' => 'DESA KULUR ILIR',
                'desa_kelurahan' => 'KULUR ILIR',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH LUBUK BESAR KECAMATAN LUBUK BESAR',
                'alamat' => 'JALAN RAYA LUBUK BESAR',
                'desa_kelurahan' => 'LUBUK BESAR',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH LUBUK LINGKUK',
                'alamat' => 'JALAN RAYA LUBUK BESAR',
                'desa_kelurahan' => 'LUBUK LINGKUK',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH LUBUK PABRIK',
                'alamat' => 'JALAN JENDRAL SUDIRMAN',
                'desa_kelurahan' => 'LUBUK PABRIK',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH PERLANG',
                'alamat' => 'KANTOR DESA JALAN RAYA DESA PERLANG',
                'desa_kelurahan' => 'PERLANG',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH TRUBUS KECAMATAN LUBUK BESAR',
                'alamat' => 'JALAN RAYA TRUBUS',
                'desa_kelurahan' => 'TRUBUS',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BELIMBING KECAMATAN LUBUK BESAR',
                'alamat' => 'DUSUN 1 DESA BELIMBING',
                'desa_kelurahan' => 'BELIMBING',
                'kecamatan' => 'Lubuk Besar',
                'jenis' => 'Desa',
            ],

            // NAMANG (8)
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BASKARA BAKTI',
                'alamat' => 'GEDUNG SERBAGUNA JALAN RAYA MAKOREM',
                'desa_kelurahan' => 'BHASKARA BHAKTI',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BUKIT KIJANG KECAMATAN NAMANG',
                'alamat' => 'JALAN DESA BUKIT KIJANG',
                'desa_kelurahan' => 'BUKIT KIJANG',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH BELILIK',
                'alamat' => 'KANTOR DESA BELILIK, JALAN DESA 1',
                'desa_kelurahan' => 'BELILIK',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH CAMBAI SELATAN',
                'alamat' => 'KANTOR DESA CAMBAI SELATAN, JALAN RAYA KOBA KM. 17',
                'desa_kelurahan' => 'CAMBAI SELATAN',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH CAMBAI KECAMATAN NAMANG',
                'alamat' => 'JALAN RAYA KOBA KM. 16 DUSUN II',
                'desa_kelurahan' => 'CAMBAI',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH JELUTUNG KECAMATAN NAMANG',
                'alamat' => 'DESA JELUTUNG',
                'desa_kelurahan' => 'JELUTUNG',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH KAYU BESI KECAMATAN NAMANG',
                'alamat' => 'DESA KAYU BESI KECAMATAN NAMANG',
                'desa_kelurahan' => 'KAYU BESI',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
            [
                'nama_koperasi' => 'KOPERASI DESA MERAH PUTIH NAMANG',
                'alamat' => 'JALAN RAYA KOBA KM 24',
                'desa_kelurahan' => 'NAMANG',
                'kecamatan' => 'Namang',
                'jenis' => 'Desa',
            ],
        ];

        foreach ($items as $item) {
            Kmp::create([
                'user_id' => $adminKdmp->id,
                'kecamatan' => $item['kecamatan'],
                'desa_kelurahan' => $item['desa_kelurahan'],
                'jenis' => $item['jenis'],
                'nama_koperasi' => $item['nama_koperasi'],
                'nomor_induk' => null,
                'no_ahu' => null,
                'tanggal_ahu' => null,
                'alamat' => $item['alamat'],
                'telp' => null,
                'status_operasional' => false,
                'status_sertifikat' => 'pending',
                'status_pelatihan' => 'belum',
                'status_pembentukan' => 'verified',
                'jumlah_anggota' => 0,
                'jumlah_pengawas' => 0,
                'jumlah_pengurus' => 0,
                'simpanan_pokok' => 0,
                'simpanan_wajib' => 0,
            ]);
        }
    }
}
