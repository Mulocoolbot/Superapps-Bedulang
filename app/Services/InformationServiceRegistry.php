<?php

namespace App\Services;

use App\Services\KependudukanService;
use App\Services\KdmpService;
use App\Services\KesehatanService;
use App\Services\PendidikanService;
use InvalidArgumentException;

class InformationServiceRegistry
{
    protected array $services = [
        'pemerintahan' => PemerintahanService::class,
        'pendidikan' => PendidikanService::class,
        'kesehatan' => KesehatanService::class,
        'perindagkop-ukm' => PerindagService::class,
        'kdmp' => KdmpService::class,
        'hukum' => [
            'name' => 'Hukum',
            'icon' => '⚖️',
            'description' => 'Dashboard informasi Hukum & Regulasi Kabupaten Bangka Tengah.',
            'filters' => [],
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
        'kependudukan' => KependudukanService::class,
        'kepegawaian' => KepegawaianService::class,
        'pertanian' => PertanianService::class,
        'desa' => PertanianService::class,
        'informasi-publik' => InformasiPublikService::class,
        'pariwisata' => PariwisataService::class,
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
