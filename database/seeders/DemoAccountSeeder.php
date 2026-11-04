<?php

namespace Database\Seeders;

use App\Models\PatrolArea;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PatrolAreaSeeder::class);

        $accounts = [
            ['name' => 'Demo System Administrator', 'email' => 'demo.admin@findsight.test', 'role' => 'super_admin', 'agency' => 'FindSight Demo Administration', 'phone' => '+60 11-9000 0001'],
            ['name' => 'Demo PDRM Officer', 'email' => 'demo.officer@findsight.test', 'role' => 'pdrm_officer', 'agency' => 'IPD Johor Bahru', 'phone' => '+60 11-9000 0002'],
            ['name' => 'Demo Skudai Patrol', 'email' => 'demo.patrol.skudai@findsight.test', 'role' => 'patrol_unit', 'agency' => 'Unit Peronda Skudai', 'phone' => '+60 11-9000 0003', 'area' => 'SKUDAI'],
            ['name' => 'Demo Johor Bahru Patrol', 'email' => 'demo.patrol.jb@findsight.test', 'role' => 'patrol_unit', 'agency' => 'Unit Peronda Johor Bahru', 'phone' => '+60 11-9000 0004', 'area' => 'JB'],
            ['name' => 'Demo Kuala Lumpur Patrol', 'email' => 'demo.patrol.kl@findsight.test', 'role' => 'patrol_unit', 'agency' => 'Unit Peronda Kuala Lumpur', 'phone' => '+60 11-9000 0005', 'area' => 'KL-CENTRAL'],
            ['name' => 'Demo Public Reporter', 'email' => 'demo.public@findsight.test', 'role' => 'public_user', 'agency' => null, 'phone' => '+60 11-9000 0006'],
        ];

        foreach ($accounts as $account) {
            $areaCode = $account['area'] ?? null;
            unset($account['area']);
            $user = User::updateOrCreate(['email' => $account['email']], $account + [
                'password' => 'FindSightDemo!2026', 'is_active' => true,
            ]);
            $user->patrolAreas()->sync($areaCode ? [PatrolArea::where('code', $areaCode)->value('id')] : []);
        }
    }
}
