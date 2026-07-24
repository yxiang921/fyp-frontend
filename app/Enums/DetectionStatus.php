<?php

namespace App\Enums;

enum DetectionStatus: string
{
    case New = 'new';
    case Reviewed = 'reviewed';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case FalsePositive = 'false_positive';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewed => 'Reviewed',
            self::Confirmed => 'Confirmed',
            self::Rejected => 'Rejected',
            self::FalsePositive => 'False Positive',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'badge-blue',
            self::Reviewed => 'badge-yellow',
            self::Confirmed => 'badge-green',
            self::Rejected => 'badge-red',
            self::FalsePositive => 'badge-gray',
        };
    }
}
