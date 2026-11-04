<?php

namespace App\Services;

use App\Models\PushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    protected string $endpoint = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send a push notification to every device registered for a user.
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $tokens = PushToken::where('user_id', $userId)->pluck('token')->all();

        if (empty($tokens)) {
            return;
        }

        $this->send($tokens, $title, $body, $data);
    }

    /**
     * Send a push notification to a list of Expo push tokens.
     *
     * @param  array<int, string>  $tokens
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));

        if (empty($tokens)) {
            return;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->post($this->endpoint, [
                    'to' => $tokens,
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'data' => $data,
                ]);

            if ($response->failed()) {
                Log::warning('ExpoPushService: push request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return;
            }

            $this->handleReceipts($response->json('data') ?? [], $tokens);
        } catch (\Throwable $e) {
            Log::warning('ExpoPushService: exception — ' . $e->getMessage());
        }
    }

    /**
     * Remove tokens that Expo reports as invalid so they are not retried.
     */
    protected function handleReceipts(array $receipts, array $tokens): void
    {
        foreach ($receipts as $index => $receipt) {
            if (($receipt['status'] ?? 'ok') !== 'error') {
                continue;
            }

            $token = $tokens[$index] ?? null;

            if ($token) {
                PushToken::where('token', $token)->delete();
            }
        }
    }
}
