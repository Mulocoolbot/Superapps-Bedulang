<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class CctvProxyController extends Controller
{
    public function proxy(Request $request, $base64_url, $file)
    {
        // Decode the base URL from base64
        $baseUrl = $this->base64UrlDecode($base64_url);
        
        if (!$baseUrl) {
            return response()->json(['error' => 'Invalid stream path'], 400);
        }

        // Construct the full target stream URL
        $targetUrl = rtrim($baseUrl, '/') . '/' . $file;

        // Resolve query parameters if any
        $queryString = $request->getQueryString();
        if ($queryString) {
            $targetUrl .= '?' . $queryString;
        }

        // Determine correct headers and content type based on file extension
        $contentType = 'application/octet-stream';
        if (str_ends_with($file, '.m3u8')) {
            $contentType = 'application/x-mpegURL';
        } elseif (str_ends_with($file, '.ts')) {
            $contentType = 'video/MP2T';
        }

        // Build list of URLs to try (local first, then remote)
        $urlsToTry = [];

        // If the target points to the production IP or domain, try localhost first
        if (str_contains($targetUrl, '157.10.109.241') || str_contains($targetUrl, 'bedulang.bangkatengahkab.go.id')) {
            $localUrl = str_replace(
                ['157.10.109.241', 'bedulang.bangkatengahkab.go.id'],
                '127.0.0.1',
                $targetUrl
            );
            $urlsToTry[] = ['url' => $localUrl, 'timeout' => 3, 'label' => 'localhost'];
        }

        // Always add the original URL as fallback
        $urlsToTry[] = ['url' => $targetUrl, 'timeout' => 8, 'label' => 'remote'];

        // Try each URL using cURL for proper HTTP status handling
        foreach ($urlsToTry as $attempt) {
            $result = $this->fetchWithCurl($attempt['url'], $attempt['timeout']);

            if ($result !== null) {
                Log::debug("CCTV Proxy: Serving from {$attempt['label']}: {$attempt['url']}");

                return response($result['body'], 200, [
                    'Content-Type' => $contentType,
                    'Content-Length' => strlen($result['body']),
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        }

        // All attempts failed
        Log::warning("CCTV Proxy: All attempts failed for: {$targetUrl}");
        return response()->json(['error' => 'CCTV stream offline or unavailable'], 404);
    }

    /**
     * Fetch URL content with cURL. Returns ['body' => string, 'status' => int] on success, null on failure.
     */
    private function fetchWithCurl(string $url, int $timeout): ?array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_FAILONERROR => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT => 'BedulangCCTVProxy/1.0',
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode < 200 || $httpCode >= 400) {
            Log::debug("CCTV Proxy: Attempt failed for {$url} - HTTP {$httpCode} - {$error}");
            return null;
        }

        return ['body' => $body, 'status' => $httpCode];
    }

    private function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
