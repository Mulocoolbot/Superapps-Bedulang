<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class AppLaunchController extends Controller
{
    /**
     * Launch an application after checking its availability.
     */
    public function launch(Request $request)
    {
        $url = $request->query('url');
        $name = $request->query('name', 'Aplikasi');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return Inertia::render('Front/Maintenance', [
                'appName' => $name,
                'targetUrl' => $url ?? '',
                'checkedAt' => now()->format('H:i:s, d M Y'),
                'reason' => 'URL aplikasi tidak valid atau tidak dikonfigurasi.',
            ]);
        }

        $isOnline = $this->pingUrl($url);

        if ($isOnline) {
            return redirect()->away($url);
        }

        return Inertia::render('Front/Maintenance', [
            'appName' => $name,
            'targetUrl' => $url,
            'checkedAt' => now()->format('H:i:s, d M Y'),
            'reason' => 'Server tidak merespons atau mengalami masalah koneksi.',
        ]);
    }

    /**
     * API endpoint to recheck app status dynamically via AJAX.
     */
    public function checkStatus(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'is_online' => false,
                'reason' => 'URL tidak valid',
            ]);
        }

        $isOnline = $this->pingUrl($url);

        return response()->json([
            'is_online' => $isOnline,
            'checked_at' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Render maintenance view directly if requested.
     */
    public function showMaintenance(Request $request)
    {
        return Inertia::render('Front/Maintenance', [
            'appName' => $request->query('name', 'Aplikasi Daerah'),
            'targetUrl' => $request->query('url', ''),
            'checkedAt' => now()->format('H:i:s, d M Y'),
            'reason' => 'Website sedang dalam pemeliharaan berkala.',
        ]);
    }

    /**
     * Check if a URL is reachable.
     */
    protected function pingUrl(string $url): bool
    {
        try {
            $response = Http::timeout(3)
                ->connectTimeout(3)
                ->withOptions([
                    'verify' => false,
                    'allow_redirects' => true,
                ])
                ->get($url);

            // Accept 2xx, 3xx, and 401/403 (server exists and responds)
            $statusCode = $response->status();
            return $statusCode < 400 || in_array($statusCode, [401, 403]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
