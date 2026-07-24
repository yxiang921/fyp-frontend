<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case InsufficientData = 'insufficient_data';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Rejected => 'Rejected',
            self::InsufficientData => 'Insufficient Data',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'badge-yellow',
            self::Confirmed => 'badge-green',
            self::Rejected => 'badge-red',
            self::InsufficientData => 'badge-gray',
        };
    }
}
