<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'DCP Azman bin Ibrahim',
                'email' => 'admin@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'agency' => 'PDRM IT Department, Bukit Aman',
                'phone' => '+60 12-345 6700',
                'is_active' => true,
            ],
            [
                'name' => 'ASP Kamal bin Hassan',
                'email' => 'kamal@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'pdrm_officer',
                'agency' => 'IPD Dang Wangi, Kuala Lumpur',
                'phone' => '+60 12-345 6701',
                'is_active' => true,
            ],
            [
                'name' => 'Insp. Tan Mei Hua',
                'email' => 'tan@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'pdrm_officer',
                'agency' => 'IPD Petaling Jaya, Selangor',
                'phone' => '+60 12-345 6702',
                'is_active' => true,
            ],
            [
                'name' => 'Insp. Norazlina binti Kamarudin',
                'email' => 'norazlina@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'pdrm_officer',
                'agency' => 'IPD Johor Bahru, Johor',
                'phone' => '+60 12-345 6703',
                'is_active' => true,
            ],
            [
                'name' => 'Insp. Rajesh a/l Muthu',
                'email' => 'rajesh@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'pdrm_officer',
                'agency' => 'IPD George Town, Penang',
                'phone' => '+60 12-345 6704',
                'is_active' => true,
            ],
            [
                'name' => 'Insp. Lim Wei Jie',
                'email' => 'lim@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'pdrm_officer',
                'agency' => 'IPD Kuching, Sarawak',
                'phone' => '+60 12-345 6705',
                'is_active' => true,
            ],
            [
                'name' => 'Sjn. Mohd Fauzi bin Abdullah',
                'email' => 'fauzi@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'patrol_unit',
                'agency' => 'Unit Peronda, IPD Dang Wangi',
                'phone' => '+60 12-345 6706',
                'is_active' => true,
            ],
            [
                'name' => 'Kpl. Ahmad Syafiq bin Razak',
                'email' => 'syafiq@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'patrol_unit',
                'agency' => 'Unit Peronda, IPD Petaling Jaya',
                'phone' => '+60 12-345 6707',
                'is_active' => true,
            ],
            [
                'name' => 'Puan Sarah binti Ismail',
                'email' => 'sarah@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'public_user',
                'agency' => null,
                'phone' => '+60 12-345 6708',
                'is_active' => true,
            ],
            [
                'name' => 'Encik Rahim bin Hassan',
                'email' => 'rahim@frms.gov.my',
                'password' => Hash::make('password'),
                'role' => 'public_user',
                'agency' => null,
                'phone' => '+60 12-345 6709',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
