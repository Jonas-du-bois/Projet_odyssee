<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create comprehensive questions for all units
        $questions = [
            // Unit 1: The Origins
            [
                'unit_id' => 1,
                'statement' => 'En quelle année Breitling a-t-elle été fondée ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 1,
                'statement' => 'Qui a fondé Breitling ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 1,
                'statement' => 'Dans quelle ville suisse Breitling a-t-elle été créée ?',
                'timer_seconds' => 25,
                'type' => 'multiple_choice'
            ],
            
            // Unit 2: Brand Values
            [
                'unit_id' => 2,
                'statement' => 'Quelles sont les trois valeurs fondamentales de Breitling ?',
                'timer_seconds' => 45,
                'type' => 'multiple'
            ],
            [
                'unit_id' => 2,
                'statement' => 'Quel est le slogan principal de Breitling ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 3: Chronomat B01 42 Triumph
            [
                'unit_id' => 3,
                'statement' => 'Quel calibre équipe la Chronomat B01 42 ?',
                'timer_seconds' => 25,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 3,
                'statement' => 'Quelle est la réserve de marche du calibre B01 ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 4: Manufacture Calibers
            [
                'unit_id' => 4,
                'statement' => 'Que signifie COSC ?',
                'timer_seconds' => 40,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 4,
                'statement' => 'Quels sont les calibres manufacture de Breitling ?',
                'timer_seconds' => 45,
                'type' => 'multiple'
            ],
            
            // Unit 5: Aviation Partnerships
            [
                'unit_id' => 5,
                'statement' => 'Quel est le nom de l\'équipe de voltige de Breitling ?',
                'timer_seconds' => 25,
                'type' => 'multiple_choice'
            ],
            
            // Unit 6: Navitimer Collection
            [
                'unit_id' => 6,
                'statement' => 'En quelle année la Navitimer a-t-elle été lancée ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 6,
                'statement' => 'Quelle est la fonction distinctive de la Navitimer ?',
                'timer_seconds' => 35,
                'type' => 'multiple_choice'
            ],
            
            // Unit 7: Superocean Heritage
            [
                'unit_id' => 7,
                'statement' => 'À quelle profondeur la Superocean est-elle étanche ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 8: Smart Technology
            [
                'unit_id' => 8,
                'statement' => 'Quelles fonctionnalités connectées Breitling développe-t-elle ?',
                'timer_seconds' => 40,
                'type' => 'multiple'
            ],
            
            // Unit 9: Léon Breitling Era
            [
                'unit_id' => 9,
                'statement' => 'Quelle était la spécialité initiale de Léon Breitling ?',
                'timer_seconds' => 35,
                'type' => 'multiple_choice'
            ],
            
            // Unit 10: Chronograph Evolution
            [
                'unit_id' => 10,
                'statement' => 'En quelle année Breitling a-t-elle créé le premier chronographe-bracelet avec poussoir indépendant ?',
                'timer_seconds' => 35,
                'type' => 'multiple_choice'
            ]
        ];

        foreach ($questions as $questionData) {
            Question::create($questionData);
        }
    }
}
