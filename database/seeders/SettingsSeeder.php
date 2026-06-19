<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'college_name'              => 'KIPS College Chakwal',
            'college_address'           => 'Chakwal, Punjab, Pakistan',
            'attendance_late_time'      => '08:30',
            'attendance_min_percent'    => '75',
            'attendance_warning_percent'=> '80',
            'session_timeout_minutes'   => '30',
            'login_max_attempts'        => '5',
            'login_lockout_minutes'     => '15',
            'theme_primary_color'       => '#1E3A5F',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
