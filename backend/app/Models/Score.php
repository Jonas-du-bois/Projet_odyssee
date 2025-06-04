<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'user_id',
        'total_points',
        'bonus_points',
        'rank_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_points' => 'integer',
        'bonus_points' => 'integer',
        'rank_id' => 'integer',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }
}
