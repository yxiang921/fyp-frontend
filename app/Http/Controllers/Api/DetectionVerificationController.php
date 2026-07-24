<?php

namespace App\Http\Controllers\Api;

use App\Enums\DetectionStatus;
use App\Enums\VerificationStatus;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\MissingPerson;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\PythonApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DetectionVerificationController extends Controller
{
    /**
     * POST /api/verify-detection
     *
     * Called by the Python face-recognition backend when a positive match is detected.
     * Creates Detection, VerificationRequest, and Alert records.
     *
     * Expects (JSON or multipart):
     *   - camera_id: string (e.g. "CAM-001")
     *   - case_number: string (e.g. "MP-2026-0001")
     *   - similarity_score: float (0-1 scale, threshold 0.25)
     *   - captured_image_url: string (Flask-generated image URL)
     *
     * Returns JSON.
     */
    public function store(Request $request)
    {
        // --- Validate ---
        $validated = $request->validate([
            'camera_id' => ['required', 'string', 'max:50'],
            'case_number' => ['required', 'string', 'max:50'],
            'similarity_score' => ['required', 'numeric', 'min:0', 'max:1'],
            'captured_image_url' => ['required', 'string', 'max:1000'],
        ]);

        // --- Lookup camera ---
        $camera = Camera::where('camera_id', $validated['camera_id'])->first();
        if (!$camera) {
            return response()->json([
                'success' => false,
                'message' => 'Camera not found: ' . $validated['camera_id'],
            ], 404);
        }

        // --- Lookup missing person ---
        $missingPerson = MissingPerson::where('case_number', $validated['case_number'])->first();
        if (!$missingPerson) {
            return response()->json([
                'success' => false,
                'message' => 'Missing person not found for case number: ' . $validated['case_number'],
            ], 404);
        }

        // --- Convert score: Python 0-1 scale → DB 0-100 percentage ---
        $similarityScore = round($validated['similarity_score'] * 100, 2);

        // --- Generate detection ID ---
        $detectionId = $this->generateId('DET', Detection::class, 'detection_id');

        // --- Captured image: strip Flask base URL prefix, store relative path ---
        $capturedImageUrl = $validated['captured_image_url'];
        $flaskBase = config('services.python_api.base_url', 'http://127.0.0.1:5000');
        $capturedImage = str_replace(rtrim($flaskBase, '/') . '/outputs/', '', $capturedImageUrl);

        // --- Create Detection ---
        $detection = Detection::create([
            'detection_id' => $detectionId,
            'camera_id' => $camera->id,
            'captured_image' => $capturedImage,
            'reference_type' => MissingPerson::class,
            'reference_id' => $missingPerson->id,
            'similarity_score' => $similarityScore,
            'status' => DetectionStatus::New,
            'detected_at' => now(),
        ]);

        Log::info("API: Detection {$detectionId} created. Score: {$similarityScore}%");

        // --- Create Alert + notifications + email patrol (shared service) ---
        $alert = PythonApiService::createAlertAndNotify(
            $detection, $missingPerson, $camera, $similarityScore,
            'api-auto-detection', $missingPerson->created_by
        );

        // --- Generate verification request ID ---
        $requestId = $this->generateId('VRF', VerificationRequest::class, 'request_id');

        // --- Determine requested_by (must be a valid user — use missing person's creator) ---
        $requestedBy = $missingPerson->created_by;
        if (!$requestedBy) {
            // Fallback to first super_admin
            $admin = User::where('role', 'super_admin')->first();
            $requestedBy = $admin ? $admin->id : User::first()->id;
        }

        // --- Create VerificationRequest (confirmed — Python already verified) ---
        $verification = VerificationRequest::create([
            'request_id' => $requestId,
            'detection_id' => $detection->id,
            'alert_id' => $alert->id,
            'requested_by' => $requestedBy,
            'status' => VerificationStatus::Confirmed,
            'verified_at' => now(),
        ]);

        Log::info("API: Verification {$requestId} created (confirmed).");

        return response()->json([
            'success' => true,
            'detection_id' => $detectionId,
            'verification_id' => $requestId,
            'alert_id' => $alert->alert_id,
            'similarity_score' => $similarityScore,
            'priority' => $alert->priority->value,
            'message' => 'Detection verified, alert created, notifications sent.',
        ], 201);
    }

    /**
     * Auto-generate a formatted ID like DET-2026-0001.
     */
    private function generateId(string $prefix, string $modelClass, string $column): string
    {
        $year = date('Y');
        $latest = $modelClass::where($column, 'like', "{$prefix}-{$year}-%")
            ->orderBy($column, 'desc')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->{$column}, -4);
            return "{$prefix}-{$year}-" . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return "{$prefix}-{$year}-0001";
    }

}
