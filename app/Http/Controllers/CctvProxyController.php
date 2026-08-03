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
        $streamUrl = rtrim($baseUrl, '/') . '/' . $file;

        // Resolve query parameters if any
        $queryString = $request->getQueryString();
        if ($queryString) {
            $streamUrl .= '?' . $queryString;
        }

        // Determine correct headers and content type based on file extension
        $contentType = 'application/octet-stream';
        if (str_ends_with($file, '.m3u8')) {
            $contentType = 'application/x-mpegURL';
        } elseif (str_ends_with($file, '.ts')) {
            $contentType = 'video/MP2T';
        }

        // Setup stream context with timeout
        $context = stream_context_create([
            'http' => [
                'timeout' => 5, // 5 seconds
                'follow_location' => 1,
            ]
        ]);

        try {
            // Open the remote stream
            $fp = @fopen($streamUrl, 'rb', false, $context);

            if (!$fp) {
                Log::warning("CCTV Proxy: Failed to open stream at URL: {$streamUrl}");
                return response()->json(['error' => 'CCTV stream offline or unavailable'], 404);
            }

            // Return streamed response block-by-block directly to the browser
            return response()->stream(function () use ($fp) {
                while (!feof($fp)) {
                    echo fread($fp, 8192); // stream in 8KB chunks
                    ob_flush();
                    flush();
                }
                fclose($fp);
            }, 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Access-Control-Allow-Origin' => '*',
            ]);

        } catch (\Exception $e) {
            Log::error("CCTV Proxy exception: " . $e->getMessage());
            return response()->json(['error' => 'Internal stream error'], 500);
        }
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
