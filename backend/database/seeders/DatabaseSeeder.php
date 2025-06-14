<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order (respecting dependencies)
        $this->call([
            // Independent tables first
            RankSeeder::class,
            QuizTypeSeeder::class,
            LastChanceSeeder::class,
            ChapterSeeder::class,
            
            // Tables with dependencies
            UserSeeder::class,
            UnitSeeder::class,
            QuestionSeeder::class,
            ChoiceSeeder::class,
            
            // More complex dependencies
            QuizInstanceSeeder::class, // Réactivé après migration vers structure polymorphique
            UserQuizScoreSeeder::class, // Dépend de QuizInstanceSeeder
            UserAnswerSeeder::class, // Dépend de QuizInstanceSeeder
            ScoreSeeder::class,
            ProgressSeeder::class,
            WeeklySeriesSeeder::class,
            NotificationSeeder::class,
            DiscoverySeeder::class,
            NoveltySeeder::class,
            EventSeeder::class,
            ReminderSeeder::class,
            WeeklySeeder::class,
            LotteryTicketSeeder::class,
            EventUnitSeeder::class,
        ]);
    }
}
