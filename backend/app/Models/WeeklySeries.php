<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklySeries extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'count',
        'bonus_tickets',
        'last_participation',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'count' => 'integer',
        'bonus_tickets' => 'integer',
        'last_participation' => 'date',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les tickets de loterie obtenus par cette série
     */
    public function lotteryTickets()
    {
        return $this->hasMany(LotteryTicket::class, 'user_id', 'user_id')
            ->where('bonus', true);
    }
}
