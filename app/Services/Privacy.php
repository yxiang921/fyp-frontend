<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class Privacy
{
    public static function log(string $action, string $type, int|string|null $id = null): void
    {
        DB::table('privacy_access_logs')->insert([
            'user_id' => auth()->id() ?? auth('sanctum')->id(),
            'action' => $action, 'resource_type' => $type, 'resource_id' => $id,
            'ip_hash' => hash_hmac('sha256', request()->ip() ?? '', config('app.key')),
            'created_at' => now(),
        ]);
    }

    public static function redact(?string $text): ?string
    {
        if ($text === null) return null;
        return preg_replace([
            '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu',
            '/(?<!\d)\d{6}[- ]?\d{2}[- ]?\d{4}(?!\d)/u',
            '/(?<!\w)\+?\d[\d\s().-]{7,}\d(?!\w)/u',
        ], '[redacted]', $text);
    }
}
