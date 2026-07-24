<?php

namespace App\Http\Controllers;

use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\PythonApiService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = VerificationRequest::with(['detection', 'alert', 'requester', 'reviewer']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('status')) {
            $status = VerificationStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        $verifications = $query->latest()->paginate(15)->withQueryString();
        $statuses = VerificationStatus::cases();

        return view('verifications.index', compact('verifications', 'statuses'));
    }

    public function show(VerificationRequest $verification)
    {
        $verification->load(['detection.camera', 'alert', 'requester', 'reviewer', 'documents']);

        return view('verifications.show', compact('verification'));
    }

    public function review(VerificationRequest $verification)
    {
        $verification->load(['detection.camera', 'alert', 'requester']);
        $officers = User::active()->whereIn('role', ['pdrm_officer', 'super_admin'])->orderBy('name')->get();

        return view('verifications.review', compact('verification', 'officers'));
    }

    /**
     * POST /verifications/{verification}/review
     *
     * Submit a manual verification review decision.
     * Super admin / PDRM officer only (enforced by route middleware).
     */
    public function submitReview(Request $request, VerificationRequest $verification)
    {
        $validated = $request->validate([
            'decision' => ['required', 'string', 'in:confirmed,rejected,insufficient_data'],
            'jpn_notes' => ['required', 'string', 'max:5000'],
        ]);

        // --- Update verification ---
        $verification->update([
            'status' => $validated['decision'],
            'jpn_notes' => $validated['jpn_notes'],
            'reviewed_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // --- Audit log ---
        $decisionLabel = match ($validated['decision']) {
            'confirmed' => 'Confirmed',
            'rejected' => 'Rejected',
            default => 'Insufficient Data',
        };

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $decisionLabel,
            'module' => 'Verifications',
            'description' => "Verification {$verification->request_id} {$validated['decision']} by " . auth()->user()->name . '.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // --- If confirmed: create alert + notify ---
        if ($validated['decision'] === 'confirmed') {
            $detection = $verification->detection;
            $missingPerson = $detection->reference;
            $camera = $detection->camera;

            if ($missingPerson && $camera) {
                $alert = PythonApiService::createAlertAndNotify(
                    $detection,
                    $missingPerson,
                    $camera,
                    $detection->similarity_score,
                    'verification-confirmation',
                    auth()->id()
                );

                // Link alert to verification
                $verification->update(['alert_id' => $alert->id]);

                // Additional audit log for alert creation
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Create',
                    'module' => 'Alerts',
                    'description' => "Alert {$alert->alert_id} created from verification {$verification->request_id} confirmation by " . auth()->user()->name . '.',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()
                    ->route('verifications.show', $verification)
                    ->with('success', "Verification confirmed. Alert {$alert->alert_id} created and patrol units notified.");
            }
        }

        return redirect()
            ->route('verifications.show', $verification)
            ->with('success', "Verification {$validated['decision']} successfully.");
    }
}
