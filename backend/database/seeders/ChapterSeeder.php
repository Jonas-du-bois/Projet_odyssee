<?php

namespace Database\Seeders;

use App\Models\Chapter;
use Illuminate\Database\Seeder;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Alternative: Create multiple chapters at once
        $chapters = [
            [
                'title' => '[discovery] Breitling et l\'aviation',
                'description' => 'L\'héritage aéronautique de la marque'
            ],
            [
                'title' => '[discovery] Collections emblématiques',
                'description' => 'Navitimer, Superocean et autres collections phares'
            ],
            [
                'title' => '[novelties] Innovations technologiques',
                'description' => 'Les dernières avancées horlogères de Breitling'
            ],
            [
                'title' => '[Reminder] Histoire de Breitling',
                'description' => 'De 1884 à aujourd\'hui, 140 ans d\'excellence horlogère'
            ],
            [
                'title' => '[weekly] Savoir-faire horloger',
                'description' => 'Les métiers d\'art et techniques de fabrication'
            ],
            [
                'title' => '[discovery] Salon de Genève 2025',
                'description' => 'Présentation des nouveautés au salon international'
            ],
            [
                'title' => '[discovery] Calibres manufacturés',
                'description' => 'Découverte des mouvements internes Breitling'
            ],
            [
                'title' => '[novelties] Montres connectées',
                'description' => 'L\'avenir numérique de l\'horlogerie traditionnelle'
            ]
        ];

        foreach ($chapters as $chapterData) {
            Chapter::create($chapterData);
        }
    }
}
