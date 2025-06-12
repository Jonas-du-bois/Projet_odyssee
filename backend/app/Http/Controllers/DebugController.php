<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Unit;
use App\Models\Discovery;
use App\Models\Choice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class DebugController extends Controller
{
    public function databaseStatus()
    {
        try {
            $stats = [
                'questions_total' => Question::count(),
                'choices_total' => Choice::count(),
                'units_total' => Unit::count(),
                'discoveries_total' => Discovery::count(),
                'questions_by_unit' => [],
                'questions_by_discovery' => [],
            ];

            // Compter les questions par unité
            for ($i = 1; $i <= 10; $i++) {
                $count = Question::where('quizable_type', 'App\Models\Unit')
                    ->where('quizable_id', $i)
                    ->count();
                $stats['questions_by_unit'][$i] = $count;
            }

            // Compter les questions par discovery
            for ($i = 1; $i <= 5; $i++) {
                $count = Question::where('quizable_type', 'App\Models\Discovery')
                    ->where('quizable_id', $i)
                    ->count();
                $stats['questions_by_discovery'][$i] = $count;
            }

            // Exemples de questions
            $stats['sample_questions'] = Question::with('choices')
                ->take(3)
                ->get()
                ->map(function($q) {
                    return [
                        'id' => $q->id,
                        'quizable_type' => $q->quizable_type,
                        'quizable_id' => $q->quizable_id,
                        'question_text' => $q->question_text,
                        'choices_count' => $q->choices->count(),
                    ];
                });

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function seedQuestions()
    {        try {
            // Forcer l'exécution du seeder
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\QuestionSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\ChoiceSeeder', '--force' => true]);

            return response()->json([
                'message' => 'Seeders exécutés',
                'questions_count' => Question::count(),
                'choices_count' => Choice::count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
