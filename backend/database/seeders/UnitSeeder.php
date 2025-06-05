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
                'description' => 'Foundation and early years',
                'theory_html' => '/theory/units/the_origins.html',
            ],
            [
                'chapter_id' => 1,
                'title' => 'Brand Values',
                'description' => 'DNA and pillars of Breitling',
                'theory_html' => '/theory/units/brand_values.html'
            ],
            [
                'chapter_id' => 2,
                'title' => 'Chronomat B01 42 Triumph',
                'description' => 'Presentation of the flagship model',
                'theory_html' => '/theory/units/chronomat_b01.html'
            ],
            [
                'chapter_id' => 3,
                'title' => 'Manufacture Calibers',
                'description' => 'In-house developed movements',
                'theory_html' => '/theory/units/manufacture_calibers.html'
            ],
            [
                'chapter_id' => 1,
                'title' => 'Aviation Partnerships',
                'description' => 'Collaborations with aviation industry',
                'theory_html' => '/theory/units/aviation_partnerships.html'
            ],
            [
                'chapter_id' => 2,
                'title' => 'Navitimer Collection',
                'description' => 'The iconic pilot watch series',
                'theory_html' => '/theory/units/navitimer_collection.html'
            ],
            [
                'chapter_id' => 2,
                'title' => 'Superocean Heritage',
                'description' => 'Professional diving watches',
                'theory_html' => '/theory/units/superocean_heritage.html'
            ],
            [
                'chapter_id' => 3,
                'title' => 'Smart Technology',
                'description' => 'Integration of digital features',
                'theory_html' => '/theory/units/smart_technology.html'
            ],
            [
                'chapter_id' => 4,
                'title' => 'Léon Breitling Era',
                'description' => 'The founder\'s legacy and vision',
                'theory_html' => '/theory/units/leon_breitling_era.html'
            ],
            [
                'chapter_id' => 4,
                'title' => 'Chronograph Evolution',
                'description' => 'Development of timing instruments',
                'theory_html' => '/theory/units/chronograph_evolution.html'
            ],
            [
                'chapter_id' => 5,
                'title' => 'Swiss Craftsmanship',
                'description' => 'Traditional watchmaking techniques',
                'theory_html' => '/theory/units/swiss_craftsmanship.html'
            ],
            [
                'chapter_id' => 5,
                'title' => 'Quality Control',
                'description' => 'Testing and certification processes',
                'theory_html' => '/theory/units/quality_control.html'
            ],
            [
                'chapter_id' => 6,
                'title' => 'Breitling Jet Team',
                'description' => 'Aerobatic demonstration squadron',
                'theory_html' => '/theory/units/breitling_jet_team.html'
            ],
            [
                'chapter_id' => 6,
                'title' => 'Ocean Conservation',
                'description' => 'Environmental protection initiatives',
                'theory_html' => '/theory/units/ocean_conservation.html'
            ],
            [
                'chapter_id' => 7,
                'title' => 'Caliber B01',
                'description' => 'Flagship chronograph movement',
                'theory_html' => '/theory/units/caliber_b01.html'
            ],
            [
                'chapter_id' => 8,
                'title' => 'Sustainable Materials',
                'description' => 'Eco-friendly production practices',
                'theory_html' => '/theory/units/sustainable_materials.html'
            ]
        ];

        foreach ($units as $unitData) {
            Unit::create($unitData);
        }
    }
}
