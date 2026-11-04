<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\PatrolArea;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatrolAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = collect([
            ['name' => 'Skudai', 'code' => 'SKUDAI', 'center_latitude' => 1.5374, 'center_longitude' => 103.6578, 'description' => 'Skudai and nearby northern Johor Bahru patrol sector.'],
            ['name' => 'Johor Bahru', 'code' => 'JB', 'center_latitude' => 1.4927, 'center_longitude' => 103.7414, 'description' => 'Johor Bahru city patrol sector.'],
            ['name' => 'Kuala Lumpur Central', 'code' => 'KL-CENTRAL', 'center_latitude' => 3.1390, 'center_longitude' => 101.6869, 'description' => 'Central Kuala Lumpur patrol sector.'],
            ['name' => 'Petaling Jaya', 'code' => 'PJ', 'center_latitude' => 3.1073, 'center_longitude' => 101.6067, 'description' => 'Petaling Jaya patrol sector.'],
        ])->mapWithKeys(function (array $data) {
            $area = PatrolArea::updateOrCreate(['code' => $data['code']], $data + ['is_active' => true]);
            return [$data['code'] => $area];
        });

        // Keep existing demo/legacy records operational after introducing areas.
        Camera::whereNull('patrol_area_id')->where(function ($query) {
            $query->where('location', 'ilike', '%Kuala Lumpur%')->orWhere('location', 'ilike', '%KLCC%')
                ->orWhere('location', 'ilike', '%Pudu%')->orWhere('location', 'ilike', '%Sentral%')
                ->orWhere('location', 'ilike', '%Chow Kit%')->orWhere('location', 'ilike', '%Ampang%');
        })->update(['patrol_area_id' => $areas['KL-CENTRAL']->id]);
        Camera::whereNull('patrol_area_id')->where('camera_id', '0')
            ->update(['patrol_area_id' => $areas['KL-CENTRAL']->id]);

        foreach (User::where('role', 'patrol_unit')->get() as $user) {
            if ($user->patrolAreas()->exists()) continue;
            $agency = mb_strtolower((string) $user->agency);
            $area = str_contains($agency, 'skudai') ? $areas['SKUDAI']
                : (str_contains($agency, 'johor') ? $areas['JB']
                : (str_contains($agency, 'petaling') ? $areas['PJ'] : $areas['KL-CENTRAL']));
            $user->patrolAreas()->sync([$area->id]);
        }
    }
}
