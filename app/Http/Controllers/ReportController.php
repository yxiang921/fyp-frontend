<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\MissingPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function analysis()
    {
        $analytics = app(\App\Services\ReportingAnalytics::class)->summary();
        // --- Summary Stats ---
        $stats = [
            'totalDetections' => Detection::count(),
            'totalAlerts' => Alert::count(),
            'totalCameras' => Camera::count(),
            'activeCases' => MissingPerson::active()->count(),
            'highConfidenceDetections' => Detection::where('similarity_score', '>=', 80)->count(),
            'newAlerts' => Alert::where('status', 'new')->count(),
            'escalatedAlerts' => Alert::where('status', 'escalated')->count(),
            'closedAlerts' => Alert::where('status', 'closed')->count(),
        ];

        // --- Camera Locations (all cameras with coordinates) ---
        $cameraLocations = Camera::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'name', 'location', 'latitude', 'longitude', 'status')
            ->get()
            ->map(fn($c) => [
                'type' => 'camera',
                'id' => $c->id,
                'name' => $c->name,
                'location' => $c->location,
                'latitude' => (float) $c->latitude,
                'longitude' => (float) $c->longitude,
                'status' => $c->status->value,
            ]);

        // --- Detection Locations (distinct camera locations with detections) ---
        $detectionLocations = Detection::join('cameras', 'detections.camera_id', '=', 'cameras.id')
            ->select(
                'cameras.id',
                'cameras.name',
                'cameras.location',
                'cameras.latitude',
                'cameras.longitude',
                DB::raw('COUNT(detections.id) as detection_count'),
                DB::raw('AVG(detections.similarity_score) as avg_score'),
                DB::raw('MAX(detections.detected_at) as last_detected')
            )
            ->whereNotNull('cameras.latitude')
            ->whereNotNull('cameras.longitude')
            ->groupBy('cameras.id', 'cameras.name', 'cameras.location', 'cameras.latitude', 'cameras.longitude')
            ->orderByDesc('detection_count')
            ->get()
            ->map(fn($d) => [
                'type' => 'detection',
                'id' => $d->id,
                'name' => $d->name,
                'location' => $d->location,
                'latitude' => (float) $d->latitude,
                'longitude' => (float) $d->longitude,
                'detection_count' => (int) $d->detection_count,
                'avg_score' => round((float) $d->avg_score, 1),
                'last_detected' => $d->last_detected,
            ]);

        // --- Last Seen Locations (missing persons with coordinates) ---
        $lastSeenLocations = MissingPerson::whereNotNull('last_seen_latitude')
            ->whereNotNull('last_seen_longitude')
            ->select('id', 'full_name', 'case_number', 'last_seen_location', 'last_seen_latitude', 'last_seen_longitude', 'status')
            ->get()
            ->map(fn($mp) => [
                'type' => 'last_seen',
                'id' => $mp->id,
                'name' => $mp->full_name,
                'case_number' => $mp->case_number,
                'location' => $mp->last_seen_location,
                'latitude' => (float) $mp->last_seen_latitude,
                'longitude' => (float) $mp->last_seen_longitude,
                'status' => $mp->status->value,
            ]);

        // --- Detections by Date (for trend chart) ---
        $detectionTrend = Detection::select(
                DB::raw('DATE(detected_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('detected_at')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->reverse();

        // --- Alerts by Status (for pie/doughnut) ---
        $alertsByStatus = Alert::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status->value => $item->count]);

        // --- Top Cameras by Detections ---
        $topCameras = Camera::withCount('detections')
            ->orderByDesc('detections_count')
            ->take(10)
            ->get();

        // --- Recent High-Confidence Detections ---
        $recentHighMatches = Detection::with(['camera', 'missingPerson'])
            ->where('similarity_score', '>=', 80)
            ->latest()
            ->take(10)
            ->get();

        return view('reports.analysis', compact(
            'analytics',
            'stats',
            'cameraLocations',
            'detectionLocations',
            'lastSeenLocations',
            'detectionTrend',
            'alertsByStatus',
            'topCameras',
            'recentHighMatches'
        ));
    }
}
