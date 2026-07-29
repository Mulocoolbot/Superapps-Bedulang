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

        if ($slug !== 'pertanian' && $slug !== 'desa' && !auth()->check()) {
            return redirect()->guest(route('login'));
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
}

