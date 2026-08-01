<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'system.name', 'value' => 'FindSight', 'type' => 'string', 'description' => 'Full system name'],
            ['key' => 'system.short_name', 'value' => 'FindSight', 'type' => 'string', 'description' => 'Short system name'],
            ['key' => 'system.version', 'value' => '1.0.0', 'type' => 'string', 'description' => 'System version'],
            ['key' => 'detection.similarity_threshold', 'value' => '80', 'type' => 'integer', 'description' => 'Minimum similarity score to generate alert'],
            ['key' => 'alert.auto_escalate_hours', 'value' => '24', 'type' => 'integer', 'description' => 'Hours before auto-escalation of un-reviewed alerts'],
            ['key' => 'facial_recognition.disclaimer', 'value' => 'Facial recognition results are NOT final identification. Similarity scores indicate potential matches only. All identity confirmations require human review and JPN verification.', 'type' => 'string', 'description' => 'Disclaimer shown on detection pages'],
            ['key' => 'maintenance.mode', 'value' => '0', 'type' => 'boolean', 'description' => 'System maintenance mode'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}
