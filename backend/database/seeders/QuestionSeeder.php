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
        // Create comprehensive questions for all units using polymorphic architecture
        $questions = [
            // Unit 1: The Origins (Chapter 1)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'En quelle année Breitling a-t-elle été fondée ?',
                'options' => json_encode(['1884', '1890', '1900', '1920']),
                'correct_answer' => '1884',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Qui a fondé Breitling ?',
                'options' => json_encode(['Léon Breitling', 'Georges Breitling', 'Henri Breitling', 'Paul Breitling']),
                'correct_answer' => 'Léon Breitling',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Dans quelle ville suisse Breitling a-t-elle été créée ?',
                'options' => json_encode(['Genève', 'Bienne', 'Saint-Imier', 'La Chaux-de-Fonds']),
                'correct_answer' => 'Saint-Imier',
                'timer_seconds' => 25,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Quelle était la spécialité initiale de Léon Breitling ?',
                'options' => json_encode(['Montres', 'Chronographes et compteurs', 'Horloges publiques', 'Bijouterie']),
                'correct_answer' => 'Chronographes et compteurs',
                'timer_seconds' => 35,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 1,
                'question_text' => 'Quel secteur a toujours été privilégié par Breitling ?',
                'options' => json_encode(['Marine', 'Aviation', 'Automobile', 'Ferroviaire']),
                'correct_answer' => 'Aviation',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 2: Brand Values (Chapter 1)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 2,
                'question_text' => 'Quel est le slogan principal de Breitling ?',
                'options' => json_encode(['Time for Excellence', 'Instruments for Professionals', 'Swiss Made Excellence', 'Precision Above All']),
                'correct_answer' => 'Instruments for Professionals',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 2,
                'question_text' => 'Quelles sont les trois valeurs fondamentales de Breitling ?',
                'options' => json_encode(['Précision, Innovation, Excellence', 'Tradition, Modernité, Performance', 'Aviation, Marine, Exploration', 'Luxe, Sport, Aventure']),
                'correct_answer' => 'Précision, Innovation, Excellence',
                'timer_seconds' => 45,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 2,
                'question_text' => 'Quelle certification garantit la précision des montres Breitling ?',
                'options' => json_encode(['ISO 9001', 'COSC', 'METAS', 'Patek Philippe Seal']),
                'correct_answer' => 'COSC',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 3: Chronomat B01 42 Triumph (Chapter 2)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 3,
                'question_text' => 'Quel calibre équipe la Chronomat B01 42 ?',
                'options' => json_encode(['Calibre B01', 'Calibre B20', 'Calibre B25', 'Calibre B04']),
                'correct_answer' => 'Calibre B01',
                'timer_seconds' => 25,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 3,
                'question_text' => 'Quelle est la réserve de marche du calibre B01 ?',
                'options' => json_encode(['48h', '60h', '70h', '80h']),
                'correct_answer' => '70h',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 3,
                'question_text' => 'Quel partenariat récent a inspiré cette collection ?',
                'options' => json_encode(['Ferrari', 'Bentley', 'Triumph Motorcycles', 'Norton']),
                'correct_answer' => 'Triumph Motorcycles',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 4: Manufacture Calibers (Chapter 3)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 4,
                'question_text' => 'Que signifie COSC ?',
                'options' => json_encode(['Contrôle Officiel Suisse des Chronomètres', 'Centre Officiel de Surveillance Chronométrique', 'Certification Officielle Suisse de Chronométrie', 'Comité Officiel Suisse des Calibres']),
                'correct_answer' => 'Contrôle Officiel Suisse des Chronomètres',
                'timer_seconds' => 40,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 4,
                'question_text' => 'Quels sont les principaux calibres manufacture de Breitling ?',
                'options' => json_encode(['B01, B02, B04', 'B20, B25, B30', 'B50, B55, B60', 'B10, B15, B25']),
                'correct_answer' => 'B01, B02, B04',
                'timer_seconds' => 45,
                'type' => 'multiple_choice'
            ],
            
            // Unit 5: Aviation Partnerships (Chapter 1)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 5,
                'question_text' => 'Quel est le nom de l\'équipe de voltige de Breitling ?',
                'options' => json_encode(['Breitling Air Team', 'Breitling Jet Team', 'Breitling Sky Team', 'Breitling Flight Team']),
                'correct_answer' => 'Breitling Jet Team',
                'timer_seconds' => 25,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 5,
                'question_text' => 'Quel astronaute célèbre a porté une Breitling dans l\'espace ?',
                'options' => json_encode(['Neil Armstrong', 'Scott Carpenter', 'Buzz Aldrin', 'John Glenn']),
                'correct_answer' => 'Scott Carpenter',
                'timer_seconds' => 35,
                'type' => 'multiple_choice'
            ],
            
            // Unit 6: Navitimer Collection (Chapter 2)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 6,
                'question_text' => 'En quelle année la Navitimer a-t-elle été lancée ?',
                'options' => json_encode(['1950', '1952', '1955', '1958']),
                'correct_answer' => '1952',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 6,
                'question_text' => 'Quelle est la fonction distinctive de la Navitimer ?',
                'options' => json_encode(['Altimètre', 'Règle à calcul circulaire', 'Boussole', 'GPS intégré']),
                'correct_answer' => 'Règle à calcul circulaire',
                'timer_seconds' => 35,
                'type' => 'multiple_choice'
            ],
            
            // Unit 7: Superocean Heritage (Chapter 2)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 7,
                'question_text' => 'À quelle profondeur maximale la Superocean est-elle étanche ?',
                'options' => json_encode(['500m', '1000m', '1500m', '2000m']),
                'correct_answer' => '2000m',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 7,
                'question_text' => 'Quel matériau spécial caractérise certains modèles Superocean ?',
                'options' => json_encode(['Titane', 'Breitlight', 'Carbone forgé', 'Acier 904L']),
                'correct_answer' => 'Breitlight',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            
            // Unit 8: Smart Technology (Chapter 3)
            [
                'quizable_type' => 'App\Models\Unit',
                'quizable_id' => 8,
                'question_text' => 'Quelle innovation technologique Breitling a-t-elle récemment intégrée ?',
                'options' => json_encode(['Écran OLED', 'Bluetooth', 'QR Code d\'authenticité', 'GPS']),
                'correct_answer' => 'QR Code d\'authenticité',
                'timer_seconds' => 40,
                'type' => 'multiple_choice'
            ]
        ];

        foreach ($questions as $questionData) {
            Question::create($questionData);
        }
    }
}
