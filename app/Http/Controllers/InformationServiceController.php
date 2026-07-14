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
                'description' => 'Dashboard informasi Pemerintahan Kabupaten Bangka Tengah.'
            ],

            'pendidikan' => [
                'name' => 'Pendidikan',
                'icon' => '🎓',
                'description' => 'Dashboard informasi Pendidikan Kabupaten Bangka Tengah.'
            ],

            'kesehatan' => [
                'name' => 'Kesehatan',
                'icon' => '🏥',
                'description' => 'Dashboard informasi Kesehatan Kabupaten Bangka Tengah.'
            ],

            'pariwisata' => [
                'name' => 'Pariwisata',
                'icon' => '🏖️',
                'description' => 'Dashboard informasi Pariwisata Kabupaten Bangka Tengah.'
            ],

            'pertanian' => [
                'name' => 'Pertanian',
                'icon' => '🌾',
                'description' => 'Dashboard informasi Pertanian Kabupaten Bangka Tengah.'
            ],

            'perikanan' => [
                'name' => 'Perikanan',
                'icon' => '🐟',
                'description' => 'Dashboard informasi Perikanan Kabupaten Bangka Tengah.'
            ],

            'disperindag' => [
                'name' => 'Perdagangan',
                'icon' => '📈',
                'description' => 'Dashboard informasi harga dan perdagangan.'
            ],

        ];

        if (! isset($services[$slug])) {
            abort(404);
        }

        return Inertia::render('Front/Services/Show', [
            'service' => $services[$slug],
        ]);
    }
}