<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\QuizInstance;
use App\Models\UserQuizScore;
use App\Models\Score;
use App\Models\Rank;
use App\Events\QuizCompleted;
use App\Listeners\SynchronizeUserScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class EventListenerPostgreSQLTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des données de test
        $this->createTestData();
    }

    /** @test */
    public function it_can_handle_quiz_completed_event_with_postgresql()
    {
        // Fausse les événements pour pouvoir les vérifier
        Event::fake();

        $user = User::factory()->create();
        $quizInstance = QuizInstance::factory()->create(['user_id' => $user->id]);
        $userQuizScore = UserQuizScore::factory()->create([
            'quiz_instance_id' => $quizInstance->id,
            'total_points' => 100,
            'bonus_obtained' => true
        ]);

        // Déclencher l'événement
        event(new QuizCompleted($userQuizScore));

        // Vérifier que l'événement a été déclenché
        Event::assertDispatched(QuizCompleted::class);
    }

    /** @test */
    public function it_synchronizes_user_score_correctly_with_postgresql()
    {
        $user = User::factory()->create();
        $quizInstance = QuizInstance::factory()->create(['user_id' => $user->id]);
        $userQuizScore = UserQuizScore::factory()->create([
            'quiz_instance_id' => $quizInstance->id,
            'total_points' => 150,
            'bonus_obtained' => true
        ]);

        // Exécuter directement le listener
        $listener = new SynchronizeUserScore();
        $listener->handle(new QuizCompleted($userQuizScore));

        // Vérifier que le score a été créé/mis à jour
        $this->assertDatabaseHas('scores', [
            'user_id' => $user->id,
            'total_points' => 150
        ]);
    }

    /** @test */
    public function it_handles_date_formatting_correctly_for_postgresql()
    {
        $listener = new SynchronizeUserScore();
        
        // Utiliser la réflexion pour tester la méthode privée
        $reflection = new \ReflectionClass($listener);
        $method = $reflection->getMethod('getDateFormatSQL');
        $method->setAccessible(true);
        
        // Configurer temporairement PostgreSQL
        config(['database.default' => 'pgsql']);
        config(['database.connections.pgsql.driver' => 'pgsql']);
        
        $sql = $method->invoke($listener);
        
        $this->assertEquals("TO_CHAR(created_at, 'YYYY-MM') = ?", $sql);
    }

    /** @test */
    public function it_handles_monthly_score_aggregation_correctly()
    {
        $user = User::factory()->create();
        
        // Créer un score existant pour ce mois
        $existingScore = Score::factory()->create([
            'user_id' => $user->id,
            'total_points' => 50,
            'created_at' => Carbon::now()
        ]);

        $quizInstance = QuizInstance::factory()->create(['user_id' => $user->id]);
        $userQuizScore = UserQuizScore::factory()->create([
            'quiz_instance_id' => $quizInstance->id,
            'total_points' => 75,
            'bonus_obtained' => false
        ]);

        // Exécuter le listener
        $listener = new SynchronizeUserScore();
        $listener->handle(new QuizCompleted($userQuizScore));

        // Vérifier que le score existant a été mis à jour (50 + 75 = 125)
        $existingScore->refresh();
        $this->assertEquals(125, $existingScore->total_points);
    }

    /** @test */
    public function it_updates_user_rank_based_on_total_points()
    {
        $user = User::factory()->create(['rank_id' => 1]);
        
        // Créer des rangs
        $rank1 = Rank::factory()->create(['minimum_points' => 0, 'level' => 1]);
        $rank2 = Rank::factory()->create(['minimum_points' => 100, 'level' => 2]);
        $rank3 = Rank::factory()->create(['minimum_points' => 500, 'level' => 3]);

        $user->update(['rank_id' => $rank1->id]);

        // Créer un quiz avec suffisamment de points pour passer au rang 2
        $quizInstance = QuizInstance::factory()->create(['user_id' => $user->id]);
        $userQuizScore = UserQuizScore::factory()->create([
            'quiz_instance_id' => $quizInstance->id,
            'total_points' => 150,
            'bonus_obtained' => false
        ]);

        // Exécuter le listener
        $listener = new SynchronizeUserScore();
        $listener->handle(new QuizCompleted($userQuizScore));

        // Vérifier que l'utilisateur a été promu
        $user->refresh();
        $this->assertEquals($rank2->id, $user->rank_id);
    }

    private function createTestData(): void
    {
        // Créer des rangs de base
        Rank::factory()->create([
            'name' => 'Débutant',
            'level' => 1,
            'minimum_points' => 0
        ]);

        Rank::factory()->create([
            'name' => 'Intermédiaire',
            'level' => 2,
            'minimum_points' => 100
        ]);

        Rank::factory()->create([
            'name' => 'Avancé',
            'level' => 3,
            'minimum_points' => 500
        ]);
    }
}
