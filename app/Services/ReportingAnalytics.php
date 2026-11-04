<?php

namespace App\Services;

use App\Models\Camera;
use App\Models\Detection;
use App\Models\Sighting;

class ReportingAnalytics
{
    public function summary(int $days = 30): array
    {
        $since = now()->subDays($days);
        $detections = Detection::with('camera')->where('detected_at', '>=', $since)->get();
        $confirmed = $detections->where('status', \App\Enums\DetectionStatus::Confirmed)->count();
        $false = $detections->whereIn('status', [\App\Enums\DetectionStatus::FalsePositive, \App\Enums\DetectionStatus::Rejected])->count();
        $responseTimes = $detections->filter(fn ($d) => $d->reviewed_at && $d->detected_at && $d->reviewed_at >= $d->detected_at)
            ->map(fn ($d) => $d->detected_at->diffInMinutes($d->reviewed_at));
        $sightingTimes = Sighting::where('created_at', '>=', $since)->whereNotNull('reviewed_at')->get()
            ->map(fn ($s) => $s->created_at->diffInMinutes($s->reviewed_at));
        $cameraRows = Camera::get()->map(function ($c) use ($detections) {
            $rows = $detections->where('camera_id', $c->id);
            $confirmed = $rows->where('status', \App\Enums\DetectionStatus::Confirmed)->count();
            $false = $rows->whereIn('status', [\App\Enums\DetectionStatus::FalsePositive, \App\Enums\DetectionStatus::Rejected])->count();
            return ['name' => $c->name, 'total' => $rows->count(), 'confirmed' => $confirmed, 'false' => $false,
                'rate' => $rows->count() ? round(100 * $confirmed / $rows->count(), 1) : null];
        });
        $trend = $detections->groupBy(fn ($d) => $d->detected_at->toDateString())->map(fn ($rows, $date) => [
            'date' => $date, 'total' => $rows->count(),
            'false' => $rows->whereIn('status', [\App\Enums\DetectionStatus::FalsePositive, \App\Enums\DetectionStatus::Rejected])->count(),
        ])->sortBy('date')->values();
        $points = [];
        foreach ($detections as $d) {
            if ($d->camera?->latitude !== null && $d->camera?->longitude !== null) $points[] = [
                'latitude' => (float)$d->camera->latitude, 'longitude' => (float)$d->camera->longitude, 'type' => 'detection',
            ];
        }
        foreach (Sighting::where('created_at', '>=', $since)->whereNotNull('latitude')->whereNotNull('longitude')->get() as $s) {
            $points[] = ['latitude' => (float)$s->latitude, 'longitude' => (float)$s->longitude, 'type' => 'sighting'];
        }
        $hotspots = collect($points)->groupBy(fn ($p) => round($p['latitude'], 2).','.round($p['longitude'], 2))
            ->map(fn ($rows, $key) => ['latitude' => (float)explode(',', $key)[0], 'longitude' => (float)explode(',', $key)[1],
                'count' => $rows->count(), 'sightings' => $rows->where('type', 'sighting')->count(), 'detections' => $rows->where('type', 'detection')->count()])->values();
        return ['days' => $days, 'total' => $detections->count(), 'confirmed' => $confirmed, 'false' => $false,
            'confirmationRate' => $detections->count() ? round(100*$confirmed/$detections->count(),1) : null,
            'falseRate' => ($confirmed+$false) ? round(100*$false/($confirmed+$false),1) : null,
            'responseMinutes' => $responseTimes->isEmpty() ? null : round($responseTimes->avg(),1), 'responseSamples' => $responseTimes->count(),
            'sightingMinutes' => $sightingTimes->isEmpty() ? null : round($sightingTimes->avg(),1),
            'cameras' => $cameraRows, 'trend' => $trend, 'hotspots' => $hotspots];
    }
}
