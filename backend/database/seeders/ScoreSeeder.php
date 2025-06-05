<?php

namespace Database\Seeders;

use App\Models\Score;
use Illuminate\Database\Seeder;

class ScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple scores at once with different timestamps throughout 2025
        $scores = [
            // Scores de Janvier
            [
                'user_id' => 2,
                'total_points' => 6500,
                'bonus_points' => 15,
                'rank_id' => 1,
                'created_at' => '2025-01-15 10:00:00',
                'updated_at' => '2025-01-15 10:00:00'
            ],
            [
                'user_id' => 3,
                'total_points' => 12000,
                'bonus_points' => 25,
                'rank_id' => 2,
                'created_at' => '2025-01-20 14:30:00',
                'updated_at' => '2025-01-20 14:30:00'
            ],
            
            // Scores de Février
            [
                'user_id' => 4,
                'total_points' => 8500,
                'bonus_points' => 18,
                'rank_id' => 2,
                'created_at' => '2025-02-10 09:15:00',
                'updated_at' => '2025-02-10 09:15:00'
            ],
            [
                'user_id' => 5,
                'total_points' => 15500,
                'bonus_points' => 35,
                'rank_id' => 3,
                'created_at' => '2025-02-25 16:45:00',
                'updated_at' => '2025-02-25 16:45:00'
            ],
            
            // Scores de Mars
            [
                'user_id' => 2,
                'total_points' => 3200,
                'bonus_points' => 12,
                'rank_id' => 1,
                'created_at' => '2025-03-12 11:20:00',
                'updated_at' => '2025-03-12 11:20:00'
            ],
            [
                'user_id' => 3,
                'total_points' => 4800,
                'bonus_points' => 20,
                'rank_id' => 2,
                'created_at' => '2025-03-28 15:30:00',
                'updated_at' => '2025-03-28 15:30:00'
            ],
            
            // Scores d'Avril
            [
                'user_id' => 4,
                'total_points' => 7200,
                'bonus_points' => 22,
                'rank_id' => 2,
                'created_at' => '2025-04-08 13:45:00',
                'updated_at' => '2025-04-08 13:45:00'
            ],
            [
                'user_id' => 5,
                'total_points' => 9800,
                'bonus_points' => 28,
                'rank_id' => 3,
                'created_at' => '2025-04-22 17:15:00',
                'updated_at' => '2025-04-22 17:15:00'
            ],
            
            // Scores de Mai
            [
                'user_id' => 2,
                'total_points' => 5100,
                'bonus_points' => 16,
                'rank_id' => 1,
                'created_at' => '2025-05-14 10:30:00',
                'updated_at' => '2025-05-14 10:30:00'
            ],
            [
                'user_id' => 3,
                'total_points' => 6700,
                'bonus_points' => 24,
                'rank_id' => 2,
                'created_at' => '2025-05-29 14:50:00',
                'updated_at' => '2025-05-29 14:50:00'
            ],
            
            // Scores de Juin (récents)
            [
                'user_id' => 4,
                'total_points' => 4300,
                'bonus_points' => 14,
                'rank_id' => 1,
                'created_at' => '2025-06-02 09:00:00',
                'updated_at' => '2025-06-02 09:00:00'
            ],
            [
                'user_id' => 5,
                'total_points' => 3900,
                'bonus_points' => 11,
                'rank_id' => 1,
                'created_at' => '2025-06-04 16:25:00',
                'updated_at' => '2025-06-04 16:25:00'
            ]
        ];

        foreach ($scores as $scoreData) {
            Score::create($scoreData);
        }
    }
}
