<?php

namespace Database\Seeders;

use App\Models\QuizType;
use Illuminate\Database\Seeder;

class QuizTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple quiz types at once
        $quizTypes = [
            [
                'name' => 'Discovery Quiz',
                'morph_type' => 'discovery',
                'base_points' => 1000,
                'speed_bonus' => 7,
                'gives_ticket' => 0,
                'bonus_multiplier' => 2
            ],
            [
                'name' => 'Event Quiz',
                'morph_type' => 'event',
                'base_points' => 2000,
                'speed_bonus' => 10,
                'gives_ticket' => 0,
                'bonus_multiplier' => 3
            ],
            [
                'name' => 'Weekly Quiz',
                'morph_type' => 'weekly',
                'base_points' => 0,
                'speed_bonus' => 1,
                'gives_ticket' => 1,
                'bonus_multiplier' => 1
            ],
            [
                'name' => 'Reminder Quiz',
                'morph_type' => 'reminder',
                'base_points' => 1000,
                'speed_bonus' => 3,
                'gives_ticket' => 0,
                'bonus_multiplier' => 1
            ],
            [
                'name' => 'Novelty Quiz',
                'morph_type' => 'novelty',
                'base_points' => 1500,
                'speed_bonus' => 8,
                'gives_ticket' => 0,
                'bonus_multiplier' => 2
            ]
        ];

        foreach ($quizTypes as $quizTypeData) {
            QuizType::create($quizTypeData);
        }
    }
}
