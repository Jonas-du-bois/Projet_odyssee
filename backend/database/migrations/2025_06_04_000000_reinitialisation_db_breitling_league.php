<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Réinitialisation complète de la base de données Breitling League
     */
    public function up(): void
    {
        // Supprimer toutes les tables existantes dans l'ordre inverse des dépendances
        $this->dropAllTables();
        
        // Créer toutes les tables selon la nouvelle structure
        $this->createAllTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropAllTables();
    }

    /**
     * Supprimer toutes les tables dans l'ordre inverse des dépendances
     */
    private function dropAllTables(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('weekly_series');
        Schema::dropIfExists('lottery_tickets');
        Schema::dropIfExists('progress');
        Schema::dropIfExists('scores');
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('user_quiz_scores');
        Schema::dropIfExists('quiz_instances');
        Schema::dropIfExists('quiz_types');
        Schema::dropIfExists('last_chances');
        Schema::dropIfExists('weeklies');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('event_units');
        Schema::dropIfExists('events');
        Schema::dropIfExists('novelties');
        Schema::dropIfExists('discoveries');
        Schema::dropIfExists('choices');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('units');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('users');
        Schema::dropIfExists('ranks');
    }

    /**
     * Créer toutes les tables selon la nouvelle structure
     */
    private function createAllTables(): void
    {        // Table Rank
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level');
            $table->integer('minimum_points');
        });

        // Table User
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('rank_id')->nullable();
            $table->date('registration_date');

            $table->foreign('rank_id')->references('id')->on('ranks');
        });

        // Table Chapter
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
        });        // Table Unit
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('theory_html')->nullable();
            
            $table->foreign('chapter_id')->references('id')->on('chapters');
        });        // Table Question
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->text('statement');
            $table->integer('timer_seconds')->nullable();
            $table->string('type');
            
            $table->foreign('unit_id')->references('id')->on('units');
        });

        // Table Choice
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('text');
            $table->boolean('is_correct');

            $table->foreign('question_id')->references('id')->on('questions');
        });

        // Table Discovery
        Schema::create('discoveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->date('available_date');

            $table->foreign('chapter_id')->references('id')->on('chapters');
        });

        // Table Novelty
        Schema::create('novelties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->date('publication_date');
            $table->boolean('initial_bonus');
            
            $table->foreign('chapter_id')->references('id')->on('chapters');
        });        // Table Event
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('theme');
            $table->date('start_date');
            $table->date('end_date');
        });

        // Table EventUnit
        Schema::create('event_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('unit_id');
            
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('unit_id')->references('id')->on('units');
        });        // Table Reminder
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->integer('number_questions');
            $table->date('deadline_date');
            
            $table->foreign('chapter_id')->references('id')->on('chapters');
        });        // Table Weekly
        Schema::create('weeklies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id');
            $table->date('week_start');
            $table->integer('number_questions');
            
            $table->foreign('chapter_id')->references('id')->on('chapters');
        });        // Table LastChance
        Schema::create('last_chances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
        });        // Table QuizType
        Schema::create('quiz_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('base_points');
            $table->boolean('speed_bonus');
            $table->boolean('gives_ticket');
            $table->integer('bonus_multiplier');
        });        // Table QuizInstance
        Schema::create('quiz_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('quiz_type_id');
            $table->string('module_type');
            $table->integer('module_id');
            $table->datetime('launch_date');
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('quiz_type_id')->references('id')->on('quiz_types');
        });        // Table UserQuizScore
        Schema::create('user_quiz_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_instance_id');
            $table->integer('total_points');
            $table->integer('total_time');
            $table->boolean('ticket_obtained');
            $table->boolean('bonus_obtained');
            
            $table->foreign('quiz_instance_id')->references('id')->on('quiz_instances');
        });        // Table UserAnswer
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('choice_id')->nullable();
            $table->boolean('is_correct');
            $table->integer('response_time');
            $table->integer('points_obtained');
            $table->datetime('date');
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('choice_id')->references('id')->on('choices');
        });        // Table Score
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('total_points');
            $table->integer('bonus_points');
            $table->unsignedBigInteger('rank_id');
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('rank_id')->references('id')->on('ranks');
        });        // Table Progress
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('chapter_id');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->float('percentage');
            $table->boolean('completed');
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('chapter_id')->references('id')->on('chapters');
            $table->foreign('unit_id')->references('id')->on('units');
        });        // Table LotteryTicket
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('weekly_id');
            $table->date('obtained_date');
            $table->boolean('bonus');
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('weekly_id')->references('id')->on('weeklies');
        });        // Table WeeklySeries
        Schema::create('weekly_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('count');
            $table->integer('bonus_tickets');
            $table->date('last_participation');
            
            $table->foreign('user_id')->references('id')->on('users');
        });        // Table Notification
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->text('message');
            $table->boolean('read');
            $table->datetime('date');
            
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
