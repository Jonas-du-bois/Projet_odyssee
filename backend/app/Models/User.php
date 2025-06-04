<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public $timestamps = false;

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
            'password' => 'hashed',
            'registration_date' => 'date',
        ];
    }

    /**
     * Relations
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }

    public function quizInstances()
    {
        return $this->hasMany(QuizInstance::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function lotteryTickets()
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function weeklySeries()
    {
        return $this->hasMany(WeeklySeries::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
