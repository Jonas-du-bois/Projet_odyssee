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
                'title' => '[Standard] Breitling et l\'aviation',
                'description' => 'L\'héritage aéronautique de la marque'
            ],
            [
                'title' => '[Discovery] Collections emblématiques',
                'description' => 'Navitimer, Superocean et autres collections phares'
            ],
            [
                'title' => '[Novelty] Innovations technologiques',
                'description' => 'Les dernières avancées horlogères de Breitling'
            ]
        ];

        foreach ($chapters as $chapterData) {
            Chapter::create($chapterData);
        }
    }
}
