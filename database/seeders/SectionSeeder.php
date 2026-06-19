<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $ics  = Program::where('code', 'ICS')->first();
        $med  = Program::where('code', 'MED')->first();
        $eng  = Program::where('code', 'ENG')->first();
        $fait = Program::where('code', 'FAIT')->first();

        $sections = [
            // ── BOYS — FIRST YEAR ───────────────────────────────
            ['code' => 'PCB1',    'program_id' => $ics->id, 'campus' => 'boys', 'year' => 'first'],
            ['code' => 'PCB2',    'program_id' => $ics->id, 'campus' => 'boys', 'year' => 'first'],
            ['code' => 'PCB3',    'program_id' => $ics->id, 'campus' => 'boys', 'year' => 'first'],
            ['code' => 'PEB/PMB', 'program_id' => $eng->id, 'campus' => 'boys', 'year' => 'first', 'is_combined' => true],

            // ── BOYS — SECOND YEAR ──────────────────────────────
            ['code' => 'SCB1',    'program_id' => $ics->id, 'campus' => 'boys', 'year' => 'second'],
            ['code' => 'SCB2',    'program_id' => $ics->id, 'campus' => 'boys', 'year' => 'second'],
            ['code' => 'SCB3',    'program_id' => $ics->id, 'campus' => 'boys', 'year' => 'second'],
            ['code' => 'SEB/SMB', 'program_id' => $eng->id, 'campus' => 'boys', 'year' => 'second', 'is_combined' => true],

            // ── GIRLS — FIRST YEAR ──────────────────────────────
            ['code' => 'PCG1', 'program_id' => $ics->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'PCG2', 'program_id' => $ics->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'PCG3', 'program_id' => $ics->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'PMG1', 'program_id' => $med->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'PMG2', 'program_id' => $med->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'PMG3', 'program_id' => $med->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'PEG',  'program_id' => $eng->id,  'campus' => 'girls', 'year' => 'first'],
            ['code' => 'FAIT', 'program_id' => $fait->id, 'campus' => 'girls', 'year' => 'first'],

            // ── GIRLS — SECOND YEAR ─────────────────────────────
            ['code' => 'SCG1',   'program_id' => $ics->id,  'campus' => 'girls', 'year' => 'second'],
            ['code' => 'SCG2',   'program_id' => $ics->id,  'campus' => 'girls', 'year' => 'second'],
            ['code' => 'SMG1',   'program_id' => $med->id,  'campus' => 'girls', 'year' => 'second'],
            ['code' => 'SMG2',   'program_id' => $med->id,  'campus' => 'girls', 'year' => 'second'],
            ['code' => 'SMG3',   'program_id' => $med->id,  'campus' => 'girls', 'year' => 'second'],
            ['code' => 'SEG',    'program_id' => $eng->id,  'campus' => 'girls', 'year' => 'second'],
            ['code' => 'FAIT2',  'program_id' => $fait->id, 'campus' => 'girls', 'year' => 'second'],

            // ── BOYS FAIT (both years) — confirm sections if more than one exists ──
            ['code' => 'FAIT-B1', 'program_id' => $fait->id, 'campus' => 'boys', 'year' => 'first'],
            ['code' => 'FAIT-B2', 'program_id' => $fait->id, 'campus' => 'boys', 'year' => 'second'],
        ];

        foreach ($sections as $section) {
            Section::firstOrCreate(['code' => $section['code']], $section);
        }
    }
}
