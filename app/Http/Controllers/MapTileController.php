<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MapTileController extends Controller
{
    public function __invoke(int $z, int $x, int $y)
    {
        abort_unless($z >= 0 && $z <= 19, 404);
        $limit = (2 ** $z) - 1;
        abort_unless($x >= 0 && $x <= $limit && $y >= 0 && $y <= $limit, 404);

        $path = "map-tiles/{$z}/{$x}/{$y}.png";
        $disk = Storage::disk('local');
        if (!$disk->exists($path)) {
            $url = str_replace(['{z}', '{x}', '{y}'], [$z, $x, $y], config('maps.upstream_url'));
            $response = Http::withHeaders([
                'User-Agent' => config('maps.user_agent'),
                'Referer' => config('app.url'),
                'Accept' => 'image/png,image/*;q=0.8',
            ])->connectTimeout(3)->timeout(10)->get($url);
            abort_unless($response->successful() && str_starts_with($response->header('Content-Type', ''), 'image/'), 502, 'Map tile provider is unavailable.');
            $disk->put($path, $response->body());
        }

        return response($disk->get($path), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
