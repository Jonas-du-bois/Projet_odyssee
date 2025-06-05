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
        // Create multiple weekly quizzes spanning 2025
        $weeklies = [
            // Janvier 2025
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
            
            // Février 2025
            [
                'chapter_id' => 3,
                'week_start' => '2025-02-03',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 3,
                'week_start' => '2025-02-10',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 4,
                'week_start' => '2025-02-17',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 4,
                'week_start' => '2025-02-24',
                'number_questions' => 4
            ],
            
            // Mars 2025
            [
                'chapter_id' => 1,
                'week_start' => '2025-03-03',
                'number_questions' => 5
            ],
            [
                'chapter_id' => 2,
                'week_start' => '2025-03-10',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 3,
                'week_start' => '2025-03-17',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 4,
                'week_start' => '2025-03-24',
                'number_questions' => 4
            ],
            
            // Avril 2025
            [
                'chapter_id' => 1,
                'week_start' => '2025-04-07',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 2,
                'week_start' => '2025-04-14',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 3,
                'week_start' => '2025-04-21',
                'number_questions' => 5
            ],
            [
                'chapter_id' => 4,
                'week_start' => '2025-04-28',
                'number_questions' => 3
            ],
            
            // Mai 2025
            [
                'chapter_id' => 1,
                'week_start' => '2025-05-05',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 2,
                'week_start' => '2025-05-12',
                'number_questions' => 3
            ],
            [
                'chapter_id' => 3,
                'week_start' => '2025-05-19',
                'number_questions' => 4
            ],
            [
                'chapter_id' => 4,
                'week_start' => '2025-05-26',
                'number_questions' => 5
            ],
            
            // Juin 2025
            [
                'chapter_id' => 1,
                'week_start' => '2025-06-02',
                'number_questions' => 3
            ]
        ];

        foreach ($weeklies as $weeklyData) {
            Weekly::create($weeklyData);
        }
    }
}
