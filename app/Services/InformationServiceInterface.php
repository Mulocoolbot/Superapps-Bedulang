<?php

namespace App\Services;

use Illuminate\Http\Request;

interface InformationServiceInterface
{
    /**
     * Get the service metadata (filters, name, icon, description, etc.).
     *
     * @return array
     */
    public function getMetadata(): array;

    /**
     * Get the dashboard data for the service.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request): array;
}
