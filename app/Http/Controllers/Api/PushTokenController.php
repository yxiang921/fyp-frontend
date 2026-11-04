<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExpoPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    /**
     * Register an Expo push token for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:20',
        ]);

        $request->user()->pushTokens()->updateOrCreate(
            ['token' => $validated['token']],
            ['device_type' => $validated['device_type'] ?? null],
        );

        return response()->json([
            'success' => true,
            'message' => 'Push token registered.',
        ]);
    }

    /**
     * Remove an Expo push token (e.g. on logout).
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
        ]);

        $request->user()->pushTokens()->where('token', $validated['token'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Push token removed.',
        ]);
    }

    /**
     * Send a test push notification to the authenticated user's devices.
     */
    public function test(Request $request): JsonResponse
    {
        app(ExpoPushService::class)->sendToUser(
            $request->user()->id,
            'FindSight test notification',
            'This is a test push. If you see this, push notifications are working!',
            ['test' => true],
        );

        return response()->json([
            'success' => true,
            'message' => 'Test push sent (if a device token is registered for this account).',
        ]);
    }
}
