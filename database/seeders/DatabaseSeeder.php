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
            ['email' => 'pimpinan@example.com'],
            [
                'name' => 'Pimpinan',
                'password' => Hash::make('password'),
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
    }
}
