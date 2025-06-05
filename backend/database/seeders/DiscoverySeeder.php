<?php

namespace Database\Seeders;

use App\Models\Discovery;
use Illuminate\Database\Seeder;

class DiscoverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple discoveries at once
        $discoveries = [
            [
                'chapter_id' => 1,
                'available_date' => '2025-01-15'
            ],
            [
                'chapter_id' => 2,
                'available_date' => '2025-02-01'
            ],
            [
                'chapter_id' => 3,
                'available_date' => '2025-02-15'
            ]
        ];

        foreach ($discoveries as $discoveryData) {
            Discovery::create($discoveryData);
        }
    }
}
