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

        // Clear old records if any
        \App\Models\BloodStock::whereNotIn('blood_type', ['A', 'B', 'AB', 'O'])->delete();

        // Seed Default 4 Blood Stocks (A, B, AB, O)
        $defaultStocks = [
            ['blood_type' => 'A', 'amount' => 20],
            ['blood_type' => 'B', 'amount' => 25],
            ['blood_type' => 'AB', 'amount' => 12],
            ['blood_type' => 'O', 'amount' => 32],
        ];

        foreach ($defaultStocks as $stock) {
            \App\Models\BloodStock::updateOrCreate(
                ['blood_type' => $stock['blood_type']],
                ['amount' => $stock['amount'], 'unit' => 'Kantong']
            );
        }
    }
}
