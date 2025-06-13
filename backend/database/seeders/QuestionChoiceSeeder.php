<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Support\Facades\DB;

class QuestionChoiceSeeder extends Seeder
{    public function run(): void
    {
        // Optimisé pour PostgreSQL (Heroku)
        echo "🔄 Nettoyage des tables...\n";
        
        // Heroku PostgreSQL ne permet pas session_replication_role
        // On supprime simplement les enregistrements existants
        echo "📊 Base PostgreSQL Heroku détectée\n";
        
        try {
            Choice::query()->delete();
            Question::query()->delete();
            
            // Reset auto-increment si possible (optionnel sur Heroku)
            try {
                DB::statement("SELECT setval(pg_get_serial_sequence('questions', 'id'), 1, false);");
                DB::statement("SELECT setval(pg_get_serial_sequence('choices', 'id'), 1, false);");
            } catch (\Exception $e) {
                echo "⚠️ Reset sequence ignoré: " . $e->getMessage() . "\n";
            }
        } catch (\Exception $e) {
            echo "⚠️ Erreur de nettoyage: " . $e->getMessage() . "\n";
        }
        
        echo "✅ Tables nettoyées, création des questions...\n";
        $this->createQuestionsWithChoices();
    }

    private function createQuestionsWithChoices(): void
    {
        $questionsData = [
            // Unité 1: Histoire de l'horlogerie
            [
                'unit_id' => 1,
                'question_text' => 'En quelle année Léon Breitling a-t-il fondé la marque Breitling ?',
                'choices' => [
                    ['text' => '1884', 'is_correct' => true],
                    ['text' => '1890', 'is_correct' => false],
                    ['text' => '1876', 'is_correct' => false],
                    ['text' => '1892', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 1,
                'question_text' => 'Quel était le premier produit fabriqué par Breitling ?',
                'choices' => [
                    ['text' => 'Chronographes', 'is_correct' => true],
                    ['text' => 'Montres de poche', 'is_correct' => false],
                    ['text' => 'Réveils', 'is_correct' => false],
                    ['text' => 'Pendules', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 1,
                'question_text' => 'Dans quelle ville Breitling a-t-il été fondé ?',
                'choices' => [
                    ['text' => 'Saint-Imier', 'is_correct' => true],
                    ['text' => 'Genève', 'is_correct' => false],
                    ['text' => 'Bienne', 'is_correct' => false],
                    ['text' => 'La Chaux-de-Fonds', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 1,
                'question_text' => 'Quelle innovation Breitling a-t-il apportée aux chronographes ?',
                'choices' => [
                    ['text' => 'Le poussoir indépendant', 'is_correct' => true],
                    ['text' => 'Le bracelet métallique', 'is_correct' => false],
                    ['text' => 'Le verre saphir', 'is_correct' => false],
                    ['text' => 'La couronne vissée', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 1,
                'question_text' => 'Quel secteur a particulièrement adopté les chronographes Breitling au début du 20ème siècle ?',
                'choices' => [
                    ['text' => 'L\'aviation', 'is_correct' => true],
                    ['text' => 'L\'automobile', 'is_correct' => false],
                    ['text' => 'Le sport', 'is_correct' => false],
                    ['text' => 'L\'industrie', 'is_correct' => false],
                ]
            ],

            // Unité 2: Collections emblématiques
            [
                'unit_id' => 2,
                'question_text' => 'Quelle est la collection Breitling la plus emblématique pour l\'aviation ?',
                'choices' => [
                    ['text' => 'Navitimer', 'is_correct' => true],
                    ['text' => 'Superocean', 'is_correct' => false],
                    ['text' => 'Premier', 'is_correct' => false],
                    ['text' => 'Chronomat', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 2,
                'question_text' => 'En quelle année la Navitimer a-t-elle été lancée ?',
                'choices' => [
                    ['text' => '1952', 'is_correct' => true],
                    ['text' => '1948', 'is_correct' => false],
                    ['text' => '1955', 'is_correct' => false],
                    ['text' => '1960', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 2,
                'question_text' => 'Quelle collection Breitling est dédiée à la plongée ?',
                'choices' => [
                    ['text' => 'Superocean', 'is_correct' => true],
                    ['text' => 'Navitimer', 'is_correct' => false],
                    ['text' => 'Premier', 'is_correct' => false],
                    ['text' => 'Avenger', 'is_correct' => false],
                ]
            ],

            // Unité 3: Innovations techniques
            [
                'unit_id' => 3,
                'question_text' => 'Qu\'est-ce qui rend unique la lunette de la Navitimer ?',
                'choices' => [
                    ['text' => 'C\'est une règle à calcul circulaire', 'is_correct' => true],
                    ['text' => 'Elle est en céramique', 'is_correct' => false],
                    ['text' => 'Elle est unidirectionnelle', 'is_correct' => false],
                    ['text' => 'Elle a des index lumineux', 'is_correct' => false],
                ]
            ],
            [
                'unit_id' => 3,
                'question_text' => 'Quel mouvement Breitling a développé en collaboration avec Tudor, TAG Heuer et Hamilton ?',
                'choices' => [
                    ['text' => 'Calibre 11', 'is_correct' => true],
                    ['text' => 'Calibre B01', 'is_correct' => false],
                    ['text' => 'Calibre B09', 'is_correct' => false],
                    ['text' => 'Calibre B20', 'is_correct' => false],
                ]
            ],
        ];

        foreach ($questionsData as $questionData) {
            echo "📝 Création question pour unité {$questionData['unit_id']}...\n";
            
            // Créer la question avec le système polymorphique
            $question = Question::create([
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => $questionData['unit_id'],
                'question_text' => $questionData['question_text'],
                'type' => 'multiple_choice',
                'timer_seconds' => 30,
            ]);

            // Créer les choix
            foreach ($questionData['choices'] as $choiceData) {
                Choice::create([
                    'question_id' => $question->id,
                    'text' => $choiceData['text'],
                    'is_correct' => $choiceData['is_correct'],
                ]);
            }
            
            echo "   ✅ Question {$question->id} créée avec " . count($questionData['choices']) . " choix\n";
        }
        
        echo "🎉 Seeder terminé ! " . count($questionsData) . " questions créées avec leurs choix.\n";
    }
}
