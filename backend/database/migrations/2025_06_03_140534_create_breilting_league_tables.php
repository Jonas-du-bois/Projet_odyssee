<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table des rangs
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->integer('niveau');
            $table->integer('points_minimum');
            $table->timestamps();
        });

        // Table des chapitres
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Table des unités
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('chapter_id');
        });

        // Table des questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->text('enonce');
            $table->integer('timer_secondes')->nullable();
            $table->string('type');
            $table->timestamps();
            
            $table->index('unit_id');
        });

        // Table des choix de réponse
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('texte');
            $table->boolean('est_correct')->default(false);
            $table->timestamps();
            
            $table->index('question_id');
        });

        // Table des découvertes
        Schema::create('discoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->date('date_disponible');
            $table->timestamps();
            
            $table->index('chapter_id');
        });

        // Table des nouveautés
        Schema::create('novelties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->date('date_publication');
            $table->boolean('bonus_initial')->default(false);
            $table->timestamps();
            
            $table->index('chapter_id');
        });

        // Table des événements
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->string('theme');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->timestamps();
            
            $table->index('chapter_id');
        });

        // Table des rappels
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->integer('nb_questions');
            $table->date('date_limite');
            $table->timestamps();
            
            $table->index('chapter_id');
        });

        // Table des weeklies
        Schema::create('weeklies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->date('semaine');
            $table->integer('nb_questions');
            $table->timestamps();
            
            $table->index('chapter_id');
        });

        // Table des dernières chances
        Schema::create('last_chances', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->timestamps();
        });

        // Table des types de quiz
        Schema::create('quiz_types', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->integer('base_points');
            $table->boolean('bonus_rapidite')->default(false);
            $table->boolean('donne_ticket')->default(false);
            $table->integer('multiplicateur_bonus')->default(1);
            $table->timestamps();
        });

        // Table des instances de quiz
        Schema::create('quiz_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_type_id')->constrained('quiz_types')->onDelete('cascade');
            $table->string('module_type');
            $table->unsignedBigInteger('module_id');
            $table->timestamp('date_lancement');
            $table->timestamps();
            
            $table->index(['user_id', 'quiz_type_id']);
        });

        // Table des scores de quiz utilisateur
        Schema::create('user_quiz_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_instance_id')->constrained('quiz_instances')->onDelete('cascade');
            $table->integer('total_points');
            $table->integer('temps_total');
            $table->boolean('ticket_obtenu')->default(false);
            $table->boolean('bonus_obtenu')->default(false);
            $table->timestamps();
            
            $table->index('quiz_instance_id');
        });

        // Table des réponses utilisateur
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('choix_id')->nullable()->constrained('choices')->onDelete('set null');
            $table->boolean('est_correct')->default(false);
            $table->integer('temps_reponse');
            $table->integer('points_obtenus');
            $table->timestamp('date');
            $table->timestamps();
            
            $table->index(['user_id', 'question_id']);
        });

        // Table des scores
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('points_total')->default(0);
            $table->integer('points_bonus')->default(0);
            $table->foreignId('rang_id')->nullable()->constrained('ranks')->onDelete('set null');
            $table->timestamps();
            
            $table->index('user_id');
        });

        // Table des progrès
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->float('pourcentage')->default(0);
            $table->boolean('terminé')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'chapter_id']);
        });

        // Table des tickets de loterie
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('weekly_id')->constrained('weeklies')->onDelete('cascade');
            $table->date('date_obtenue');
            $table->boolean('bonus')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'weekly_id']);
        });

        // Table des séries hebdomadaires
        Schema::create('weekly_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('count')->default(0);
            $table->integer('bonus_tickets')->default(0);
            $table->date('derniere_participation')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });

        // Table des notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->text('message');
            $table->boolean('lu')->default(false);
            $table->timestamp('date');
            $table->timestamps();
            
            $table->index('user_id');
        });

        // Modification de la table users existante pour ajouter les champs spécifiques
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom')->after('name')->nullable();
            $table->foreignId('rang_id')->nullable()->after('email')->constrained('ranks')->onDelete('set null');
            $table->date('date_inscription')->after('rang_id')->default(now());
            
            $table->index('rang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les modifications de la table users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rang_id']);
            $table->dropColumn(['nom', 'rang_id', 'date_inscription']);
        });

        // Supprimer les tables dans l'ordre inverse (à cause des contraintes de clés étrangères)
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
        Schema::dropIfExists('events');
        Schema::dropIfExists('novelties');
        Schema::dropIfExists('discoveries');
        Schema::dropIfExists('choices');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('units');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('ranks');
    }
};
