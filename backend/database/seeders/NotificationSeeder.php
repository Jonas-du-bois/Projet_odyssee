<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple notifications at once
        $notifications = [
            [
                'user_id' => 2,
                'type' => 'new_quiz',
                'message' => 'A new quiz is available in the "Origins" unit',
                'read' => 0,
                'date' => '2025-01-10 08:00:00'
            ],
            [
                'user_id' => 2,
                'type' => 'discovery',
                'message' => 'A new discovery is available in the chapter "Introduction to Breitling"',
                'read' => 1,
                'date' => '2025-01-15 09:00:00'
            ],
            [
                'user_id' => 2,
                'type' => 'event',
                'message' => 'The event "Breitling Anniversary" starts tomorrow',
                'read' => 0,
                'date' => '2025-01-31 10:00:00'
            ]
        ];

        foreach ($notifications as $notificationData) {
            Notification::create($notificationData);
        }
    }
}
