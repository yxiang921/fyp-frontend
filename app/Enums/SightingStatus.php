<?php

namespace App\Enums;

enum SightingStatus: string
{
    case New = 'new';
    case Reviewed = 'reviewed';
    case Confirmed = 'confirmed';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewed => 'Reviewed',
            self::Confirmed => 'Confirmed',
            self::Dismissed => 'Dismissed',
        };
    }
}
