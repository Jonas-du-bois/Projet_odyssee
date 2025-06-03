<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'user_id',
        'points_total',
        'points_bonus',
        'rang_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'points_total' => 'integer',
        'points_bonus' => 'integer',
        'rang_id' => 'integer',
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
        return $this->belongsTo(Rank::class, 'rang_id');
    }
}
