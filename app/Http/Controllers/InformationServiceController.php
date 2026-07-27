<?php

namespace App\Http\Controllers;

use App\Services\InformationServiceRegistry;
use Inertia\Inertia;

class InformationServiceController extends Controller
{
    protected InformationServiceRegistry $registry;

    /**
     * InformationServiceController constructor.
     *
     * @param InformationServiceRegistry $registry
     */
    public function __construct(InformationServiceRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Display the specified information service dashboard.
     *
     * @param string $slug
     * @return \Inertia\Response
     */
    public function show($slug)
    {
        if (! $this->registry->has($slug)) {
            abort(404);
        }

        $service = $this->registry->resolve($slug);
        
        $metadata = $service->getMetadata();
        $dashboard = $service->getDashboardData(request());

        return Inertia::render('Front/Services/Show', [
            'service' => [
                ...$metadata,
                'slug' => $slug,
            ],
            'dashboard' => $dashboard,
        ]);
    }

    public function showKdmpKecamatan($nama)
    {
        $service = $this->registry->resolve('kdmp');
        $metadata = $service->getMetadata();
        
        $koperasiDetails = app(\App\Services\KdmpService::class)->getKecamatanDetails($nama);

        if (!$koperasiDetails) {
            abort(404);
        }

        return Inertia::render('Front/Services/Dashboards/KdmpKecamatanDetails', [
            'service' => [
                ...$metadata,
                'slug' => 'kdmp',
            ],
            'kecamatan' => $nama,
            'koperasiDetails' => $koperasiDetails,
        ]);
    }

    public function showKdmpKoperasi($id)
    {
        $service = $this->registry->resolve('kdmp');
        $metadata = $service->getMetadata();
        
        $koperasi = app(\App\Services\KdmpService::class)->getKoperasiDetail($id);

        if (!$koperasi) {
            abort(404);
        }

        return Inertia::render('Front/Services/Dashboards/KdmpKoperasiDetails', [
            'service' => [
                ...$metadata,
                'slug' => 'kdmp',
            ],
            'koperasi' => $koperasi,
        ]);
    }
}

