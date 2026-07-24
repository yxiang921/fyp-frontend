<?php

namespace Database\Seeders;

use App\Models\Alert;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['new', 'new', 'new', 'under_review', 'under_review', 'escalated', 'closed', 'false_positive'];
        $priorities = ['low', 'medium', 'medium', 'high', 'high', 'critical'];

        // Generate 75 alerts — all missing_person type
        for ($i = 0; $i < 75; $i++) {
            $alertNum = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $score = rand(40, 98) + (rand(0, 99) / 100);
            $status = $statuses[array_rand($statuses)];
            $priority = in_array($status, ['new', 'under_review', 'escalated']) ? $priorities[array_rand($priorities)] : 'low';

            $resolvedAt = null;
            if (in_array($status, ['closed', 'false_positive'])) {
                $resolvedAt = now()->subDays(rand(1, 30));
            }

            // Assign some alerts to patrol_unit users (IDs 7-8)
            $assignedTo = null;
            if (in_array($status, ['under_review', 'escalated'])) {
                $assignedTo = rand(1, 8); // can be PDRM officer (2-6) or patrol (7-8)
            }

            Alert::create([
                'alert_id' => 'ALT-2024-' . $alertNum,
                'detection_id' => rand(1, 200),
                'alert_type' => 'missing_person',
                'reference_type' => 'App\Models\MissingPerson',
                'reference_id' => rand(1, 50),
                'similarity_score' => $score,
                'camera_id' => rand(1, 15),
                'status' => $status,
                'priority' => $priority,
                'assigned_to' => $assignedTo,
                'description' => 'Alert generated from facial recognition detection. Similarity score: ' . number_format($score, 1) . '%.',
                'resolved_at' => $resolvedAt,
            ]);
        }
    }
}
