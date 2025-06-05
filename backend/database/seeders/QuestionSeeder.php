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
        // Create multiple questions at once
        $questions = [
            [
                'unit_id' => 1,
                'statement' => 'In what year was Breitling founded?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 1,
                'statement' => 'Who founded Breitling?',
                'timer_seconds' => 30,
                'type' => 'multiple_choice'
            ],
            [
                'unit_id' => 2,
                'statement' => 'What are the three core values of Breitling?',
                'timer_seconds' => 45,
                'type' => 'multiple'
            ]
        ];

        foreach ($questions as $questionData) {
            Question::create($questionData);
        }
    }
}
