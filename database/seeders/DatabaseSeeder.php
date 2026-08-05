<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(KoperasiSeeder::class);
        $this->call(KdmpRealSeeder::class);

        User::updateOrCreate(
            ['email' => 'bedulangsuperadmin@gmail.com'],
            [
                'name' => 'Super Admin Bedulang',
                'password' => Hash::make('superadmin12345678'),
                'role' => 'super_admin',
                'service_slug' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'adminbedulang@gmail.com'],
            [
                'name' => 'Admin Website Bedulang',
                'password' => Hash::make('adminbedulang12345678'),
                'role' => 'admin',
                'service_slug' => 'pengaduan-bedulang',
            ]
        );

        User::updateOrCreate(
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

        User::updateOrCreate(
            ['email' => 'admin.perindag@example.com'],
            [
                'name' => 'Admin Perindag',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'service_slug' => 'perindagkop-ukm',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin.kesehatan@example.com'],
            [
                'name' => 'Admin Kesehatan',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'service_slug' => 'kesehatan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'rsud.abuhanifah@bangkatengahkab.go.id'],
            [
                'name' => 'Admin RSUD Abuhanifah',
                'password' => Hash::make('Bateng_2026'),
                'role' => 'admin',
                'service_slug' => 'informasi-publik',
            ]
        );

        User::updateOrCreate(
            ['email' => 'bupati@bangkatengahkab.go.id'],
            [
                'name' => 'Bupati Bangka Tengah',
                'password' => Hash::make('Bateng_2026'),
                'role' => 'pimpinan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'wabup@bangkatengahkab.go.id'],
            [
                'name' => 'Wakil Bupati Bangka Tengah',
                'password' => Hash::make('Bateng_2026'),
                'role' => 'pimpinan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'sekda@bangkatengahkab.go.id'],
            [
                'name' => 'Sekda Bangka Tengah',
                'password' => Hash::make('Bateng_2026'),
                'role' => 'pimpinan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Publik Test',
                'password' => Hash::make('password'),
                'role' => 'publik',
            ]
        );

        $utdUser = User::where('email', 'rsud.abuhanifah@bangkatengahkab.go.id')->first();
        $adminUser = User::where('email', 'admin.kesehatan@example.com')->first();
        $superAdminUser = User::where('email', 'bedulangsuperadmin@gmail.com')->first();
        $updaters = array_filter([$utdUser?->id, $adminUser?->id, $superAdminUser?->id]);

        $components = ['Whole Blood (WB)', 'Packed Red Blood (PRC)'];
        $bloodTypes = ['A+', 'B+', 'O+', 'AB+'];

        // Initial default stock values for matrix
        $initialAmounts = [
            'Whole Blood (WB)' => [
                'A+' => 18, 'B+' => 22, 'O+' => 35, 'AB+' => 12,
            ],
            'Packed Red Blood (PRC)' => [
                'A+' => 24, 'B+' => 28, 'O+' => 42, 'AB+' => 15,
            ],
        ];

        foreach ($components as $comp) {
            foreach ($bloodTypes as $bt) {
                \App\Models\BloodStock::updateOrCreate(
                    [
                        'component' => $comp,
                        'blood_type' => $bt,
                    ],
                    [
                        'amount' => $initialAmounts[$comp][$bt] ?? 10,
                        'unit' => 'Kantong',
                        'updated_by' => $updaters[array_rand($updaters)] ?? null,
                    ]
                );
            }
        }

        // Seed 100 dummy change logs in blood_stock_logs
        \App\Models\BloodStockLog::truncate();

        $now = \Carbon\Carbon::now();
        for ($i = 100; $i >= 1; $i--) {
            $comp = $components[array_rand($components)];
            $bt = $bloodTypes[array_rand($bloodTypes)];
            $oldAmount = rand(5, 45);
            $delta = rand(-8, 12);
            if ($delta === 0) $delta = 3;
            $newAmount = max(0, $oldAmount + $delta);
            $actualDelta = $newAmount - $oldAmount;
            $updaterId = $updaters[array_rand($updaters)] ?? null;

            $actionText = $actualDelta > 0 ? "Penambahan stok (+{$actualDelta} kantong)" : "Pengurangan stok ({$actualDelta} kantong)";
            $description = "{$actionText} pada {$comp} Gol. {$bt}";

            // Distribute timestamps back over the last 30 days
            $timestamp = $now->copy()->subHours($i * 5)->subMinutes(rand(5, 55));

            \App\Models\BloodStockLog::create([
                'component' => $comp,
                'blood_type' => $bt,
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'delta' => $actualDelta,
                'description' => $description,
                'updated_by' => $updaterId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
}
