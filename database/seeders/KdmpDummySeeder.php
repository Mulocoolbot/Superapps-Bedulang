<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kmp;
use App\Models\KmpHistoryLog;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class KdmpDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Create Admin KDMP Keseluruhan
        $superAdmin = User::updateOrCreate(
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
        $kecamatanAdmins = [];

        // Create Admin Kecamatan
        foreach ($kecamatans as $kec) {
            $slug = strtolower(str_replace(' ', '', $kec));
            $adminKec = User::firstOrCreate(
                ['email' => "admin_{$slug}@example.com"],
                [
                    'name' => "Admin Kecamatan {$kec}",
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'service_slug' => 'kdmp',
                    'kdmp_role' => 'admin_kecamatan',
                    'kdmp_kecamatan' => $kec,
                ]
            );
            $kecamatanAdmins[$kec] = $adminKec;
        }

        // Create 100 KMP Admins and their KMPs
        for ($i = 1; $i <= 100; $i++) {
            $kecamatan = $kecamatans[array_rand($kecamatans)];
            $isDesa = (rand(0, 1) === 1);
            $jenis = $isDesa ? 'Desa' : 'Kelurahan';
            $desaKelurahan = $faker->citySuffix . ' ' . $faker->lastName;

            // Admin KMP
            $adminKmp = User::create([
                'name' => "Admin KMP {$i} {$kecamatan}",
                'email' => "admin_kmp{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'admin',
                'service_slug' => 'kdmp',
                'kdmp_role' => 'admin_kmp',
                'kdmp_kecamatan' => $kecamatan,
            ]);

            // Create KMP
            $kmp = Kmp::create([
                'user_id' => $adminKmp->id,
                'kecamatan' => $kecamatan,
                'desa_kelurahan' => $desaKelurahan,
                'jenis' => $jenis,
                'nama_koperasi' => "Koperasi {$jenis} Merah Putih {$desaKelurahan}",
                'nomor_induk' => 'NIB-' . $faker->numerify('#######'),
                'no_ahu' => 'AHU-' . $faker->numerify('####') . '.AH.01.01.Tahun ' . date('Y'),
                'tanggal_ahu' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'alamat' => $faker->address,
                'telp' => $faker->phoneNumber,
                'status_operasional' => $faker->boolean(80),
                'status_sertifikat' => $faker->randomElement(['pending', 'verified', 'rejected']),
                'status_pembentukan' => $faker->randomElement(['pending_verification', 'verified', 'verified', 'verified', 'rejected']), // Skew to verified
            ]);

            // Generate History Logs
            // 1. Log pembuatan
            KmpHistoryLog::create([
                'kmp_id' => $kmp->id,
                'user_id' => $adminKmp->id,
                'action' => 'create_kmp',
                'description' => 'Admin KMP membuat KMP baru.',
                'created_at' => $faker->dateTimeBetween('-1 month', '-2 weeks'),
            ]);

            // 2. Log verifikasi kecamatan
            if ($kmp->status_pembentukan !== 'pending_verification') {
                KmpHistoryLog::create([
                    'kmp_id' => $kmp->id,
                    'user_id' => $kecamatanAdmins[$kecamatan]->id,
                    'action' => 'verify_pembentukan',
                    'description' => "Admin Kecamatan " . ($kmp->status_pembentukan === 'verified' ? 'menyetujui' : 'menolak') . " pembentukan KMP.",
                    'created_at' => $faker->dateTimeBetween('-2 weeks', '-1 week'),
                ]);
            }
            
            // 3. Log random data edit request
            if (rand(1, 100) <= 30) {
                KmpHistoryLog::create([
                    'kmp_id' => $kmp->id,
                    'user_id' => $adminKmp->id,
                    'action' => 'request_edit',
                    'description' => 'Admin KMP mengajukan perubahan data.',
                    'created_at' => clone $kmp->created_at,
                ]);
            }
        }
    }
}
