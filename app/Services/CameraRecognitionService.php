<?php

namespace App\Services;

use App\Models\Camera;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CameraRecognitionService
{
    public function status(Camera $camera): array
    {
        return $this->request('GET', $camera);
    }

    public function control(Camera $camera, string $action): array
    {
        return $this->request('POST', $camera, $action);
    }

    private function request(string $method, Camera $camera, ?string $action = null): array
    {
        $url = rtrim(config('services.python_api.base_url'), '/')
            .'/api/cameras/'.rawurlencode($camera->camera_id).'/recognition';
        try {
            $client = Http::acceptJson()->connectTimeout(2)->timeout(5);
            if ($key = config('services.python_api.api_key')) {
                $client = $client->withHeader('X-API-Key', $key);
            }
            $response = $method === 'POST'
                ? $client->post($url, ['action' => $action])
                : $client->get($url);
            if (!$response->successful()) {
                throw new \RuntimeException($response->json('message') ?: "Python service returned HTTP {$response->status()}.");
            }
            return $response->json();
        } catch (ConnectionException $error) {
            throw new \RuntimeException('Cannot reach the Python recognition service at '.config('services.python_api.base_url').'.', 0, $error);
        }
    }
}
