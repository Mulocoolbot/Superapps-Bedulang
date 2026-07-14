<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class InformationServiceController extends Controller
{
    public function show($slug)
    {
        $services = [
            'pemerintahan' => [
                'name' => 'Pemerintahan',
                'icon' => '🏛️',
                'description' => 'Dashboard informasi Pemerintahan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Harga Cabe', 'value' => 'harga-cabe'],
                    ['label' => 'Harga Terigu', 'value' => 'harga-terigu'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                ],
            ],
            'pendidikan' => [
                'name' => 'Pendidikan',
                'icon' => '🎓',
                'description' => 'Dashboard informasi Pendidikan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Jumlah Siswa', 'value' => 'jumlah-siswa'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'kesehatan' => [
                'name' => 'Kesehatan',
                'icon' => '🏥',
                'description' => 'Dashboard informasi Kesehatan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'perindagkop-ukm' => [
                'name' => 'Perindagkop-UKM',
                'icon' => '📈',
                'description' => 'Dashboard informasi Perindagkop dan UKM Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Telur', 'value' => 'harga-cabe'],
                    ['label' => 'Terigu', 'value' => 'harga-terigu'],
                    ['label' => 'Minyak Goreng', 'value' => 'gender'],
                ],
            ],
            'hukum' => [
                'name' => 'Hukum',
                'icon' => '⚖️',
                'description' => 'Dashboard informasi Hukum Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kasus', 'value' => 'kasus'],
                    ['label' => 'Gender', 'value' => 'gender'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                ],
            ],
            'sosial' => [
                'name' => 'Sosial',
                'icon' => '🤝',
                'description' => 'Dashboard informasi Sosial Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'kependudukan' => [
                'name' => 'Kependudukan',
                'icon' => '🪪',
                'description' => 'Dashboard informasi Kependudukan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'kepegawaian' => [
                'name' => 'Kepegawaian',
                'icon' => '👔',
                'description' => 'Dashboard informasi Kepegawaian Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Gender', 'value' => 'gender'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                ],
            ],
            'lingkungan' => [
                'name' => 'Lingkungan',
                'icon' => '🌿',
                'description' => 'Dashboard informasi Lingkungan Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Harga Cabe', 'value' => 'harga-cabe'],
                ],
            ],
            'geografis' => [
                'name' => 'Geografis',
                'icon' => '🗺️',
                'description' => 'Dashboard informasi Geografis Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Gender', 'value' => 'gender'],
                    ['label' => 'Harga Terigu', 'value' => 'harga-terigu'],
                ],
            ],
            'pariwisata' => [
                'name' => 'Pariwisata',
                'icon' => '🏖️',
                'description' => 'Dashboard informasi Pariwisata Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Harga Cabe', 'value' => 'harga-cabe'],
                    ['label' => 'Kematian', 'value' => 'kematian'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
            'data-sektoral' => [
                'name' => 'Data Sektoral',
                'icon' => '📊',
                'description' => 'Dashboard informasi Data Sektoral Kabupaten Bangka Tengah.',
                'filters' => [
                    ['label' => 'Kelahiran', 'value' => 'kelahiran'],
                    ['label' => 'Harga Terigu', 'value' => 'harga-terigu'],
                    ['label' => 'Gender', 'value' => 'gender'],
                ],
            ],
        ];

        if (! isset($services[$slug])) {
            abort(404);
        }

        $dashboard = [
            'summary' => [
                ['title' => 'Jumlah Data', 'value' => '1.248', 'meta' => 'Data terupdate hari ini'],
                ['title' => 'Layanan Aktif', 'value' => '42', 'meta' => 'Transaksi berjalan'],
                ['title' => 'Kepuasan', 'value' => '94%', 'meta' => 'Rata-rata pengguna'],
            ],
            'chart' => [
                'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                'series' => [18, 24, 19, 27, 31, 29, 35],
            ],
            'topData' => [
                ['title' => 'Data Terbanyak', 'value' => '412', 'note' => 'Laporan harian'],
                ['title' => 'Permintaan', 'value' => '89', 'note' => 'Dalam 24 jam'],
            ],
            'info' => [
                'title' => 'Status Sinkronisasi',
                'message' => 'Integrasi dengan sumber data OPD akan segera dipublikasikan secara real time.',
            ],
            'rows' => [
                ['name' => 'Layanan Digital', 'value' => '320', 'date' => '2026-07-12', 'status' => 'Aktif'],
                ['name' => 'Dashboard OPD', 'value' => '184', 'date' => '2026-07-11', 'status' => 'Baru'],
                ['name' => 'Peta Data', 'value' => '97', 'date' => '2026-07-10', 'status' => 'Rutin'],
            ],
        ];

        return Inertia::render('Front/Services/Show', [
            'service' => [
                ...$services[$slug],
                'slug' => $slug,
            ],
            'dashboard' => $dashboard,
        ]);
    }
}
