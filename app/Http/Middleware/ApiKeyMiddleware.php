<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    /**
     * Validate the X-API-Key header for incoming API requests.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = config('services.python_api.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key not configured on server.',
            ], 500);
        }

        $providedKey = $request->header('X-API-Key');

        if (empty($providedKey) || $providedKey !== $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
