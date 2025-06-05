<?php

namespace Database\Seeders;

use App\Models\Reminder;
use Illuminate\Database\Seeder;

class ReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple reminders at once
        $reminders = [
            [
                'chapter_id' => 1,
                'number_questions' => 5,
                'deadline_date' => '2025-01-31'
            ],
            [
                'chapter_id' => 2,
                'number_questions' => 3,
                'deadline_date' => '2025-02-28'
            ],
            [
                'chapter_id' => 3,
                'number_questions' => 4,
                'deadline_date' => '2025-03-31'
            ]
        ];

        foreach ($reminders as $reminderData) {
            Reminder::create($reminderData);
        }
    }
}
