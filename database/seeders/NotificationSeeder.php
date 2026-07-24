<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['alert_new', 'alert_updated', 'verification_requested', 'verification_completed', 'detection_new', 'case_update'];
        $titles = [
            'alert_new' => ['New High-Priority Alert', 'New Alert Generated', 'Critical Alert Detected', 'Alert: Potential Match Found'],
            'alert_updated' => ['Alert Status Updated', 'Alert Escalated', 'Alert Under Review', 'Alert Assigned'],
            'verification_requested' => ['Verification Request Submitted', 'New Verification Request', 'JPN Verification Required'],
            'verification_completed' => ['Verification Completed', 'Identity Verification Result', 'JPN Confirmation Received'],
            'detection_new' => ['New Detection Recorded', 'High-Similarity Detection', 'Facial Match Detected'],
            'case_update' => ['Case Status Updated', 'Case Reopened', 'Case Information Updated'],
        ];
        $messages = [
            'alert_new' => ['A new high-priority alert has been generated with similarity score exceeding threshold.', 'Facial recognition system detected a potential match requiring immediate review.'],
            'alert_updated' => ['The alert has been escalated for priority review by senior officer.', 'Alert status has been changed and reassigned to investigating officer.'],
            'verification_requested' => ['A police officer has requested JPN verification for a detection match.', 'New identity verification request pending JPN review.'],
            'verification_completed' => ['JPN has completed the verification review. Result available.', 'Identity verification outcome has been recorded.'],
            'detection_new' => ['A new facial recognition detection has been recorded by the system.', 'New potential match detected by surveillance camera.'],
            'case_update' => ['Case file has been updated with new information.', 'Missing person case status has been modified.'],
        ];

        // Generate 100 notifications
        for ($i = 0; $i < 100; $i++) {
            $type = $types[array_rand($types)];
            $title = $titles[$type][array_rand($titles[$type])];
            $message = $messages[$type][array_rand($messages[$type])];
            $isRead = rand(0, 100) < 60; // 60% read

            Notification::create([
                'user_id' => rand(1, 10),
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => ['type' => $type, 'generated_at' => now()->subDays(rand(0, 30))->toDateTimeString()],
                'read_at' => $isRead ? now()->subDays(rand(0, 14)) : null,
            ]);
        }
    }
}
