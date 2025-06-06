<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    
    protected $table = 'users'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rank_id',
        'registration_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Relationship with Rank
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    /**
     * Relationship with scores
     */
    public function scores()
    {
        return $this->hasMany(Score::class, 'user_id');
    }

    /**
     * Relationship with quiz instances
     */
    public function quizInstances()
    {
        return $this->hasMany(QuizInstance::class, 'user_id');
    }

    /**
     * Relationship with progress
     */
    public function progress()
    {
        return $this->hasMany(Progress::class, 'user_id');
    }

    /**
     * Relationship with lottery tickets
     */
    public function lotteryTickets()
    {
        return $this->hasMany(LotteryTicket::class, 'user_id');
    }

    /**
     * Relationship with user quiz scores
     */
    public function userQuizScores()
    {
        return $this->hasManyThrough(UserQuizScore::class, QuizInstance::class, 'user_id', 'quiz_instance_id');
    }

    /**
     * Relationship with user score (single record)
     */
    public function userScore()
    {
        return $this->hasOne(Score::class, 'user_id');
    }

    /**
     * Calculer le total des points de l'utilisateur (scores + bonus)
     */
    public function getTotalPoints()
    {
        return $this->scores()->sum(DB::raw('total_points + bonus_points'));
    }

    /**
     * Calculer le total des points depuis les quiz scores (fallback)
     */
    public function getTotalQuizPoints()
    {
        return \App\Models\UserQuizScore::whereHas('quizInstance', function($query) {
            $query->where('user_id', $this->id);
        })->sum('total_points');
    }

    /**
     * Obtenir le total des points avec fallback automatique
     */
    public function getTotalPointsWithFallback()
    {
        $scoresTotal = $this->getTotalPoints();
        return $scoresTotal > 0 ? $scoresTotal : $this->getTotalQuizPoints();
    }

    /**
     * Mettre à jour le rang de l'utilisateur basé sur ses points totaux
     */
    public function updateRank()
    {
        $totalPoints = $this->getTotalPoints();
        
        $newRank = \App\Models\Rank::where('minimum_points', '<=', $totalPoints)
            ->orderBy('minimum_points', 'desc')
            ->first();

        if ($newRank && $this->rank_id !== $newRank->id) {
            $this->update(['rank_id' => $newRank->id]);
            return $newRank;
        }

        return $this->rank;
    }
}
