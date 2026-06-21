<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeCategory;

class FeeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Admission Fee', 'is_recurring' => false],
            ['name' => 'Monthly Tuition Fee', 'is_recurring' => true],
            ['name' => 'Examination Fee', 'is_recurring' => false],
            ['name' => 'Miscellaneous', 'is_recurring' => false],
        ];

        foreach ($categories as $c) {
            FeeCategory::firstOrCreate(['name' => $c['name']], $c);
        }
    }
}
