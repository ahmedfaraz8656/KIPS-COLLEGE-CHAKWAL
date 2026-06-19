<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['code' => 'ICS',  'name' => 'Intermediate Computer Science', 'campus_scope' => 'both'],
            ['code' => 'MED',  'name' => 'FSc Pre-Medical',                'campus_scope' => 'both'],
            ['code' => 'ENG',  'name' => 'FSc Pre-Engineering',            'campus_scope' => 'both'],
            ['code' => 'FAIT', 'name' => 'Faculty of Arts',                'campus_scope' => 'both'],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
