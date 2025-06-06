<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RankUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $oldRankId;
    public $newRankId;
    public $totalPoints;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $oldRankId, $newRankId, $totalPoints)
    {
        $this->user = $user;
        $this->oldRankId = $oldRankId;
        $this->newRankId = $newRankId;
        $this->totalPoints = $totalPoints;
    }
}
