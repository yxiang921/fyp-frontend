<?php

namespace App\Enums;

enum NotificationType: string
{
    case AlertNew = 'alert_new';
    case AlertUpdated = 'alert_updated';
    case VerificationRequested = 'verification_requested';
    case VerificationCompleted = 'verification_completed';
    case DetectionNew = 'detection_new';
    case CaseUpdate = 'case_update';

    public function label(): string
    {
        return match ($this) {
            self::AlertNew => 'New Alert',
            self::AlertUpdated => 'Alert Updated',
            self::VerificationRequested => 'Verification Requested',
            self::VerificationCompleted => 'Verification Completed',
            self::DetectionNew => 'New Detection',
            self::CaseUpdate => 'Case Update',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AlertNew => 'fa-bell',
            self::AlertUpdated => 'fa-bell-on',
            self::VerificationRequested => 'fa-check-circle',
            self::VerificationCompleted => 'fa-check-double',
            self::DetectionNew => 'fa-camera',
            self::CaseUpdate => 'fa-folder-open',
        };
    }
}
