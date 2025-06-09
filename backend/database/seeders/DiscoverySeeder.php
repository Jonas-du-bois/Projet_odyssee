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
        // Create comprehensive discoveries linking to chapters
        $discoveries = [
            [
                'chapter_id' => 1, // Breitling et l'aviation
                'available_date' => '2025-01-15'
            ],
            [
                'chapter_id' => 2, // Collections emblématiques
                'available_date' => '2025-01-20'
            ],
            [
                'chapter_id' => 6, // Salon de Genève 2025
                'available_date' => '2025-01-25'
            ],
            [
                'chapter_id' => 7, // Calibres manufacturés
                'available_date' => '2025-02-01'
            ],
            [
                'chapter_id' => 1, // Breitling et l'aviation (série 2)
                'available_date' => '2025-02-05'
            ]
        ];

        foreach ($discoveries as $discoveryData) {
            Discovery::create($discoveryData);
        }
    }
}
