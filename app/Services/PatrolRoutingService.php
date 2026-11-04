<?php

namespace App\Services;

use App\Models\Camera;
use App\Models\Alert;
use App\Models\PatrolArea;
use App\Models\Sighting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PatrolRoutingService
{
    public function areaForCamera(Camera $camera): ?PatrolArea
    {
        if ($camera->patrolArea?->is_active) {
            return $camera->patrolArea;
        }

        $location = mb_strtolower($camera->location.' '.$camera->name);
        return PatrolArea::where('is_active', true)->get()->first(
            fn (PatrolArea $area) => str_contains($location, mb_strtolower($area->name))
                || str_contains($location, mb_strtolower($area->code))
        );
    }

    public function recipientsForCamera(Camera $camera): Collection
    {
        $area = $this->areaForCamera($camera);
        if (!$area) {
            return collect();
        }

        return $area->users()->where('role', 'patrol_unit')->where('is_active', true)->get();
    }

    public function areaForSighting(Sighting $sighting): ?PatrolArea
    {
        $areas = PatrolArea::where('is_active', true)->get();
        $location = mb_strtolower($sighting->location ?? '');
        $named = $areas->first(fn (PatrolArea $area) =>
            str_contains($location, mb_strtolower($area->name))
            || str_contains($location, mb_strtolower($area->code))
        );
        if ($named || $sighting->latitude === null || $sighting->longitude === null) {
            return $named;
        }

        return $areas->filter(fn (PatrolArea $area) =>
            $area->center_latitude !== null && $area->center_longitude !== null
        )->sortBy(fn (PatrolArea $area) => $this->distanceKm(
            (float) $sighting->latitude,
            (float) $sighting->longitude,
            (float) $area->center_latitude,
            (float) $area->center_longitude,
        ))->first();
    }

    public function recipientsForSighting(Sighting $sighting): Collection
    {
        return $this->recipientsForArea($this->areaForSighting($sighting));
    }

    public function recipientsForAlert(Alert $alert): Collection
    {
        $area = $alert->patrolArea
            ?? ($alert->camera ? $this->areaForCamera($alert->camera) : null)
            ?? ($alert->sighting ? $this->areaForSighting($alert->sighting) : null);

        return $this->recipientsForArea($area);
    }

    public function restrictAlertsToPatrolAreas(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $alerts) use ($user) {
            $alerts->whereHas('patrolArea.users', fn (Builder $users) => $users->whereKey($user->id))
                ->orWhere(function (Builder $legacy) use ($user) {
                    $legacy->whereNull('patrol_area_id')
                        ->whereHas('camera.patrolArea.users', fn (Builder $users) => $users->whereKey($user->id));
                });
        });
    }

    private function recipientsForArea(?PatrolArea $area): Collection
    {
        return $area
            ? $area->users()->where('role', 'patrol_unit')->where('is_active', true)->get()
            : collect();
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;
        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
