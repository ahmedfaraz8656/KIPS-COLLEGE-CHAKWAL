<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Subject;
use App\Models\ProgramSubject;

/**
 * Default subjects + marks per Program + Year, confirmed by Ahmed for the
 * Test Series (Test 1–10). The 6th slot (Religious/Studies) is a ROTATING
 * pair — both subjects are seeded with is_rotating=true and the same
 * rotation_group so the Exam Creation module can alternate between them
 * per test (Test 1 = TTQ, Test 2 = Islamiyat, etc. for 1st Year;
 * similarly SST/PST for 2nd Year).
 *
 * FLP 1, FLP 2, Send Up, and Pre-Board exams use ALL subjects in a
 * rotation_group (both Islamiyat AND TTQ / both SST AND PST) since they
 * are cumulative exams — this is handled in the Exam Controller logic,
 * not in this seeder.
 */
class ProgramSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $sub = fn (string $code) => Subject::where('short_code', $code)->first()->id;
        $prog = fn (string $code) => Program::where('code', $code)->first()->id;

        $rows = [];

        // ════════════════════════════════════════════════════════
        // FIRST YEAR — rotating pair: Islamiyat ↔ TTQ
        // ════════════════════════════════════════════════════════
        $firstYearRotation = ['rotation_group' => 'religious_1st_yr', 'is_rotating' => true];

        // ICS — Physics, Computer, English, Math, Urdu, Islamiyat/TTQ
        $rows = array_merge($rows, [
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('PHY'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('CS'),   'year' => 'first', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('ENG'),  'year' => 'first', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('MATH'), 'year' => 'first', 'default_marks' => 30, 'sort_order' => 4],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('URD'),  'year' => 'first', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('ISL'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('TTQ'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
        ]);

        // Medical — Physics, Chemistry, English, Biology, Urdu, Islamiyat/TTQ
        $rows = array_merge($rows, [
            ['program_id' => $prog('MED'), 'subject_id' => $sub('PHY'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('CHEM'), 'year' => 'first', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('ENG'),  'year' => 'first', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('BIO'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('URD'),  'year' => 'first', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('ISL'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
            ['program_id' => $prog('MED'), 'subject_id' => $sub('TTQ'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
        ]);

        // Engineering — Physics, Chemistry, English, Math, Urdu, Islamiyat/TTQ
        $rows = array_merge($rows, [
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('PHY'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('CHEM'), 'year' => 'first', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('ENG'),  'year' => 'first', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('MATH'), 'year' => 'first', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('URD'),  'year' => 'first', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('ISL'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('TTQ'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
        ]);

        // FAIT — Sociology, Computer, English, Psychology/Education, Urdu, Islamiyat/TTQ
        // NOTE: Psychology used for Girls FAIT, Education used for Boys FAIT —
        // both seeded here; Section gender determines which one displays.
        $rows = array_merge($rows, [
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('SOC'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('CS'),   'year' => 'first', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('ENG'),  'year' => 'first', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('PSY'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('EDU'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('URD'),  'year' => 'first', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('ISL'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('TTQ'),  'year' => 'first', 'default_marks' => 25, 'sort_order' => 6] + $firstYearRotation,
        ]);

        // ════════════════════════════════════════════════════════
        // SECOND YEAR — rotating pair: SST ↔ PST
        // ⚠️ Pending Ahmed's final confirmation on exact naming —
        //    see PROMPT_EXAM_SUBJECTS_CONFIG.md item #3
        // ════════════════════════════════════════════════════════
        $secondYearRotation = ['rotation_group' => 'studies_2nd_yr', 'is_rotating' => true];

        $rows = array_merge($rows, [
            // ICS
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('PHY'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('CS'),   'year' => 'second', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('ENG'),  'year' => 'second', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('MATH'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('URD'),  'year' => 'second', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('SST'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,
            ['program_id' => $prog('ICS'), 'subject_id' => $sub('PST'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,

            // Medical
            ['program_id' => $prog('MED'), 'subject_id' => $sub('PHY'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('CHEM'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('ENG'),  'year' => 'second', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('BIO'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('URD'),  'year' => 'second', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('MED'), 'subject_id' => $sub('SST'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,
            ['program_id' => $prog('MED'), 'subject_id' => $sub('PST'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,

            // Engineering
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('PHY'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('CHEM'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('ENG'),  'year' => 'second', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('MATH'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('URD'),  'year' => 'second', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('SST'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,
            ['program_id' => $prog('ENG'), 'subject_id' => $sub('PST'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,

            // FAIT (Psychology=Girls, Education=Boys)
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('SOC'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 1],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('CS'),  'year' => 'second', 'default_marks' => 25, 'sort_order' => 2],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('ENG'), 'year' => 'second', 'default_marks' => 40, 'sort_order' => 3],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('PSY'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('EDU'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 4],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('URD'), 'year' => 'second', 'default_marks' => 30, 'sort_order' => 5],
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('SST'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,
            ['program_id' => $prog('FAIT'), 'subject_id' => $sub('PST'), 'year' => 'second', 'default_marks' => 25, 'sort_order' => 6] + $secondYearRotation,
        ]);

        foreach ($rows as $row) {
            ProgramSubject::firstOrCreate(
                ['program_id' => $row['program_id'], 'subject_id' => $row['subject_id'], 'year' => $row['year']],
                $row
            );
        }
    }
}
