<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubmissionReceipt
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->input('submission_id');
        if (!$id) return $next($request);
        $request->validate(['submission_id' => 'uuid']);
        $fields = $request->except(['submission_id']);
        foreach ($request->allFiles() as $key => $files) {
            $fields[$key] = array_map(fn ($file) => hash_file('sha256', $file->getRealPath()), is_array($files) ? $files : [$files]);
        }
        ksort($fields);
        $fingerprint = hash('sha256', $request->path().json_encode($fields));
        return Cache::lock('submission:'.$id, 120)->block(5, function () use ($id, $fingerprint, $request, $next) {
            $receipt = DB::table('submission_receipts')->find($id);
            if ($receipt) {
                abort_unless(hash_equals($receipt->fingerprint, $fingerprint), 409, 'Submission identifier already used with different content.');
                return response()->json(json_decode($receipt->response, true), $receipt->status);
            }
            return DB::transaction(function () use ($id, $fingerprint, $request, $next) {
                $response = $next($request);
                if ($response->isSuccessful()) DB::table('submission_receipts')->insert([
                    'id' => $id, 'fingerprint' => $fingerprint, 'response' => $response->getContent(),
                    'status' => $response->getStatusCode(), 'created_at' => now(),
                ]);
                return $response;
            });
        });
    }
}
