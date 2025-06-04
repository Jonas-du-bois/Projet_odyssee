<?php

namespace Database\Seeders;

use App\Models\UserAnswer;
use Illuminate\Database\Seeder;

class UserAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple user answers at once
        $userAnswers = [
            [
                'user_id' => 2,
                'question_id' => 1,
                'choice_id' => 1,
                'is_correct' => 1,
                'response_time' => 15,
                'points_obtained' => 15,
                'date' => '2025-01-10 10:01:15'
            ],
            [
                'user_id' => 2,
                'question_id' => 2,
                'choice_id' => 5,
                'is_correct' => 1,
                'response_time' => 20,
                'points_obtained' => 10,
                'date' => '2025-01-10 10:02:35'
            ]
        ];

        foreach ($userAnswers as $userAnswerData) {
            UserAnswer::create($userAnswerData);
        }
    }
}
