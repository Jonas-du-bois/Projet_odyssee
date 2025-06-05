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
                'rank_id' => 1
            ],
            [
                'user_id' => 3,
                'total_points' => 12000,
                'bonus_points' => 25,
                'rank_id' => 2
            ],
            
            // Scores de Février
            [
                'user_id' => 4,
                'total_points' => 8500,
                'bonus_points' => 18,
                'rank_id' => 2
            ],
            [
                'user_id' => 5,
                'total_points' => 15500,
                'bonus_points' => 35,
                'rank_id' => 3
            ],
            
            // Scores de Mars
            [
                'user_id' => 2,
                'total_points' => 3200,
                'bonus_points' => 12,
                'rank_id' => 1
            ],
            [
                'user_id' => 3,
                'total_points' => 4800,
                'bonus_points' => 20,
                'rank_id' => 2
            ],
            
            // Scores d'Avril
            [
                'user_id' => 4,
                'total_points' => 7200,
                'bonus_points' => 22,
                'rank_id' => 2
            ],
            [
                'user_id' => 5,
                'total_points' => 9800,
                'bonus_points' => 28,
                'rank_id' => 3
            ],
            
            // Scores de Mai
            [
                'user_id' => 2,
                'total_points' => 5100,
                'bonus_points' => 16,
                'rank_id' => 1
            ],
            [
                'user_id' => 3,
                'total_points' => 6700,
                'bonus_points' => 24,
                'rank_id' => 2
            ],
            
            // Scores de Juin (récents)
            [
                'user_id' => 4,
                'total_points' => 4300,
                'bonus_points' => 14,
                'rank_id' => 1
            ],
            [
                'user_id' => 5,
                'total_points' => 3900,
                'bonus_points' => 11,
                'rank_id' => 1
            ]
        ];

        foreach ($scores as $scoreData) {
            Score::create($scoreData);
        }
    }
}
