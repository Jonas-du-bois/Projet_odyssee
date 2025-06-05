<?php

namespace Database\Seeders;

use App\Models\Novelty;
use Illuminate\Database\Seeder;

class NoveltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple novelties at once
        $novelties = [
            [
                'chapter_id' => 1,
                'publication_date' => '2025-01-10',
                'initial_bonus' => 50
            ],
            [
                'chapter_id' => 2,
                'publication_date' => '2025-02-10',
                'initial_bonus' => 75
            ],
            [
                'chapter_id' => 3,
                'publication_date' => '2025-03-10',
                'initial_bonus' => 100
            ]
        ];

        foreach ($novelties as $noveltyData) {
            Novelty::create($noveltyData);
        }
    }
}
