<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Choice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeederNew extends Seeder
{    /**
     * Run the database seeds.
     */
    public function run(): void
    {        // Désactiver les contraintes de clé étrangère temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Vider d'abord les questions et choix existants pour éviter les doublons
        Choice::truncate();
        Question::truncate();
        
        // Réactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Définir les questions avec leurs choix
        $questionsData = [
            // Unit 1: The Origins (Chapter 1)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'En quelle année Breitling a-t-elle été fondée ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => '1884', 'is_correct' => true],
                    ['text' => '1890', 'is_correct' => false],
                    ['text' => '1900', 'is_correct' => false],
                    ['text' => '1920', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Qui a fondé Breitling ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Léon Breitling', 'is_correct' => true],
                    ['text' => 'Georges Breitling', 'is_correct' => false],
                    ['text' => 'Henri Breitling', 'is_correct' => false],
                    ['text' => 'Paul Breitling', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Dans quelle ville suisse Breitling a-t-elle été créée ?',
                'timer_seconds' => 25,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Saint-Imier', 'is_correct' => true],
                    ['text' => 'Genève', 'is_correct' => false],
                    ['text' => 'Bienne', 'is_correct' => false],
                    ['text' => 'La Chaux-de-Fonds', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Quelle était la spécialité initiale de Léon Breitling ?',
                'timer_seconds' => 35,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Chronographes et compteurs', 'is_correct' => true],
                    ['text' => 'Montres', 'is_correct' => false],
                    ['text' => 'Horloges publiques', 'is_correct' => false],
                    ['text' => 'Bijouterie', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Quel secteur a toujours été privilégié par Breitling ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Aviation', 'is_correct' => true],
                    ['text' => 'Marine', 'is_correct' => false],
                    ['text' => 'Automobile', 'is_correct' => false],
                    ['text' => 'Ferroviaire', 'is_correct' => false]
                ]
            ],
            
            // Unit 2: Brand Values (Chapter 1)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 2,
                'question_text' => 'Quel est le slogan principal de Breitling ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Instruments for Professionals', 'is_correct' => true],
                    ['text' => 'Time for Excellence', 'is_correct' => false],
                    ['text' => 'Swiss Made Excellence', 'is_correct' => false],
                    ['text' => 'Precision Above All', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 2,
                'question_text' => 'Quelles sont les trois valeurs fondamentales de Breitling ?',
                'timer_seconds' => 45,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Précision, Innovation, Excellence', 'is_correct' => true],
                    ['text' => 'Tradition, Modernité, Performance', 'is_correct' => false],
                    ['text' => 'Aviation, Marine, Exploration', 'is_correct' => false],
                    ['text' => 'Luxe, Sport, Aventure', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 2,
                'question_text' => 'Quelle certification garantit la précision des montres Breitling ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'COSC', 'is_correct' => true],
                    ['text' => 'ISO 9001', 'is_correct' => false],
                    ['text' => 'METAS', 'is_correct' => false],
                    ['text' => 'Patek Philippe Seal', 'is_correct' => false]
                ]
            ],
            
            // Unit 3: Chronomat B01 42 Triumph (Chapter 2)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 3,
                'question_text' => 'Quel calibre équipe la Chronomat B01 42 ?',
                'timer_seconds' => 25,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => 'Calibre B01', 'is_correct' => true],
                    ['text' => 'Calibre B20', 'is_correct' => false],
                    ['text' => 'Calibre B25', 'is_correct' => false],
                    ['text' => 'Calibre B04', 'is_correct' => false]
                ]
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 3,
                'question_text' => 'Quelle est la réserve de marche du calibre B01 ?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice',
                'choices' => [
                    ['text' => '70h', 'is_correct' => true],
                    ['text' => '48h', 'is_correct' => false],
                    ['text' => '60h', 'is_correct' => false],
                    ['text' => '80h', 'is_correct' => false]
                ]
            ]
        ];
        
        // Créer les questions et leurs choix
        foreach ($questionsData as $questionData) {
            $choices = $questionData['choices'];
            unset($questionData['choices']);
            
            $question = Question::create($questionData);
            
            foreach ($choices as $choiceData) {
                Choice::create([
                    'question_id' => $question->id,
                    'text' => $choiceData['text'],
                    'is_correct' => $choiceData['is_correct']
                ]);
            }
        }
    }
}
