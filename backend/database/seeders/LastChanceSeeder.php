<?php

namespace Database\Seeders;

use App\Models\LastChance;
use Illuminate\Database\Seeder;

class LastChanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple last chances at once
        $lastChances = [
            
            [
                'name' => 'Dernière chance',
                'start_date' => '2025-12-05',
                'end_date' => '2025-12-31'
            ]
        ];

        foreach ($lastChances as $lastChanceData) {
            LastChance::create($lastChanceData);
        }
    }
}
