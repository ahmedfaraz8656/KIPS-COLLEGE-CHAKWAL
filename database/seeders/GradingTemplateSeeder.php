<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradingTemplate;
use App\Models\GradingRule;

class GradingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = GradingTemplate::firstOrCreate(
            ['name' => 'Standard Grading'],
            ['min_pass_percent' => 33, 'is_default' => true]
        );

        $rules = [
            ['from_percent' => 90, 'to_percent' => 100, 'grade' => 'A+', 'remarks' => 'Outstanding'],
            ['from_percent' => 80, 'to_percent' => 89.99, 'grade' => 'A',  'remarks' => 'Excellent'],
            ['from_percent' => 70, 'to_percent' => 79.99, 'grade' => 'B',  'remarks' => 'Very Good'],
            ['from_percent' => 60, 'to_percent' => 69.99, 'grade' => 'C',  'remarks' => 'Good'],
            ['from_percent' => 50, 'to_percent' => 59.99, 'grade' => 'D',  'remarks' => 'Satisfactory'],
            ['from_percent' => 33, 'to_percent' => 49.99, 'grade' => 'E',  'remarks' => 'Pass'],
            ['from_percent' => 0,  'to_percent' => 32.99, 'grade' => 'F',  'remarks' => 'Fail'],
        ];

        foreach ($rules as $rule) {
            GradingRule::firstOrCreate(
                ['grading_template_id' => $template->id, 'grade' => $rule['grade']],
                $rule + ['grading_template_id' => $template->id]
            );
        }
    }
}
