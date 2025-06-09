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
        
        // Create comprehensive chapters with theory content
        $chapters = [
            [
                'title' => '[discovery] Breitling et l\'aviation',
                'description' => 'L\'héritage aéronautique de la marque',
                'theory_content' => '<h2>L\'Aviation dans l\'ADN de Breitling</h2><p>Depuis sa fondation en 1884, Breitling entretient des liens privilégiés avec l\'aviation. La marque s\'est spécialisée dans les instruments de chronométrage pour pilotes et a développé des montres spécifiquement conçues pour les professionnels de l\'aéronautique.</p><p>Parmi les collaborations marquantes : le Breitling Jet Team, les partenariats avec les forces aériennes et le développement de chronographes de bord pour cockpits d\'avions.</p>',
                'is_active' => true
            ],
            [
                'title' => '[discovery] Collections emblématiques',
                'description' => 'Navitimer, Superocean et autres collections phares',
                'theory_content' => '<h2>Les Collections Iconiques</h2><p><strong>Navitimer :</strong> Lancée en 1952, cette collection incarne l\'essence de Breitling avec sa règle à calcul circulaire permettant aux pilotes d\'effectuer tous les calculs liés au vol.</p><p><strong>Superocean :</strong> Dédiée à la plongée professionnelle, cette collection offre une étanchéité exceptionnelle et une lisibilité parfaite sous l\'eau.</p><p><strong>Chronomat :</strong> Le chronographe sport-chic par excellence, alliant performance et élégance.</p>',
                'is_active' => true
            ],
            [
                'title' => '[novelties] Innovations technologiques',
                'description' => 'Les dernières avancées horlogères de Breitling',
                'theory_content' => '<h2>Innovation et Technologie</h2><p>Breitling ne cesse d\'innover avec des technologies de pointe :</p><ul><li>Calibres manufacture B01, B02, B04</li><li>Matériaux innovants : Breitlight®, titane, or rouge</li><li>Certification COSC pour tous les mouvements</li><li>Intégration de fonctions connectées sans compromettre l\'esthétique traditionnelle</li></ul>',
                'is_active' => true
            ],
            [
                'title' => '[Reminder] Histoire de Breitling',
                'description' => 'De 1884 à aujourd\'hui, 140 ans d\'excellence horlogère',
                'theory_content' => '<h2>140 Ans d\'Histoire</h2><p><strong>1884 :</strong> Léon Breitling fonde l\'entreprise à Saint-Imier</p><p><strong>1915 :</strong> Premier chronographe-bracelet avec poussoir indépendant</p><p><strong>1969 :</strong> Lancement du Calibre 01, premier mouvement chronographe automatique</p><p><strong>2017 :</strong> Acquisition par CVC Capital Partners, nouvelle ère sous la direction de Georges Kern</p>',
                'is_active' => true
            ],
            [
                'title' => '[weekly] Savoir-faire horloger',
                'description' => 'Les métiers d\'art et techniques de fabrication',
                'theory_content' => '<h2>L\'Art de l\'Horlogerie</h2><p>Le savoir-faire Breitling repose sur :</p><ul><li>Maîtrise des complications horlogères</li><li>Finitions soignées des calibres</li><li>Assemblage manuel précis</li><li>Contrôles qualité rigoureux</li><li>Formation continue des horlogers</li></ul><p>Chaque montre Breitling bénéficie d\'un savoir-faire transmis de génération en génération.</p>',
                'is_active' => true
            ],
            [
                'title' => '[discovery] Salon de Genève 2025',
                'description' => 'Présentation des nouveautés au salon international',
                'theory_content' => '<h2>Watches & Wonders 2025</h2><p>Breitling présente ses dernières créations au plus prestigieux salon horloger mondial. Cette année, focus sur :</p><ul><li>Nouvelles déclinaisons de la Navitimer</li><li>Superocean Heritage II</li><li>Innovations en matière de durabilité</li><li>Collaborations exclusives</li></ul>',
                'is_active' => true
            ],
            [
                'title' => '[discovery] Calibres manufacturés',
                'description' => 'Découverte des mouvements internes Breitling',
                'theory_content' => '<h2>Les Calibres Manufacture</h2><p><strong>Calibre B01 :</strong> Chronographe manufacture avec 70h de réserve de marche</p><p><strong>Calibre B02 :</strong> Version GMT du B01</p><p><strong>Calibre B04 :</strong> Chronographe GMT avec fonction UTC</p><p>Tous certifiés COSC, ces mouvements représentent l\'excellence technique de Breitling.</p>',
                'is_active' => true
            ],
            [
                'title' => '[novelties] Montres connectées',
                'description' => 'L\'avenir numérique de l\'horlogerie traditionnelle',
                'theory_content' => '<h2>Horlogerie Connectée</h2><p>Breitling explore l\'avenir avec des fonctionnalités connectées intelligentes :</p><ul><li>Suivi d\'activité discret</li><li>Notifications sélectives</li><li>Géolocalisation pour pilotes</li><li>Préservation de l\'esthétique classique</li></ul><p>L\'objectif : enrichir l\'expérience sans dénaturer l\'horlogerie traditionnelle.</p>',
                'is_active' => true
            ]
        ];

        foreach ($chapters as $chapterData) {
            Chapter::create($chapterData);
        }
    }
}
