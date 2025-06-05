<?php

namespace Database\Seeders;

use App\Models\WeeklySeries;
use Illuminate\Database\Seeder;

class WeeklySeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple weekly series at once
        $weeklySeries = [
            [
                'user_id' => 2,
                'count' => 2,
                'bonus_tickets' => 1,
                'last_participation' => '2025-01-22'
            ],
            [
                'user_id' => 3,
                'count' => 3,
                'bonus_tickets' => 1,
                'last_participation' => '2025-01-22'
            ],
            [
                'user_id' => 4,
                'count' => 1,
                'bonus_tickets' => 0,
                'last_participation' => '2025-01-15'
            ],
            [
                'user_id' => 5,
                'count' => 4,
                'bonus_tickets' => 2,
                'last_participation' => '2025-01-29'
            ]
        ];

        foreach ($weeklySeries as $weeklySeriesData) {
            WeeklySeries::create($weeklySeriesData);
        }
    }
}
