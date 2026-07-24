<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SystemSettingSeeder::class,
            CameraSeeder::class,
            MissingPersonSeeder::class,
            DetectionSeeder::class,
            AlertSeeder::class,
            VerificationRequestSeeder::class,
            NotificationSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
