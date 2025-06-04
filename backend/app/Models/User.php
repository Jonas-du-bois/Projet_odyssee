<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;
    
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
     * Relationship with notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
