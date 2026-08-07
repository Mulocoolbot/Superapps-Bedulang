<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
    }
}

