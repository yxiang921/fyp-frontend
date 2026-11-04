<?php

namespace App\Http\Controllers;

use App\Enums\AlertStatus;
use App\Enums\NotificationType;
use App\Mail\PatrolAlertNotification;
use App\Models\Alert;
use App\Models\AlertTimeline;
use App\Models\EmailLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\PatrolRoutingService;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::with(['detection', 'sighting', 'missingPerson', 'camera', 'assignedTo']);
        if ($request->user()->isPatrolUnit()) {
            app(PatrolRoutingService::class)->restrictAlertsToPatrolAreas($query, $request->user());
        }

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $status = AlertStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        $alerts = $query->latest()->paginate(15)->withQueryString();
        $statuses = AlertStatus::cases();

        return view('alerts.index', compact('alerts', 'statuses'));
    }

    public function show(Alert $alert)
    {
        $this->ensurePatrolCanAccess($alert);
        $alert->load(['detection.camera', 'sighting', 'missingPerson', 'camera', 'patrolArea', 'assignedTo', 'timeline']);

        return view('alerts.show', compact('alert'));
    }

    public function review(Alert $alert)
    {
        $this->ensurePatrolCanAccess($alert);
        $alert->load(['detection.camera', 'sighting', 'missingPerson', 'camera', 'assignedTo']);
        if (!$alert->detection) {
            return redirect()->route('alerts.show', $alert);
        }
        $officers = User::active()->whereIn('role', ['pdrm_officer', 'super_admin'])->orderBy('name')->get();

        return view('alerts.review', compact('alert', 'officers'));
    }

    public function updateStatus(Request $request, Alert $alert)
    {
        $this->ensurePatrolCanAccess($alert);
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,under_review,escalated,closed,false_positive'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $alert->status->label();
        $alert->update([
            'status' => $validated['status'],
        ]);

        // Record timeline entry
        AlertTimeline::create([
            'alert_id' => $alert->id,
            'event_type' => 'status_changed',
            'description' => $validated['notes']
                ? "Status changed from {$oldStatus} to " . AlertStatus::from($validated['status'])->label() . ". {$validated['notes']}"
                : "Status changed from {$oldStatus} to " . AlertStatus::from($validated['status'])->label() . ".",
            'performed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Alert status updated successfully.');
    }

    public function sendAlert(Alert $alert)
    {
        $this->ensurePatrolCanAccess($alert);
        $alert->load(['detection.camera', 'sighting', 'missingPerson', 'camera', 'patrolArea', 'timeline']);

        $routing = app(PatrolRoutingService::class);
        $patrolUsers = $routing->recipientsForAlert($alert);
        $areaName = $alert->patrolArea?->name
            ?? ($alert->camera ? $routing->areaForCamera($alert->camera)?->name : null)
            ?? ($alert->sighting ? $routing->areaForSighting($alert->sighting)?->name : null)
            ?? 'unassigned area';
        $location = $alert->camera?->name ?? $alert->sighting?->location ?? 'reported location';

        if ($patrolUsers->isEmpty()) {
            return redirect()->back()->with('warning', "No active patrol unit is assigned to {$areaName}.");
        }

        $sentCount = 0;
        $mailableClass = PatrolAlertNotification::class;
        $subject = "FindSight Alert Dispatch — {$alert->missingPerson->case_number} — {$alert->alert_id}";
        
        foreach ($patrolUsers as $patrolUser) {
            // In-app + mobile push notification for the patrol unit.
            // Notification::created() automatically fires the Expo push to the user.
            try {
                Notification::create([
                    'user_id' => $patrolUser->id,
                    'type' => NotificationType::AlertNew,
                    'title' => "Alert Dispatch: {$alert->alert_id}",
                    'message' => "Alert for {$alert->missingPerson->full_name} (Case: {$alert->missingPerson->case_number}) at {$location}.",
                    'data' => [
                        'alert_id' => $alert->id,
                        'detection_id' => $alert->detection_id,
                        'sighting_id' => $alert->sighting_id,
                        'case_number' => $alert->missingPerson->case_number,
                    ],
                ]);
                $sentCount++;
            } catch (\Exception $e) {
                Log::error("Failed to create patrol notification for {$patrolUser->name}: " . $e->getMessage());
            }

            if ($alert->detection && $alert->camera) try {
                Mail::to($patrolUser->email)->send(
                    new PatrolAlertNotification($alert, $alert->detection, $alert->missingPerson, $alert->camera, $alert->timeline)
                );

                EmailLog::log([
                    'recipient_email' => $patrolUser->email,
                    'recipient_name' => $patrolUser->name,
                    'subject' => $subject,
                    'mailable_class' => $mailableClass,
                    'alert_id' => $alert->id,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'trigger_type' => 'manual',
                    'triggered_by' => auth()->id(),
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to queue patrol alert email to {$patrolUser->email}: " . $e->getMessage());

                EmailLog::log([
                    'recipient_email' => $patrolUser->email,
                    'recipient_name' => $patrolUser->name,
                    'subject' => $subject,
                    'mailable_class' => $mailableClass,
                    'alert_id' => $alert->id,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'trigger_type' => 'manual',
                    'triggered_by' => auth()->id(),
                ]);
            }
        }

        // Record timeline entry for the manual dispatch
        AlertTimeline::create([
            'alert_id' => $alert->id,
            'event_type' => 'alert_dispatched',
            'description' => "Alert email and mobile push sent to {$sentCount} patrol unit(s) assigned to {$areaName}.",
            'performed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', "Alert sent to {$sentCount} patrol unit(s) assigned to {$areaName}.");
    }

    private function ensurePatrolCanAccess(Alert $alert): void
    {
        $user = request()->user();
        if (!$user?->isPatrolUnit()) return;
        $allowed = app(PatrolRoutingService::class)
            ->restrictAlertsToPatrolAreas(Alert::query()->whereKey($alert->id), $user)->exists();
        abort_unless($allowed, 403, 'This alert is outside your patrol area.');
    }
}
