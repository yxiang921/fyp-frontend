<?php

namespace Database\Seeders;

use App\Models\Detection;
use Illuminate\Database\Seeder;

class DetectionSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['new', 'new', 'new', 'reviewed', 'reviewed', 'reviewed', 'confirmed', 'confirmed', 'rejected', 'false_positive'];

        // Generate 200 detections — all reference MissingPerson
        for ($i = 0; $i < 200; $i++) {
            $detectionNum = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $refId = rand(1, 50);
            $score = $this->generateScore();
            $status = $statuses[array_rand($statuses)];

            // Generate a realistic Flask detection image filename
            $timestamp = now()->subDays(rand(1, 180))->format('Ymd_His');
            $randomHash = substr(md5(rand()), 0, 8);
            $capturedImage = 'http://127.0.0.1:5000/outputs/detections/detection_' . $timestamp . '_' . $randomHash . '.jpg';

            Detection::create([
                'detection_id' => 'DET-2024-' . $detectionNum,
                'camera_id' => rand(1, \App\Models\Camera::count()),
                'captured_image' => $capturedImage,
                'reference_type' => 'App\Models\MissingPerson',
                'reference_id' => $refId,
                'similarity_score' => $score,
                'status' => $status,
                'detected_at' => now()->subDays(rand(1, 180))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'reviewed_by' => in_array($status, ['reviewed', 'confirmed', 'rejected']) ? rand(2, 6) : null,
                'review_notes' => in_array($status, ['reviewed', 'confirmed', 'rejected']) ? 'Reviewed by officer.' : null,
            ]);
        }
    }

    protected function generateScore(): float
    {
        // Generate a realistic distribution — most scores between 40-95%
        $base = rand(35, 97);
        return $base + (rand(0, 99) / 100);
    }
}
