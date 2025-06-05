<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple events at once
        $events = [
            [
                'theme' => 'Breitling Anniversary',
                'start_date' => '2025-02-01',
                'end_date' => '2025-02-28'
            ],
            [
                'theme' => 'Navitimer Week',
                'start_date' => '2025-03-01',
                'end_date' => '2025-03-07'
            ],
            [
                'theme' => 'Watchmaking Innovation',
                'start_date' => '2025-04-01',
                'end_date' => '2025-04-07'
            ]
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }
    }
}
