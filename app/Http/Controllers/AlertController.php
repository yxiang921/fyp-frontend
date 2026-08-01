<?php

namespace App\Http\Controllers;

use App\Enums\AlertPriority;
use App\Enums\AlertStatus;
use App\Mail\PatrolAlertNotification;
use App\Models\Alert;
use App\Models\AlertTimeline;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::with(['detection', 'reference', 'camera', 'assignedTo']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $status = AlertStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('priority')) {
            $priority = AlertPriority::tryFrom($request->input('priority'));
            if ($priority) {
                $query->where('priority', $priority);
            }
        }

        if ($request->filled('type')) {
            $query->byType($request->input('type'));
        }

        $alerts = $query->latest()->paginate(15)->withQueryString();
        $statuses = AlertStatus::cases();
        $priorities = AlertPriority::cases();

        return view('alerts.index', compact('alerts', 'statuses', 'priorities'));
    }

    public function show(Alert $alert)
    {
        $alert->load(['detection.camera', 'reference', 'camera', 'assignedTo', 'timeline', 'verificationRequests']);

        return view('alerts.show', compact('alert'));
    }

    public function review(Alert $alert)
    {
        $alert->load(['detection.camera', 'reference', 'camera', 'assignedTo']);
        $officers = User::active()->whereIn('role', ['pdrm_officer', 'super_admin'])->orderBy('name')->get();

        return view('alerts.review', compact('alert', 'officers'));
    }

    public function sendAlert(Alert $alert)
    {
        $alert->load(['detection.camera', 'reference', 'camera', 'timeline']);

        $patrolUsers = User::where('role', 'patrol_unit')->where('is_active', true)->get();

        if ($patrolUsers->isEmpty()) {
            return redirect()->back()->with('warning', 'No active patrol units found to send the alert to.');
        }

        $sentCount = 0;
        $mailableClass = PatrolAlertNotification::class;
        $subject = "FindSight Alert Dispatch — {$alert->reference->case_number} — {$alert->alert_id}";
        
        foreach ($patrolUsers as $patrolUser) {
            try {
                Mail::to($patrolUser->email)->send(
                    new PatrolAlertNotification($alert, $alert->detection, $alert->reference, $alert->camera, $alert->timeline)
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

                $sentCount++;
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
            'description' => "Alert notification manually sent to {$sentCount} patrol unit(s).",
            'performed_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', "Alert notification sent to {$sentCount} patrol unit(s).");
    }
}
