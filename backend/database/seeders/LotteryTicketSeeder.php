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
        // Create multiple lottery tickets at once spanning 2025
        $lotteryTickets = [
            // Janvier 2025
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
            
            // Février 2025
            [
                'user_id' => 2,
                'weekly_id' => 3,
                'obtained_date' => '2025-02-05',
                'bonus' => 1
            ],
            [
                'user_id' => 3,
                'weekly_id' => 3,
                'obtained_date' => '2025-02-05',
                'bonus' => 0
            ],
            [
                'user_id' => 4,
                'weekly_id' => 4,
                'obtained_date' => '2025-02-12',
                'bonus' => 1
            ],
            [
                'user_id' => 5,
                'weekly_id' => 4,
                'obtained_date' => '2025-02-12',
                'bonus' => 0
            ],
            
            // Mars 2025
            [
                'user_id' => 2,
                'weekly_id' => 5,
                'obtained_date' => '2025-03-03',
                'bonus' => 0
            ],
            [
                'user_id' => 3,
                'weekly_id' => 5,
                'obtained_date' => '2025-03-03',
                'bonus' => 1
            ],
            [
                'user_id' => 4,
                'weekly_id' => 6,
                'obtained_date' => '2025-03-10',
                'bonus' => 0
            ],
            [
                'user_id' => 5,
                'weekly_id' => 6,
                'obtained_date' => '2025-03-10',
                'bonus' => 1
            ],
            
            // Avril 2025
            [
                'user_id' => 2,
                'weekly_id' => 7,
                'obtained_date' => '2025-04-07',
                'bonus' => 1
            ],
            [
                'user_id' => 3,
                'weekly_id' => 7,
                'obtained_date' => '2025-04-07',
                'bonus' => 0
            ],
            [
                'user_id' => 4,
                'weekly_id' => 8,
                'obtained_date' => '2025-04-14',
                'bonus' => 1
            ],
            [
                'user_id' => 5,
                'weekly_id' => 8,
                'obtained_date' => '2025-04-14',
                'bonus' => 0
            ],
            
            // Mai 2025
            [
                'user_id' => 2,
                'weekly_id' => 9,
                'obtained_date' => '2025-05-05',
                'bonus' => 0
            ],
            [
                'user_id' => 3,
                'weekly_id' => 9,
                'obtained_date' => '2025-05-05',
                'bonus' => 1
            ],
            [
                'user_id' => 4,
                'weekly_id' => 10,
                'obtained_date' => '2025-05-12',
                'bonus' => 0
            ],
            [
                'user_id' => 5,
                'weekly_id' => 10,
                'obtained_date' => '2025-05-12',
                'bonus' => 1
            ],
            
            // Juin 2025
            [
                'user_id' => 2,
                'weekly_id' => 11,
                'obtained_date' => '2025-06-02',
                'bonus' => 1
            ],
            [
                'user_id' => 3,
                'weekly_id' => 11,
                'obtained_date' => '2025-06-02',
                'bonus' => 0
            ]
        ];

        foreach ($lotteryTickets as $lotteryTicketData) {
            LotteryTicket::create($lotteryTicketData);
        }
    }
}
