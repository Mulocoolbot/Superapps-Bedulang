<?php

namespace App\Services;

use InvalidArgumentException;

class InformationServiceRegistry
{
    protected array $services = [
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
        'perindagkop-ukm' => PerindagService::class,
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
        'lingkungan' => DkpService::class,
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
        'makanan-bergizi-gratis' => MbgService::class,
        'perizinan-terpadu' => [
            'name' => 'Perizinan Terpadu',
            'icon' => '🏛️',
            'description' => 'Portal Layanan Perizinan Terpadu Kabupaten Bangka Tengah.',
            'filters' => [],
        ],
        'pelayanan-kesehatan' => [
            'name' => 'Pelayanan Kesehatan',
            'icon' => '🏥',
            'description' => 'Portal Pelayanan Kesehatan Kabupaten Bangka Tengah.',
            'filters' => [],
        ],
        'pengaduan-ppid' => [
            'name' => 'Pengaduan PPID',
            'icon' => '📢',
            'description' => 'Portal Layanan Pengaduan PPID Kabupaten Bangka Tengah.',
            'filters' => [],
        ],
        'administrasi-warga' => [
            'name' => 'Administrasi Warga',
            'icon' => '🪪',
            'description' => 'Portal Layanan Administrasi Warga Kabupaten Bangka Tengah.',
            'filters' => [],
        ],
    ];

    public function has(string $slug): bool
    {
        return isset($this->services[$slug]);
    }

    public function resolve(string $slug): InformationServiceInterface
    {
        if (!$this->has($slug)) {
            throw new InvalidArgumentException("Service with slug '{$slug}' is not registered.");
        }

        $serviceConfig = $this->services[$slug];

        if (is_string($serviceConfig) && class_exists($serviceConfig)) {
            return app($serviceConfig);
        }

        return new DefaultInformationService($slug, $serviceConfig);
    }
}
