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
        // Create multiple user quiz scores at once corresponding to quiz instances
        $userQuizScores = [
            // Quiz instances 1-5 (Janvier)
            [
                'quiz_instance_id' => 1,
                'total_points' => 2500,
                'total_time' => 180,
                'ticket_obtained' => 0,
                'bonus_obtained' => 5
            ],
            [
                'quiz_instance_id' => 2,
                'total_points' => 1800,
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
                'total_points' => 2200,
                'total_time' => 120,
                'ticket_obtained' => 1,
                'bonus_obtained' => 15
            ],
            
            // Quiz instances 6-9 (Février)
            [
                'quiz_instance_id' => 6,
                'total_points' => 2800,
                'total_time' => 165,
                'ticket_obtained' => 1,
                'bonus_obtained' => 8
            ],
            [
                'quiz_instance_id' => 7,
                'total_points' => 2100,
                'total_time' => 190,
                'ticket_obtained' => 0,
                'bonus_obtained' => 12
            ],
            [
                'quiz_instance_id' => 8,
                'total_points' => 3200,
                'total_time' => 210,
                'ticket_obtained' => 1,
                'bonus_obtained' => 20
            ],
            [
                'quiz_instance_id' => 9,
                'total_points' => 1950,
                'total_time' => 135,
                'ticket_obtained' => 0,
                'bonus_obtained' => 5
            ],
            
            // Quiz instances 10-12 (Mars)
            [
                'quiz_instance_id' => 10,
                'total_points' => 2650,
                'total_time' => 175,
                'ticket_obtained' => 1,
                'bonus_obtained' => 18
            ],
            [
                'quiz_instance_id' => 11,
                'total_points' => 2900,
                'total_time' => 185,
                'ticket_obtained' => 1,
                'bonus_obtained' => 22
            ],
            [
                'quiz_instance_id' => 12,
                'total_points' => 2400,
                'total_time' => 160,
                'ticket_obtained' => 0,
                'bonus_obtained' => 14
            ],
            
            // Quiz instances 13-15 (Avril)
            [
                'quiz_instance_id' => 13,
                'total_points' => 2750,
                'total_time' => 145,
                'ticket_obtained' => 1,
                'bonus_obtained' => 16
            ],
            [
                'quiz_instance_id' => 14,
                'total_points' => 3100,
                'total_time' => 195,
                'ticket_obtained' => 1,
                'bonus_obtained' => 25
            ],
            [
                'quiz_instance_id' => 15,
                'total_points' => 2350,
                'total_time' => 170,
                'ticket_obtained' => 0,
                'bonus_obtained' => 11
            ],
            
            // Quiz instances 16-18 (Mai)
            [
                'quiz_instance_id' => 16,
                'total_points' => 2850,
                'total_time' => 155,
                'ticket_obtained' => 1,
                'bonus_obtained' => 19
            ],
            [
                'quiz_instance_id' => 17,
                'total_points' => 2600,
                'total_time' => 180,
                'ticket_obtained' => 0,
                'bonus_obtained' => 13
            ],
            [
                'quiz_instance_id' => 18,
                'total_points' => 3300,
                'total_time' => 200,
                'ticket_obtained' => 1,
                'bonus_obtained' => 28
            ],
            
            // Quiz instances 19-20 (Juin)
            [
                'quiz_instance_id' => 19,
                'total_points' => 2950,
                'total_time' => 165,
                'ticket_obtained' => 1,
                'bonus_obtained' => 21
            ],
            [
                'quiz_instance_id' => 20,
                'total_points' => 2700,
                'total_time' => 175,
                'ticket_obtained' => 0,
                'bonus_obtained' => 15
            ]
        ];

        foreach ($userQuizScores as $userQuizScoreData) {
            UserQuizScore::create($userQuizScoreData);
        }
    }
}
