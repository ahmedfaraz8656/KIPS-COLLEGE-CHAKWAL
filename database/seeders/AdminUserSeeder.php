<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@kipscollege.edu.pk'],
            [
                'name' => 'Ahmed Faraz',
                'password' => Hash::make('Admin@123'),
                'whatsapp' => '03000000000',
                'gender' => 'male',
                'campus' => 'both',
                'status' => true,
                'force_password_change' => true,
            ]
        );

        $admin->assignRole('Managing Director');

        // ⚠️ IMPORTANT: Change this password immediately after first login.
        // Default login: admin@kipscollege.edu.pk / Admin@123
    }
}
