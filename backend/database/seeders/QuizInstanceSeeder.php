<?php

namespace Database\Seeders;

use App\Models\QuizInstance;
use Illuminate\Database\Seeder;

class QuizInstanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple quiz instances at once
        $quizInstances = [
            [
                'user_id' => 2,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 1,
                'launch_date' => '2025-01-10 10:00:00'
            ],
            [
                'user_id' => 2,
                'quiz_type_id' => 2,
                'module_type' => 'Discovery',
                'module_id' => 1,
                'launch_date' => '2025-01-16 14:30:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 2,
                'launch_date' => '2025-01-12 09:15:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 3,
                'module_type' => 'Event',
                'module_id' => 1,
                'launch_date' => '2025-02-03 11:45:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 4,
                'module_type' => 'Weekly',
                'module_id' => 1,
                'launch_date' => '2025-01-08 16:20:00'
            ]
        ];

        foreach ($quizInstances as $quizInstanceData) {
            QuizInstance::create($quizInstanceData);
        }
    }
}
