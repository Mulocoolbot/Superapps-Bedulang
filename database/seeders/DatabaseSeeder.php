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
        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'service_slug' => null,
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
            ['email' => 'admin.utd@example.com'],
            [
                'name' => 'Admin UTD Bangka Tengah',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'service_slug' => 'informasi-publik',
            ]
        );

        User::updateOrCreate(
            ['email' => 'pimpinan@email.com'],
            [
                'name' => 'Pimpinan',
                'password' => Hash::make('qwertyu'),
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

        $utdUser = User::where('email', 'admin.utd@example.com')->first();
        $adminUser = User::where('email', 'admin.kesehatan@example.com')->first();
        $superAdminUser = User::where('email', 'superadmin@example.com')->first();
        $updaters = array_filter([$utdUser?->id, $adminUser?->id, $superAdminUser?->id]);

        $components = ['Whole Blood (WB)', 'Packed Red Blood (PRC)'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

        // Initial default stock values for matrix
        $initialAmounts = [
            'Whole Blood (WB)' => [
                'A+' => 18, 'A-' => 5, 'B+' => 22, 'B-' => 6,
                'O+' => 35, 'O-' => 10, 'AB+' => 12, 'AB-' => 4,
            ],
            'Packed Red Blood (PRC)' => [
                'A+' => 24, 'A-' => 8, 'B+' => 28, 'B-' => 7,
                'O+' => 42, 'O-' => 12, 'AB+' => 15, 'AB-' => 6,
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
