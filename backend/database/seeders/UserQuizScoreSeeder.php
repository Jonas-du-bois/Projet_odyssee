<?php

namespace Database\Seeders;

use App\Models\UserQuizScore;
use Illuminate\Database\Seeder;

class UserQuizScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple user quiz scores at once
        $userQuizScores = [
            [
                'quiz_instance_id' => 1,
                'total_points' => 2500,
                'total_time' => 180,
                'ticket_obtained' => 0,
                'bonus_obtained' => 5
            ],
            [
                'quiz_instance_id' => 2,
                'total_points' => 0,
                'total_time' => 150,
                'ticket_obtained' => 1,
                'bonus_obtained' => 10
            ],
            [
                'quiz_instance_id' => 3,
                'total_points' => 3000,
                'total_time' => 200,
                'ticket_obtained' => 0,
                'bonus_obtained' => 0
            ],
            [
                'quiz_instance_id' => 4,
                'total_points' => 1800,
                'total_time' => 240,
                'ticket_obtained' => 0,
                'bonus_obtained' => 3
            ],
            [
                'quiz_instance_id' => 5,
                'total_points' => 0,
                'total_time' => 120,
                'ticket_obtained' => 1,
                'bonus_obtained' => 15
            ]
        ];

        foreach ($userQuizScores as $userQuizScoreData) {
            UserQuizScore::create($userQuizScoreData);
        }
    }
}
