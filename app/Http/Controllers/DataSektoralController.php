<?php

namespace App\Http\Controllers;

use App\Services\DataSektoralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DataSektoralController extends Controller
{
    protected DataSektoralService $service;

    public function __construct(DataSektoralService $service)
    {
        $this->service = $service;
    }

    /**
     * Render the Data Sektoral page.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('DataSektoral/Index');
    }

    /**
     * Get list of urusan (level 1).
     *
     * @return JsonResponse
     */
    public function getUrusan(): JsonResponse
    {
        $data = $this->service->getUrusan();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get list of sub urusan for a given urusan (level 2).
     *
     * @param string $urusan
     * @return JsonResponse
     */
    public function getSubUrusan(string $urusan): JsonResponse
    {
        $data = $this->service->getSubUrusan($urusan);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get list of indikator for a given urusan and sub_urusan (level 3).
     *
     * @param string $urusan
     * @param string $subUrusan
     * @return JsonResponse
     */
    public function getIndikator(string $urusan, string $subUrusan): JsonResponse
    {
        $data = $this->service->getIndikator($urusan, $subUrusan);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get sectoral data with year range filter (level 4).
     *
     * @param string $urusan
     * @param string $subUrusan
     * @param string $indikator
     * @param int $tahunAwal
     * @param int $tahunAkhir
     * @return JsonResponse
     */
    public function getData(string $urusan, string $subUrusan, string $indikator, int $tahunAwal, int $tahunAkhir): JsonResponse
    {
        // Validate year range
        if ($tahunAwal > $tahunAkhir) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun awal tidak boleh lebih besar dari tahun akhir.',
            ], 422);
        }

        if ($tahunAwal < 2000 || $tahunAkhir > (int) date('Y') + 1) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang tahun tidak valid.',
            ], 422);
        }

        $data = $this->service->getData($urusan, $subUrusan, $indikator, $tahunAwal, $tahunAkhir);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Download PDF report for sectoral data.
     *
     * @param string $urusan
     * @param string $subUrusan
     * @param string $indikator
     * @param int $tahunAwal
     * @param int $tahunAkhir
     */
    public function downloadPdf(string $urusan, string $subUrusan, string $indikator, int $tahunAwal, int $tahunAkhir)
    {
        return $this->service->downloadPdf($urusan, $subUrusan, $indikator, (int) $tahunAwal, (int) $tahunAkhir);
    }
}
