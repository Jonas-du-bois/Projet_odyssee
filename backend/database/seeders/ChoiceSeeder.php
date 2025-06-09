<?php

namespace Database\Seeders;

use App\Models\Choice;
use Illuminate\Database\Seeder;

class ChoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create comprehensive choices for all questions
        $choices = [
            // Question 1: En quelle année Breitling a-t-elle été fondée ?
            ['question_id' => 1, 'text' => '1884', 'is_correct' => 1],
            ['question_id' => 1, 'text' => '1856', 'is_correct' => 0],
            ['question_id' => 1, 'text' => '1901', 'is_correct' => 0],
            ['question_id' => 1, 'text' => '1923', 'is_correct' => 0],
            
            // Question 2: Qui a fondé Breitling ?
            ['question_id' => 2, 'text' => 'Léon Breitling', 'is_correct' => 1],
            ['question_id' => 2, 'text' => 'Georges Breitling', 'is_correct' => 0],
            ['question_id' => 2, 'text' => 'Gaston Breitling', 'is_correct' => 0],
            ['question_id' => 2, 'text' => 'Louis Breitling', 'is_correct' => 0],
            
            // Question 3: Dans quelle ville suisse Breitling a-t-elle été créée ?
            ['question_id' => 3, 'text' => 'Saint-Imier', 'is_correct' => 1],
            ['question_id' => 3, 'text' => 'Genève', 'is_correct' => 0],
            ['question_id' => 3, 'text' => 'Bienne', 'is_correct' => 0],
            ['question_id' => 3, 'text' => 'La Chaux-de-Fonds', 'is_correct' => 0],
            
            // Question 4: Quelles sont les trois valeurs fondamentales de Breitling ? (multiple)
            ['question_id' => 4, 'text' => 'Précision', 'is_correct' => 1],
            ['question_id' => 4, 'text' => 'Performance', 'is_correct' => 1],
            ['question_id' => 4, 'text' => 'Innovation', 'is_correct' => 1],
            ['question_id' => 4, 'text' => 'Minimalisme', 'is_correct' => 0],
            ['question_id' => 4, 'text' => 'Tradition', 'is_correct' => 0],
            
            // Question 5: Quel est le slogan principal de Breitling ?
            ['question_id' => 5, 'text' => 'Instruments for Professionals', 'is_correct' => 1],
            ['question_id' => 5, 'text' => 'A Cut Above', 'is_correct' => 0],
            ['question_id' => 5, 'text' => 'Swiss Made Excellence', 'is_correct' => 0],
            ['question_id' => 5, 'text' => 'Time for Adventure', 'is_correct' => 0],
            
            // Question 6: Quel calibre équipe la Chronomat B01 42 ?
            ['question_id' => 6, 'text' => 'Calibre B01', 'is_correct' => 1],
            ['question_id' => 6, 'text' => 'Calibre B02', 'is_correct' => 0],
            ['question_id' => 6, 'text' => 'Calibre B04', 'is_correct' => 0],
            ['question_id' => 6, 'text' => 'Calibre B13', 'is_correct' => 0],
            
            // Question 7: Quelle est la réserve de marche du calibre B01 ?
            ['question_id' => 7, 'text' => '70 heures', 'is_correct' => 1],
            ['question_id' => 7, 'text' => '48 heures', 'is_correct' => 0],
            ['question_id' => 7, 'text' => '72 heures', 'is_correct' => 0],
            ['question_id' => 7, 'text' => '42 heures', 'is_correct' => 0],
            
            // Question 8: Que signifie COSC ?
            ['question_id' => 8, 'text' => 'Contrôle Officiel Suisse des Chronomètres', 'is_correct' => 1],
            ['question_id' => 8, 'text' => 'Centre Officiel Suisse de Certification', 'is_correct' => 0],
            ['question_id' => 8, 'text' => 'Comité Officiel des Standards Chronométriques', 'is_correct' => 0],
            ['question_id' => 8, 'text' => 'Commission Officielle Suisse des Calibres', 'is_correct' => 0],
            
            // Question 9: Quels sont les calibres manufacture de Breitling ? (multiple)
            ['question_id' => 9, 'text' => 'B01', 'is_correct' => 1],
            ['question_id' => 9, 'text' => 'B02', 'is_correct' => 1],
            ['question_id' => 9, 'text' => 'B04', 'is_correct' => 1],
            ['question_id' => 9, 'text' => 'B13', 'is_correct' => 0],
            ['question_id' => 9, 'text' => 'B25', 'is_correct' => 0],
            
            // Question 10: Quel est le nom de l\'équipe de voltige de Breitling ?
            ['question_id' => 10, 'text' => 'Breitling Jet Team', 'is_correct' => 1],
            ['question_id' => 10, 'text' => 'Breitling Air Force', 'is_correct' => 0],
            ['question_id' => 10, 'text' => 'Breitling Aerobatic Team', 'is_correct' => 0],
            ['question_id' => 10, 'text' => 'Breitling Sky Squadron', 'is_correct' => 0],
            
            // Question 11: En quelle année la Navitimer a-t-elle été lancée ?
            ['question_id' => 11, 'text' => '1952', 'is_correct' => 1],
            ['question_id' => 11, 'text' => '1948', 'is_correct' => 0],
            ['question_id' => 11, 'text' => '1956', 'is_correct' => 0],
            ['question_id' => 11, 'text' => '1961', 'is_correct' => 0],
            
            // Question 12: Quelle est la fonction distinctive de la Navitimer ?
            ['question_id' => 12, 'text' => 'Règle à calcul circulaire', 'is_correct' => 1],
            ['question_id' => 12, 'text' => 'Altimètre intégré', 'is_correct' => 0],
            ['question_id' => 12, 'text' => 'Boussole digitale', 'is_correct' => 0],
            ['question_id' => 12, 'text' => 'GPS aviation', 'is_correct' => 0],
            
            // Question 13: À quelle profondeur la Superocean est-elle étanche ?
            ['question_id' => 13, 'text' => '2000 mètres', 'is_correct' => 1],
            ['question_id' => 13, 'text' => '1000 mètres', 'is_correct' => 0],
            ['question_id' => 13, 'text' => '500 mètres', 'is_correct' => 0],
            ['question_id' => 13, 'text' => '300 mètres', 'is_correct' => 0],
            
            // Question 14: Quelles fonctionnalités connectées Breitling développe-t-elle ? (multiple)
            ['question_id' => 14, 'text' => 'Suivi d\'activité', 'is_correct' => 1],
            ['question_id' => 14, 'text' => 'Notifications sélectives', 'is_correct' => 1],
            ['question_id' => 14, 'text' => 'Géolocalisation', 'is_correct' => 1],
            ['question_id' => 14, 'text' => 'Réseaux sociaux', 'is_correct' => 0],
            ['question_id' => 14, 'text' => 'Jeux mobiles', 'is_correct' => 0],
            
            // Question 15: Quelle était la spécialité initiale de Léon Breitling ?
            ['question_id' => 15, 'text' => 'Chronographes et compteurs de sport', 'is_correct' => 1],
            ['question_id' => 15, 'text' => 'Montres de poche simples', 'is_correct' => 0],
            ['question_id' => 15, 'text' => 'Horloges d\'église', 'is_correct' => 0],
            ['question_id' => 15, 'text' => 'Réveils de voyage', 'is_correct' => 0],
            
            // Question 16: En quelle année Breitling a-t-elle créé le premier chronographe-bracelet avec poussoir indépendant ?
            ['question_id' => 16, 'text' => '1915', 'is_correct' => 1],
            ['question_id' => 16, 'text' => '1910', 'is_correct' => 0],
            ['question_id' => 16, 'text' => '1920', 'is_correct' => 0],
            ['question_id' => 16, 'text' => '1925', 'is_correct' => 0]
        ];

        foreach ($choices as $choiceData) {
            Choice::create($choiceData);
        }
    }
}
