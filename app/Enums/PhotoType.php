<?php

namespace App\Enums;

enum PhotoType: string
{
    case Front = 'front';
    case LeftProfile = 'left_profile';
    case RightProfile = 'right_profile';
    case Body = 'body';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Front => 'Front',
            self::LeftProfile => 'Left Profile',
            self::RightProfile => 'Right Profile',
            self::Body => 'Body',
            self::Other => 'Other',
        };
    }
}
