<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Physics',           'short_code' => 'PHY'],
            ['name' => 'Chemistry',         'short_code' => 'CHEM'],
            ['name' => 'Biology',           'short_code' => 'BIO'],
            ['name' => 'Mathematics',       'short_code' => 'MATH'],
            ['name' => 'Computer Science',  'short_code' => 'CS'],
            ['name' => 'English',           'short_code' => 'ENG'],
            ['name' => 'Urdu',              'short_code' => 'URD'],
            ['name' => 'Islamiyat',         'short_code' => 'ISL'],
            ['name' => 'Tarjumatul Quran',  'short_code' => 'TTQ'],
            ['name' => 'Pakistan Studies',  'short_code' => 'PST'],
            ['name' => 'Social Studies',    'short_code' => 'SST'],
            ['name' => 'Sociology',         'short_code' => 'SOC'],
            ['name' => 'Psychology',        'short_code' => 'PSY'],
            ['name' => 'Education',         'short_code' => 'EDU'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['short_code' => $subject['short_code']], $subject);
        }
    }
}
