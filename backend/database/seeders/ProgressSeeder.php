<?php

namespace Database\Seeders;

use App\Models\Progress;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multiple progress records at once
        $progressRecords = [
            [
                'user_id' => 2,
                'chapter_id' => 1,
                'unit_id' => 1,
                'percentage' => 100.0,
                'completed' => 1
            ],
            [
                'user_id' => 2,
                'chapter_id' => 1,
                'unit_id' => 2,
                'percentage' => 50.0,
                'completed' => 0
            ],
            [
                'user_id' => 3,
                'chapter_id' => 1,
                'unit_id' => 1,
                'percentage' => 100.0,
                'completed' => 1
            ]
        ];

        foreach ($progressRecords as $progressData) {
            Progress::create($progressData);
        }
    }
}
