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
        // Create multiple scores at once
        $scores = [
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
            ]
        ];

        foreach ($scores as $scoreData) {
            Score::create($scoreData);
        }
    }
}
