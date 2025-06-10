<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SqliteToPostgresqlMigrationSeeder extends Seeder
{
    /**
     * Migrer les données de SQLite vers PostgreSQL
     * Ce seeder doit être exécuté après la migration vers PostgreSQL
     */
    public function run(): void
    {
        Log::info('Début de la migration des données SQLite vers PostgreSQL');

        // Vérifier si nous avons accès à SQLite (en local)
        $sqlitePath = database_path('database.sqlite');
        
        if (!file_exists($sqlitePath)) {
            Log::warning('Fichier SQLite non trouvé, migration des données annulée');
            return;
        }

        try {
            // Connexion SQLite
            $sqliteConnection = new \PDO('sqlite:' . $sqlitePath);
            $sqliteConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            Log::info('Connexion SQLite établie');

            // Migrer les données table par table
            $this->migrateRanks($sqliteConnection);
            $this->migrateUsers($sqliteConnection);
            $this->migrateChapters($sqliteConnection);
            $this->migrateUnits($sqliteConnection);
            $this->migrateQuestions($sqliteConnection);
            $this->migrateChoices($sqliteConnection);
            $this->migrateDiscoveries($sqliteConnection);
            $this->migrateNovelties($sqliteConnection);
            $this->migrateEvents($sqliteConnection);
            $this->migrateEventUnits($sqliteConnection);
            $this->migrateReminders($sqliteConnection);
            $this->migrateWeeklies($sqliteConnection);
            $this->migrateLastChances($sqliteConnection);
            $this->migrateQuizTypes($sqliteConnection);
            $this->migrateQuizInstances($sqliteConnection);
            $this->migrateUserQuizScores($sqliteConnection);
            $this->migrateUserAnswers($sqliteConnection);
            $this->migrateScores($sqliteConnection);
            $this->migrateProgress($sqliteConnection);
            $this->migrateLotteryTickets($sqliteConnection);
            $this->migrateWeeklySeries($sqliteConnection);
            $this->migrateNotifications($sqliteConnection);

            Log::info('Migration des données terminée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la migration des données: ' . $e->getMessage());
            throw $e;
        }
    }

    private function migrateRanks(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM ranks')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('ranks')->insert([
                'id' => $row['id'],
                'name' => $row['name'],
                'level' => $row['level'],
                'minimum_points' => $row['minimum_points'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' rangs');
    }

    private function migrateUsers(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM users')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('users')->insert([
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $row['password'],
                'rank_id' => $row['rank_id'],
                'registration_date' => $row['registration_date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
                'email_verified_at' => $row['email_verified_at'] ?? null,
                'remember_token' => $row['remember_token'] ?? null,
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' utilisateurs');
    }

    private function migrateChapters(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM chapters')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('chapters')->insert([
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
                'theory_content' => $row['theory_content'] ?? null,
                'is_active' => $row['is_active'] ?? true,
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' chapitres');
    }

    private function migrateUnits(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM units')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('units')->insert([
                'id' => $row['id'],
                'chapter_id' => $row['chapter_id'],
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
                'theory_html' => $row['theory_html'] ?? null,
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' unités');
    }

    private function migrateQuestions(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM questions')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('questions')->insert([
                'id' => $row['id'],
                'quizable_type' => $row['quizable_type'] ?? null,
                'quizable_id' => $row['quizable_id'] ?? null,
                'question_text' => $row['question_text'] ?? null,
                'options' => $row['options'] ?? null,
                'correct_answer' => $row['correct_answer'] ?? null,
                'timer_seconds' => $row['timer_seconds'] ?? null,
                'type' => $row['type'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' questions');
    }

    private function migrateChoices(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM choices')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('choices')->insert([
                'id' => $row['id'],
                'question_id' => $row['question_id'],
                'text' => $row['text'],
                'is_correct' => $row['is_correct'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' choix');
    }

    // Méthodes similaires pour les autres tables...
    private function migrateDiscoveries(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM discoveries')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('discoveries')->insert([
                'id' => $row['id'],
                'chapter_id' => $row['chapter_id'],
                'available_date' => $row['available_date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' découvertes');
    }

    private function migrateNovelties(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM novelties')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('novelties')->insert([
                'id' => $row['id'],
                'chapter_id' => $row['chapter_id'],
                'publication_date' => $row['publication_date'],
                'initial_bonus' => $row['initial_bonus'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' nouveautés');
    }

    private function migrateEvents(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM events')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('events')->insert([
                'id' => $row['id'],
                'theme' => $row['theme'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' événements');
    }

    private function migrateEventUnits(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM event_units')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('event_units')->insert([
                'id' => $row['id'],
                'event_id' => $row['event_id'],
                'unit_id' => $row['unit_id'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' unités d\'événements');
    }

    private function migrateReminders(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM reminders')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('reminders')->insert([
                'id' => $row['id'],
                'chapter_id' => $row['chapter_id'],
                'number_questions' => $row['number_questions'],
                'deadline_date' => $row['deadline_date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' rappels');
    }

    private function migrateWeeklies(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM weeklies')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('weeklies')->insert([
                'id' => $row['id'],
                'chapter_id' => $row['chapter_id'],
                'week_start' => $row['week_start'],
                'number_questions' => $row['number_questions'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' hebdomadaires');
    }

    private function migrateLastChances(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM last_chances')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('last_chances')->insert([
                'id' => $row['id'],
                'name' => $row['name'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' dernières chances');
    }

    private function migrateQuizTypes(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM quiz_types')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('quiz_types')->insert([
                'id' => $row['id'],
                'name' => $row['name'],
                'morph_type' => $row['morph_type'] ?? null,
                'base_points' => $row['base_points'],
                'speed_bonus' => $row['speed_bonus'],
                'gives_ticket' => $row['gives_ticket'],
                'bonus_multiplier' => $row['bonus_multiplier'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' types de quiz');
    }

    private function migrateQuizInstances(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM quiz_instances')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('quiz_instances')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'quiz_type_id' => $row['quiz_type_id'],
                'quizable_type' => $row['quizable_type'] ?? null,
                'quizable_id' => $row['quizable_id'] ?? null,
                'quiz_mode' => $row['quiz_mode'] ?? 'quiz',
                'launch_date' => $row['launch_date'],
                'speed_bonus' => $row['speed_bonus'] ?? false,
                'status' => $row['status'] ?? 'active',
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' instances de quiz');
    }

    private function migrateUserQuizScores(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM user_quiz_scores')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('user_quiz_scores')->insert([
                'id' => $row['id'],
                'quiz_instance_id' => $row['quiz_instance_id'],
                'total_points' => $row['total_points'],
                'total_time' => $row['total_time'],
                'ticket_obtained' => $row['ticket_obtained'],
                'bonus_obtained' => $row['bonus_obtained'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' scores de quiz');
    }

    private function migrateUserAnswers(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM user_answers')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('user_answers')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'quiz_instance_id' => $row['quiz_instance_id'] ?? null,
                'question_id' => $row['question_id'],
                'choice_id' => $row['choice_id'] ?? null,
                'is_correct' => $row['is_correct'],
                'response_time' => $row['response_time'],
                'points_obtained' => $row['points_obtained'],
                'date' => $row['date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' réponses utilisateur');
    }

    private function migrateScores(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM scores')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('scores')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'total_points' => $row['total_points'],
                'bonus_points' => $row['bonus_points'],
                'rank_id' => $row['rank_id'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' scores');
    }

    private function migrateProgress(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM progress')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('progress')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'chapter_id' => $row['chapter_id'],
                'unit_id' => $row['unit_id'] ?? null,
                'percentage' => $row['percentage'],
                'completed' => $row['completed'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' progressions');
    }

    private function migrateLotteryTickets(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM lottery_tickets')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('lottery_tickets')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'weekly_id' => $row['weekly_id'],
                'obtained_date' => $row['obtained_date'],
                'bonus' => $row['bonus'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' tickets de loterie');
    }

    private function migrateWeeklySeries(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM weekly_series')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('weekly_series')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'count' => $row['count'],
                'bonus_tickets' => $row['bonus_tickets'],
                'last_participation' => $row['last_participation'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' séries hebdomadaires');
    }

    private function migrateNotifications(\PDO $sqlite): void
    {
        $data = $sqlite->query('SELECT * FROM notifications')->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($data as $row) {
            DB::table('notifications')->insert([
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'type' => $row['type'],
                'message' => $row['message'],
                'read' => $row['read'],
                'date' => $row['date'],
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);
        }
        
        Log::info('Migré ' . count($data) . ' notifications');
    }
}
