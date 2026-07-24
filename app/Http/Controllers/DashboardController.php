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
            'highPriorityAlerts' => Alert::new()->whereIn('priority', ['high', 'critical'])->count(),
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
        $stats = [
            'assignedAlerts' => Alert::where('assigned_to', auth()->id())->count(),
            'newAlerts' => Alert::where('assigned_to', auth()->id())->new()->count(),
            'resolvedToday' => Alert::where('assigned_to', auth()->id())
                ->whereDate('resolved_at', today())->count(),
        ];

        $assignedAlerts = Alert::with(['camera', 'detection'])
            ->where('assigned_to', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.patrol', compact('stats', 'assignedAlerts'));
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
