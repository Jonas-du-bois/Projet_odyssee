<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklySeries extends Model
{
    protected $fillable = [
        'user_id',
        'count',
        'bonus_tickets',
        'derniere_participation',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'count' => 'integer',
        'bonus_tickets' => 'integer',
        'derniere_participation' => 'date',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
