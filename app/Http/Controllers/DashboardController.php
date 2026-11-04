<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Alert;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\MissingPerson;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect()->route(auth()->user()->role->dashboard());
    }

    public function admin()
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalAlerts' => Alert::count(),
            'totalCameras' => Camera::count(),
            'activeCases' => MissingPerson::active()->count(),
            'pendingVerifications' => MissingPerson::where('status', 'pending')->count(),
            'newAlerts' => Alert::new()->count(),
            'onlineCameras' => Camera::online()->count(),
        ];

        $recentDetections = Detection::with('camera')->latest()->take(8)->get();
        $recentAlerts = Alert::with(['camera', 'assignedTo'])->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();

        return view('dashboard.admin', compact('stats', 'recentDetections', 'recentAlerts', 'recentUsers'));
    }

    public function pdrm()
    {
        $stats = [
            'activeMissingPersons' => MissingPerson::active()->count(),
            'pendingVerifications' => MissingPerson::where('status', 'pending')->count(),
            'todayAlerts' => Alert::whereDate('created_at', today())->count(),
            'newDetections' => Detection::new()->count(),
        ];

        $recentDetections = Detection::with('camera')->latest()->take(8)->get();
        $recentAlerts = Alert::with(['camera'])->latest()->take(5)->get();
        $pendingVerifications = MissingPerson::with('creator')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.pdrm', compact('stats', 'recentDetections', 'recentAlerts', 'pendingVerifications'));
    }

    public function patrol()
    {
        $areaAlerts = app(\App\Services\PatrolRoutingService::class)
            ->restrictAlertsToPatrolAreas(Alert::query(), auth()->user());
        $stats = [
            'assignedAlerts' => (clone $areaAlerts)->count(),
            'newAlerts' => (clone $areaAlerts)->new()->count(),
            'resolvedToday' => (clone $areaAlerts)
                ->whereDate('resolved_at', today())->count(),
        ];

        $assignedAlerts = app(\App\Services\PatrolRoutingService::class)
            ->restrictAlertsToPatrolAreas(Alert::with(['camera.patrolArea', 'detection']), auth()->user())
            ->latest()
            ->take(10)
            ->get();

        $patrolAreas = auth()->user()->patrolAreas()->orderBy('name')->get();
        return view('dashboard.patrol', compact('stats', 'assignedAlerts', 'patrolAreas'));
    }

    public function public()
    {
        $userId = auth()->id();

        $stats = [
            'totalCases' => MissingPerson::where('created_by', $userId)->count(),
            'pendingCases' => MissingPerson::where('created_by', $userId)->where('status', 'pending')->count(),
            'activeCases' => MissingPerson::where('created_by', $userId)->where('status', 'active')->count(),
            'resolvedCases' => MissingPerson::where('created_by', $userId)
                ->whereIn('status', ['found', 'closed'])->count(),
        ];

        $myCases = MissingPerson::where('created_by', $userId)
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.public', compact('stats', 'myCases'));
    }
}
