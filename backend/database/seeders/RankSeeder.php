<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple ranks at once
        $ranks = [
            [
                'name' => 'Rubber',
                'level' => 1,
                'minimum_points' => 0
            ],
            [
                'name' => 'Fabric',
                'level' => 2,
                'minimum_points' => 250000
            ],
            [
                'name' => 'Exotic Leather',
                'level' => 3,
                'minimum_points' => 350000
            ],
            [
                'name' => 'Steel',
                'level' => 4,
                'minimum_points' => 690000
            ],
            [
                'name' => 'Gold',
                'level' => 5,
                'minimum_points' => 1000000
            ],
            [
                'name' => 'Titanium',
                'level' => 6,
                'minimum_points' => 1241000
            ]
        ];

        foreach ($ranks as $rankData) {
            Rank::create($rankData);
        }
    }
}
