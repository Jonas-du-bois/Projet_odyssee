<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple units at once
        $units = [
            [
                'chapter_id' => 1,
                'title' => 'The Origins',
                'description' => 'Foundation and early years'
            ],
            [
                'chapter_id' => 1,
                'title' => 'Brand Values',
                'description' => 'DNA and pillars of Breitling'
            ],
            [
                'chapter_id' => 2,
                'title' => 'Chronomat B01 42 Triumph',
                'description' => 'Presentation of the flagship model'
            ],
            [
                'chapter_id' => 3,
                'title' => 'Manufacture Calibers',
                'description' => 'In-house developed movements'
            ]
        ];

        foreach ($units as $unitData) {
            Unit::create($unitData);
        }
    }
}
