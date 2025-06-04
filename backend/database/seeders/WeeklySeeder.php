<?php

namespace Database\Seeders;

use App\Models\Weekly;
use Illuminate\Database\Seeder;

class WeeklySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple weekly quizzes at once
        $weeklies = [
            [
                'chapter_id' => 1,
                'week_start' => '2025-01-06',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 1,
                'week_start' => '2025-01-13',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 2,
                'week_start' => '2025-01-20',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 2,
                'week_start' => '2025-01-27',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 3,
                'week_start' => '2025-02-03',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 3,
                'week_start' => '2025-02-10',
                'number_questions' => 4
            ]
        ];

        foreach ($weeklies as $weeklyData) {
            Weekly::create($weeklyData);
        }
    }
}
