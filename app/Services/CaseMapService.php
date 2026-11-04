<?php

namespace App\Services;

use App\Models\MissingPerson;

class CaseMapService
{
    public function points(MissingPerson $person): array
    {
        $points = [];
        foreach ($person->sightings()->whereNotNull('latitude')->whereNotNull('longitude')->get() as $s) {
            $points[] = ['type' => 'sighting', 'latitude' => (float) $s->latitude, 'longitude' => (float) $s->longitude,
                'label' => $s->location, 'status' => $s->status->value, 'at' => $s->sighted_at?->toIso8601String()];
        }
        foreach ($person->detections()->with('camera')->get() as $d) {
            if ($d->camera?->latitude === null || $d->camera?->longitude === null) continue;
            $points[] = ['type' => 'detection', 'latitude' => (float) $d->camera->latitude, 'longitude' => (float) $d->camera->longitude,
                'label' => $d->camera->name, 'status' => $d->status->value, 'at' => $d->detected_at?->toIso8601String()];
        }
        if ($person->last_seen_latitude !== null && $person->last_seen_longitude !== null) {
            $points[] = ['type' => 'last_seen', 'latitude' => (float) $person->last_seen_latitude,
                'longitude' => (float) $person->last_seen_longitude, 'label' => $person->last_seen_location, 'status' => $person->status->value];
        }
        return $points;
    }
}
