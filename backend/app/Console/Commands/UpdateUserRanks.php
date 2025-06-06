<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Score;
use App\Models\Rank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUserRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranks:update {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule et met à jour les rangs de tous les utilisateurs basé sur leurs points totaux';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Début de la mise à jour des rangs utilisateurs...');

        // Confirmation si pas de --force
        if (!$this->option('force')) {
            if (!$this->confirm('Cette opération va recalculer les rangs de tous les utilisateurs. Continuer ?')) {
                $this->info('Opération annulée.');
                return;
            }
        }

        // Vérifier qu'il y a des rangs configurés
        $ranksCount = Rank::count();
        if ($ranksCount === 0) {
            $this->error('❌ Aucun rang configuré dans le système !');
            return;
        }

        $this->info("📊 {$ranksCount} rangs disponibles dans le système");

        // Récupérer tous les utilisateurs
        $users = User::all();
        $this->info("👥 {$users->count()} utilisateurs à traiter");

        $updated = 0;
        $assigned = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                DB::transaction(function () use ($user, &$updated, &$assigned) {
                    // Calculer le total des points de l'utilisateur
                    $totalPoints = Score::where('user_id', $user->id)
                        ->sum(DB::raw('total_points + bonus_points'));

                    // Trouver le rang approprié - le rang le plus élevé dont les points minimum sont atteints
                    $newRank = Rank::where('minimum_points', '<=', $totalPoints)
                        ->orderBy('minimum_points', 'desc')
                        ->first();

                    // Si aucun rang trouvé, prendre le rang de niveau 1 (minimum)
                    if (!$newRank) {
                        $newRank = Rank::orderBy('level', 'asc')->first();
                    }

                    $currentRankId = $user->rank_id;
                    
                    if ($newRank && $newRank->id !== $currentRankId) {
                        // Mettre à jour le rang de l'utilisateur
                        $user->update(['rank_id' => $newRank->id]);
                        
                        if ($currentRankId) {
                            $updated++;
                        } else {
                            $assigned++;
                        }

                        // Mettre à jour le rang dans le score le plus récent
                        $latestScore = Score::where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if ($latestScore && $latestScore->rank_id !== $newRank->id) {
                            $latestScore->update(['rank_id' => $newRank->id]);
                        }
                    }
                });
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("❌ Erreur pour l'utilisateur {$user->name} (ID: {$user->id}): " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Résumé
        $this->info('✅ Mise à jour des rangs terminée !');
        $this->table(
            ['Statistique', 'Nombre'],
            [
                ['Rangs mis à jour', $updated],
                ['Rangs assignés (nouveaux)', $assigned],
                ['Erreurs', $errors],
                ['Total traité', $users->count()]
            ]
        );

        // Afficher la distribution des rangs après mise à jour
        $this->newLine();
        $this->info('📊 Distribution des rangs après mise à jour:');
        
        $distribution = DB::table('users')
            ->join('ranks', 'users.rank_id', '=', 'ranks.id')
            ->select('ranks.name', 'ranks.level', DB::raw('COUNT(*) as count'))
            ->groupBy('ranks.id', 'ranks.name', 'ranks.level')
            ->orderBy('ranks.level')
            ->get();

        $distributionData = [];
        foreach ($distribution as $row) {
            $distributionData[] = [
                $row->name,
                "Niveau {$row->level}",
                $row->count
            ];
        }

        $this->table(
            ['Rang', 'Niveau', 'Utilisateurs'],
            $distributionData
        );

        return 0;
    }
}
