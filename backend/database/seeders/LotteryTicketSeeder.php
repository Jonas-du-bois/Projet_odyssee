<?php

namespace Database\Seeders;

use App\Models\LotteryTicket;
use Illuminate\Database\Seeder;

class LotteryTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple lottery tickets at once
        $lotteryTickets = [
            [
                'user_id' => 2,
                'weekly_id' => 1,
                'obtained_date' => '2025-01-08',
                'bonus' => 0
            ],
            [
                'user_id' => 2,
                'weekly_id' => 2,
                'obtained_date' => '2025-01-15',
                'bonus' => 0
            ],
            [
                'user_id' => 3,
                'weekly_id' => 1,
                'obtained_date' => '2025-01-08',
                'bonus' => 1
            ],
            [
                'user_id' => 3,
                'weekly_id' => 2,
                'obtained_date' => '2025-01-15',
                'bonus' => 0
            ],
            [
                'user_id' => 4,
                'weekly_id' => 1,
                'obtained_date' => '2025-01-08',
                'bonus' => 0
            ],
            [
                'user_id' => 4,
                'weekly_id' => 3,
                'obtained_date' => '2025-01-22',
                'bonus' => 1
            ],
            [
                'user_id' => 5,
                'weekly_id' => 2,
                'obtained_date' => '2025-01-15',
                'bonus' => 0
            ],
            [
                'user_id' => 5,
                'weekly_id' => 3,
                'obtained_date' => '2025-01-22',
                'bonus' => 0
            ]
        ];

        foreach ($lotteryTickets as $lotteryTicketData) {
            LotteryTicket::create($lotteryTicketData);
        }
    }
}
