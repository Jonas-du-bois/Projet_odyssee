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
                'quiz_type_id' => 1, // Discovery Quiz (nouveau ID 1)
                'quizable_type' => 'discovery',
                'quizable_id' => 1,
                'launch_date' => '2025-01-10 10:00:00'
            ],
            [
                'user_id' => 2,
                'quiz_type_id' => 1, // Discovery Quiz
                'quizable_type' => 'discovery',
                'quizable_id' => 1,
                'launch_date' => '2025-01-16 14:30:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 5, // Novelty Quiz (nouveau ID 5)
                'quizable_type' => 'novelty',
                'quizable_id' => 1,
                'launch_date' => '2025-01-12 09:15:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 2, // Event Quiz (nouveau ID 2)
                'quizable_type' => 'event',
                'quizable_id' => 1,
                'launch_date' => '2025-01-03 11:45:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 3, // Weekly Quiz (nouveau ID 3)
                'quizable_type' => 'weekly',
                'quizable_id' => 1,
                'launch_date' => '2025-01-08 16:20:00'
            ],
            
            // Février 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 1, // Discovery Quiz
                'quizable_type' => 'discovery',
                'quizable_id' => 2,
                'launch_date' => '2025-02-05 10:30:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 1, // Discovery Quiz
                'quizable_type' => 'discovery',
                'quizable_id' => 2,
                'launch_date' => '2025-02-12 15:45:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 5, // Novelty Quiz
                'quizable_type' => 'novelty',
                'quizable_id' => 1,
                'launch_date' => '2025-02-18 09:00:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 3, // Weekly Quiz
                'quizable_type' => 'weekly',
                'quizable_id' => 2,
                'launch_date' => '2025-02-25 14:15:00'
            ],
            
            // Mars 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 5, // Novelty Quiz
                'quizable_type' => 'novelty',
                'quizable_id' => 1,
                'launch_date' => '2025-03-08 11:20:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 1, // Discovery Quiz
                'quizable_type' => 'discovery',
                'quizable_id' => 3,
                'launch_date' => '2025-03-15 16:30:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 1, // Discovery Quiz
                'quizable_type' => 'discovery',
                'quizable_id' => 3,
                'launch_date' => '2025-03-22 13:45:00'
            ],
            
            // Avril 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 3, // Weekly Quiz (nouveau ID 3)
                'quizable_type' => 'weekly',
                'quizable_id' => 3,
                'launch_date' => '2025-04-02 10:15:00'
            ],
            [
                'user_id' => 3,
                'quiz_type_id' => 5, // Novelty Quiz (nouveau ID 5)
                'quizable_type' => 'novelty',
                'quizable_id' => 2,
                'launch_date' => '2025-04-14 14:20:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 4, // Unit Quiz (nouveau ID 4)
                'quizable_type' => 'unit',
                'quizable_id' => 6,
                'launch_date' => '2025-04-28 09:30:00'
            ],
            
            // Mai 2025
            [
                'user_id' => 2,
                'quiz_type_id' => 1, // Discovery Quiz (nouveau ID 1)
                'quizable_type' => 'discovery',
                'quizable_id' => 4,
                'launch_date' => '2025-05-06 15:00:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 3, // Weekly Quiz
                'quizable_type' => 'weekly',
                'quizable_id' => 4,
                'launch_date' => '2025-05-20 11:45:00'
            ],
            [
                'user_id' => 5,
                'quiz_type_id' => 5, // Novelty Quiz
                'quizable_type' => 'novelty',
                'quizable_id' => 3,
                'launch_date' => '2025-05-30 16:15:00'
            ],
            
            // Juin 2025 (données récentes)
            [
                'user_id' => 3,
                'quiz_type_id' => 4, // Unit Quiz
                'quizable_type' => 'unit',
                'quizable_id' => 7,
                'launch_date' => '2025-06-01 10:00:00'
            ],
            [
                'user_id' => 4,
                'quiz_type_id' => 1, // Discovery Quiz
                'quizable_type' => 'discovery',
                'quizable_id' => 5,
                'launch_date' => '2025-06-03 14:30:00'
            ]
        ];

        foreach ($quizInstances as $quizInstanceData) {
            QuizInstance::create($quizInstanceData);
        }
    }
}
