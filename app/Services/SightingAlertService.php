<?php

namespace App\Services;

use App\Enums\AlertStatus;
use App\Enums\NotificationType;
use App\Models\Alert;
use App\Models\AlertTimeline;
use App\Models\Notification;
use App\Models\Sighting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SightingAlertService
{
    public function __construct(private readonly PatrolRoutingService $routing) {}

    public function createForConfirmedSighting(Sighting $sighting, int $performedBy): Alert
    {
        return DB::transaction(function () use ($sighting, $performedBy) {
            $locked = Sighting::with('missingPerson')->lockForUpdate()->findOrFail($sighting->id);
            $existing = Alert::where('sighting_id', $locked->id)->first();
            if ($existing) return $existing;

            $area = $this->routing->areaForSighting($locked);
            $alert = Alert::create([
                'alert_id' => $this->nextAlertId(),
                'sighting_id' => $locked->id,
                'missing_person_id' => $locked->missing_person_id,
                'patrol_area_id' => $area?->id,
                'similarity_score' => 0,
                'status' => AlertStatus::New,
                'description' => "Confirmed public sighting of {$locked->missingPerson->full_name} at {$locked->location}.",
            ]);

            AlertTimeline::create([
                'alert_id' => $alert->id,
                'event_type' => 'confirmed_sighting',
                'description' => 'Alert generated after an officer confirmed public sighting report #'.$locked->id.'.',
                'performed_by' => $performedBy,
            ]);

            $officers = User::active()->whereIn('role', ['super_admin', 'pdrm_officer'])->get();
            $patrol = $this->routing->recipientsForSighting($locked);
            foreach ($officers->concat($patrol)->unique('id') as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => NotificationType::AlertNew,
                    'title' => 'Confirmed public sighting',
                    'message' => "{$locked->missingPerson->full_name} was reported at {$locked->location}.",
                    'data' => [
                        'alert_id' => $alert->id,
                        'sighting_id' => $locked->id,
                        'missing_person_id' => $locked->missing_person_id,
                        'patrol_area_id' => $area?->id,
                    ],
                ]);
            }

            return $alert;
        });
    }

    private function nextAlertId(): string
    {
        $year = now()->format('Y');
        $latest = Alert::where('alert_id', 'like', "ALT-{$year}-%")
            ->lockForUpdate()->orderByDesc('alert_id')->value('alert_id');
        $number = $latest ? ((int) substr(strrchr($latest, '-'), 1)) + 1 : 1;
        return sprintf('ALT-%s-%04d', $year, $number);
    }
}
