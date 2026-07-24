<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case PdrmOfficer = 'pdrm_officer';
    case PatrolUnit = 'patrol_unit';
    case PublicUser = 'public_user';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin (PDRM IT Department)',
            self::PdrmOfficer => 'PDRM Officer',
            self::PatrolUnit => 'Patrol Unit',
            self::PublicUser => 'Public User',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::SuperAdmin => 'badge-purple',
            self::PdrmOfficer => 'badge-blue',
            self::PatrolUnit => 'badge-teal',
            self::PublicUser => 'badge-green',
        };
    }

    public function dashboard(): string
    {
        return match ($this) {
            self::SuperAdmin => 'dashboard.admin',
            self::PdrmOfficer => 'dashboard.pdrm',
            self::PatrolUnit => 'dashboard.patrol',
            self::PublicUser => 'dashboard.public',
        };
    }
}
