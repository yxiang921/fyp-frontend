<?php

namespace App\Enums;

enum AlertPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Critical => 'Critical',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Low => 'badge-green',
            self::Medium => 'badge-blue',
            self::High => 'badge-orange',
            self::Critical => 'badge-red',
        };
    }
}
