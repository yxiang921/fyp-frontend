<?php

namespace App\Services;

use App\Enums\AlertPriority;
use App\Enums\AlertStatus;
use App\Enums\NotificationType;
use App\Mail\NewAlertNotification;
use App\Models\Alert;
use App\Models\AlertTimeline;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\EmailLog;
use App\Models\MissingPerson;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PythonApiService
{
    /**
     * Sync a missing person record to the Python API server.
     *
     * Sends case_number, fullname, and the primary photo (if exists)
     * as multipart/form-data to the configured Python server endpoint.
     *
     * This is fire-and-forget — failures are logged but never thrown.
     */
    public static function syncMissingPerson(MissingPerson $missingPerson): void
    {
        $baseUrl = config('services.python_api.base_url');
        $apiKey = config('services.python_api.api_key');

        if (empty($apiKey)) {
            Log::warning('PythonApiService: PYTHON_API_KEY not set. Skipping sync for case ' . $missingPerson->case_number);
            return;
        }

        try {
            // Build the multipart request
            $httpRequest = Http::withHeaders([
                'X-API-Key' => $apiKey,
            ]);

            // Attach photo if the missing person has a primary photo
            $primaryPhoto = $missingPerson->primaryPhoto;
            if ($primaryPhoto) {
                $photoPath = storage_path('app/public/' . $primaryPhoto->path);
                if (file_exists($photoPath)) {
                    $httpRequest->attach(
                        'photo',
                        file_get_contents($photoPath),
                        basename($primaryPhoto->path)
                    );
                }
            }

            $response = $httpRequest->post($baseUrl . '/api/missing-persons', [
                'case_number' => $missingPerson->case_number,
                'fullname' => $missingPerson->full_name,
            ]);

            if ($response->successful()) {
                Log::info('PythonApiService: Successfully synced case ' . $missingPerson->case_number);
            } else {
                Log::warning('PythonApiService: Failed to sync case ' . $missingPerson->case_number . '. Status: ' . $response->status() . '. Body: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('PythonApiService: Exception syncing case ' . $missingPerson->case_number . ': ' . $e->getMessage());
        }
    }

    /**
     * Create an Alert + timeline + notifications + email patrol units.
     *
     * Shared between the API controller (auto-detection) and the
     * VerificationController (manual confirmation).
     *
     * @return Alert
     */
    public static function createAlertAndNotify(
        Detection $detection,
        MissingPerson $missingPerson,
        Camera $camera,
        float $similarityScore,
        string $triggeredBy = 'system',
        ?int $performedBy = null,
    ): Alert {
        // --- Determine priority from score ---
        $priority = match (true) {
            $similarityScore >= 90 => AlertPriority::Critical,
            $similarityScore >= 70 => AlertPriority::High,
            $similarityScore >= 50 => AlertPriority::Medium,
            default => AlertPriority::Low,
        };

        // --- Generate alert ID ---
        $year = date('Y');
        $latest = Alert::where('alert_id', 'like', "ALT-{$year}-%")
            ->orderBy('alert_id', 'desc')
            ->first();
        if ($latest) {
            $lastNum = (int) substr($latest->alert_id, -4);
            $alertId = "ALT-{$year}-" . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $alertId = "ALT-{$year}-0001";
        }

        // --- Create Alert ---
        $alert = Alert::create([
            'alert_id' => $alertId,
            'detection_id' => $detection->id,
            'alert_type' => 'missing_person',
            'reference_type' => MissingPerson::class,
            'reference_id' => $missingPerson->id,
            'similarity_score' => $similarityScore,
            'camera_id' => $camera->id,
            'status' => AlertStatus::New,
            'priority' => $priority,
            'description' => "Potential match detected for {$missingPerson->full_name} (Case: {$missingPerson->case_number}) at {$camera->name} ({$camera->location}). Similarity score: {$similarityScore}%.",
        ]);

        Log::info("Alert {$alertId} created. Priority: {$priority->value}");

        // --- Alert timeline entry ---
        AlertTimeline::create([
            'alert_id' => $alert->id,
            'event_type' => 'alert_generated',
            'description' => "Alert generated from {$triggeredBy}. Similarity score: {$similarityScore}%. Camera: {$camera->name}.",
            'performed_by' => $performedBy ?? $missingPerson->created_by,
        ]);

        // --- In-app notifications for PDRM officers & super_admins ---
        $officers = User::whereIn('role', ['super_admin', 'pdrm_officer'])->get();
        foreach ($officers as $officer) {
            Notification::create([
                'user_id' => $officer->id,
                'type' => NotificationType::AlertNew,
                'title' => "New Alert: {$alertId}",
                'message' => "Potential match for {$missingPerson->full_name} at {$camera->name}. Score: {$similarityScore}%. Priority: {$priority->label()}.",
                'data' => [
                    'alert_id' => $alert->id,
                    'detection_id' => $detection->id,
                ],
            ]);
        }

        // --- Email patrol unit users ---
        $patrolUsers = User::where('role', 'patrol_unit')->where('is_active', true)->get();
        $mailableClass = NewAlertNotification::class;
        $subject = "FindSight Alert: Potential Match — {$missingPerson->case_number}";

        foreach ($patrolUsers as $patrolUser) {
            try {
                Mail::to($patrolUser->email)->send(
                    new NewAlertNotification($alert, $detection, $missingPerson, $camera)
                );

                EmailLog::log([
                    'recipient_email' => $patrolUser->email,
                    'recipient_name' => $patrolUser->name,
                    'subject' => $subject,
                    'mailable_class' => $mailableClass,
                    'alert_id' => $alert->id,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'trigger_type' => 'auto',
                    'triggered_by' => $performedBy ?? $missingPerson->created_by,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to queue email to {$patrolUser->email}: " . $e->getMessage());

                EmailLog::log([
                    'recipient_email' => $patrolUser->email,
                    'recipient_name' => $patrolUser->name,
                    'subject' => $subject,
                    'mailable_class' => $mailableClass,
                    'alert_id' => $alert->id,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'trigger_type' => 'auto',
                    'triggered_by' => $performedBy ?? $missingPerson->created_by,
                ]);
            }
        }

        return $alert;
    }
}
