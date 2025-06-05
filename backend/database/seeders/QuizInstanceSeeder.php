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
        // Create multiple quiz instances at once spanning the year 2025
        $quizInstances = [
            // Janvier 2025
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
                'launch_date' => '2025-01-03 11:45:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 4,
                'module_type' => 'Weekly',
                'module_id' => 1,
                'launch_date' => '2025-01-08 16:20:00'
            ],
            
            // Février 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 3,
                'launch_date' => '2025-02-05 10:30:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 2,
                'module_type' => 'Discovery',
                'module_id' => 2,
                'launch_date' => '2025-02-12 15:45:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 4,
                'launch_date' => '2025-02-18 09:00:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 4,
                'module_type' => 'Weekly',
                'module_id' => 2,
                'launch_date' => '2025-02-25 14:15:00'
            ],
            
            // Mars 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 3,
                'module_type' => 'Novelty',
                'module_id' => 1,
                'launch_date' => '2025-03-08 11:20:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 5,
                'launch_date' => '2025-03-15 16:30:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 2,
                'module_type' => 'Discovery',
                'module_id' => 3,
                'launch_date' => '2025-03-22 13:45:00'
            ],
            
            // Avril 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 4,
                'module_type' => 'Weekly',
                'module_id' => 3,
                'launch_date' => '2025-04-02 10:15:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 3,
                'module_type' => 'Novelty',
                'module_id' => 2,
                'launch_date' => '2025-04-14 14:20:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 6,
                'launch_date' => '2025-04-28 09:30:00'
            ],
            
            // Mai 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 2,
                'module_type' => 'Discovery',
                'module_id' => 4,
                'launch_date' => '2025-05-06 15:00:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 4,
                'module_type' => 'Weekly',
                'module_id' => 4,
                'launch_date' => '2025-05-20 11:45:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 3,
                'module_type' => 'Novelty',
                'module_id' => 3,
                'launch_date' => '2025-05-30 16:15:00'
            ],
            
            // Juin 2025 (données récentes)
            [
                'user_id' => 3,
                'quiz_type_id' => 1,
                'module_type' => 'Unit',
                'module_id' => 7,
                'launch_date' => '2025-06-01 10:00:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 2,
                'module_type' => 'Discovery',
                'module_id' => 5,
                'launch_date' => '2025-06-03 14:30:00'
            ]
        ];

        foreach ($quizInstances as $quizInstanceData) {
            QuizInstance::create($quizInstanceData);
        }
    }
}
