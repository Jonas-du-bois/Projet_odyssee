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
        // Create multiple choices at once
        $choices = [
            // Choices for question 1: "In what year was Breitling founded?"
            ['question_id' => 1, 'text' => '1884', 'is_correct' => 1],
            ['question_id' => 1, 'text' => '1856', 'is_correct' => 0],
            ['question_id' => 1, 'text' => '1901', 'is_correct' => 0],
            ['question_id' => 1, 'text' => '1923', 'is_correct' => 0],
            
            // Choices for question 2: "Who founded Breitling?"
            ['question_id' => 2, 'text' => 'Léon Breitling', 'is_correct' => 1],
            ['question_id' => 2, 'text' => 'Georges Breitling', 'is_correct' => 0],
            ['question_id' => 2, 'text' => 'Gaston Breitling', 'is_correct' => 0],
            ['question_id' => 2, 'text' => 'Louis Breitling', 'is_correct' => 0],
            
            // Choices for question 3: "What are the three core values of Breitling?"
            ['question_id' => 3, 'text' => 'Precision', 'is_correct' => 1],
            ['question_id' => 3, 'text' => 'Performance', 'is_correct' => 1],
            ['question_id' => 3, 'text' => 'Innovation', 'is_correct' => 1],
            ['question_id' => 3, 'text' => 'Minimalism', 'is_correct' => 0],
            
        ];

        foreach ($choices as $choiceData) {
            Choice::create($choiceData);
        }
    }
}
