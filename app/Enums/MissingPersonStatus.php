<?php

namespace App\Enums;

enum MissingPersonStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Found = 'found';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Verification',
            self::Active => 'Active',
            self::Found => 'Found',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'badge-yellow',
            self::Active => 'badge-red',
            self::Found => 'badge-green',
            self::Closed => 'badge-gray',
            self::Reopened => 'badge-yellow',
        };
    }
}
