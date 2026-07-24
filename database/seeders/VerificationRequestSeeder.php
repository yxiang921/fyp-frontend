<?php

namespace Database\Seeders;

use App\Models\VerificationRequest;
use Illuminate\Database\Seeder;

class VerificationRequestSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'pending', 'pending', 'pending', 'confirmed', 'confirmed', 'rejected', 'rejected', 'insufficient_data'];

        // Generate 40 verification requests
        for ($i = 0; $i < 40; $i++) {
            $requestNum = str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $status = $statuses[array_rand($statuses)];
            $isCompleted = in_array($status, ['confirmed', 'rejected', 'insufficient_data']);

            VerificationRequest::create([
                'request_id' => 'VRF-2024-' . $requestNum,
                'detection_id' => rand(1, 200),
                'alert_id' => rand(1, 75),
                'requested_by' => rand(2, 6), // PDRM officers
                'reviewed_by' => $isCompleted ? rand(2, 6) : null, // Reviewed by PDRM officers
                'status' => $status,
                'jpn_notes' => $isCompleted ? $this->reviewNote($status) : null,
                'police_notes' => 'Requesting identity verification for facial recognition match.',
                'verified_at' => $isCompleted ? now()->subDays(rand(1, 60)) : null,
            ]);
        }
    }

    protected function reviewNote(string $status): string
    {
        return match ($status) {
            'confirmed' => 'Identity confirmed against official records. Name, IC number, and DOB match.',
            'rejected' => 'Identity does NOT match official records. Facial recognition result is a false match.',
            'insufficient_data' => 'Insufficient data in official records for positive identification. Additional supporting documents required.',
            default => '',
        };
    }
}
