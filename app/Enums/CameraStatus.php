<?php

namespace App\Enums;

enum CameraStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Maintenance = 'maintenance';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Offline => 'Offline',
            self::Maintenance => 'Maintenance',
            self::Error => 'Error',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Online => 'badge-green',
            self::Offline => 'badge-gray',
            self::Maintenance => 'badge-yellow',
            self::Error => 'badge-red',
        };
    }

    public function dot(): string
    {
        return match ($this) {
            self::Online => 'text-green-500',
            self::Offline => 'text-gray-400',
            self::Maintenance => 'text-yellow-500',
            self::Error => 'text-red-500',
        };
    }
}
