<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventUnits = [
            ['event_id' => 1, 'unit_id' => 1],
            ['event_id' => 1, 'unit_id' => 3],
            ['event_id' => 1, 'unit_id' => 8],
            ['event_id' => 2, 'unit_id' => 5],
            ['event_id' => 2, 'unit_id' => 13]
        ];

        DB::table('event_units')->insert($eventUnits);
    }
}
