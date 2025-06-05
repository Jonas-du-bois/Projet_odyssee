<?php

namespace App\Events;

use App\Models\UserQuizScore;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userQuizScore;

    /**
     * Create a new event instance.
     */
    public function __construct(UserQuizScore $userQuizScore)
    {
        $this->userQuizScore = $userQuizScore;
    }
}
