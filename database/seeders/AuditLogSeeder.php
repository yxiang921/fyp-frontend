<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['Missing Persons', 'Persons of Interest', 'Detections', 'Alerts', 'Verifications', 'Cameras', 'Users', 'Authentication', 'System'];
        $actions = ['Create', 'Update', 'Delete', 'View', 'Login', 'Logout', 'Export', 'Assign'];

        $descriptions = [
            'Missing Persons' => ['Created new missing person case', 'Updated case status from Active to Found', 'Deleted case record', 'Viewed case details'],
            'Persons of Interest' => ['Created new POI record', 'Updated risk level', 'Changed POI status', 'Viewed POI profile'],
            'Detections' => ['Auto-generated detection', 'Reviewed detection record', 'Confirmed facial match', 'Marked as false positive'],
            'Alerts' => ['Alert auto-generated from detection', 'Changed alert status', 'Assigned alert to officer', 'Escalated alert to critical'],
            'Verifications' => ['Submitted verification request', 'Reviewed verification request', 'Confirmed identity match', 'Rejected identity match'],
            'Cameras' => ['Registered new camera', 'Updated camera configuration', 'Changed camera status'],
            'Users' => ['Created new user account', 'Updated user profile', 'Deactivated user account', 'Changed user role'],
            'Authentication' => ['User logged in successfully', 'Failed login attempt', 'User logged out', 'Password changed'],
            'System' => ['System configuration updated', 'Scheduled task executed', 'System backup completed'],
        ];

        // Generate 200 audit logs
        for ($i = 0; $i < 200; $i++) {
            $module = $modules[array_rand($modules)];
            $action = $actions[array_rand($actions)];

            $moduleDescriptions = $descriptions[$module] ?? ['System action performed'];
            $description = $moduleDescriptions[array_rand($moduleDescriptions)];

            AuditLog::create([
                'user_id' => rand(1, 10),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => rand(0, 1) ? json_encode(['status' => 'previous_value', 'updated_at' => now()->subDays(rand(1, 90))->toDateTimeString()]) : null,
                'new_values' => rand(0, 1) ? json_encode(['status' => 'new_value', 'updated_at' => now()->subDays(rand(0, 30))->toDateTimeString()]) : null,
                'ip_address' => '192.168.' . rand(1, 5) . '.' . rand(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);
        }
    }
}
