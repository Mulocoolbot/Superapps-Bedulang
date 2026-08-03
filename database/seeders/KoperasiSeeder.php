<?php

namespace Database\Seeders;

use App\Models\Koperasi;
use App\Models\KoperasiImportLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KoperasiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear all dummy cooperative records & logs for clean real data input (Database agnostic)
        Koperasi::query()->delete();
        KoperasiImportLog::query()->delete();

        // 2. Create / Ensure Admin Koperasi User exists
        User::updateOrCreate(
            ['email' => 'admin.koperasi@example.com'],
            [
                'name' => 'Admin Koperasi Bangka Tengah',
                'password' => Hash::make('password'),
                'role' => 'admin_koperasi',
                'service_slug' => 'koperasi',
            ]
        );
    }
}
