<?php

namespace App\Enums;

enum AlertStatus: string
{
    case New = 'new';
    case UnderReview = 'under_review';
    case Escalated = 'escalated';
    case Closed = 'closed';
    case FalsePositive = 'false_positive';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::UnderReview => 'Under Review',
            self::Escalated => 'Escalated',
            self::Closed => 'Closed',
            self::FalsePositive => 'False Positive',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'badge-blue',
            self::UnderReview => 'badge-yellow',
            self::Escalated => 'badge-red',
            self::Closed => 'badge-green',
            self::FalsePositive => 'badge-gray',
        };
    }
}
