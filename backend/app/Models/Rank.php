<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'level',
        'minimum_points',
    ];

    protected $casts = [
        'level' => 'integer',
        'minimum_points' => 'integer',
    ];

    /**
     * Relations
     */
    public function users()
    {
        return $this->hasMany(User::class, 'rank_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'rank_id');
    }
}
