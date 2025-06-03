<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'nom',
        'niveau',
        'points_minimum',
    ];

    protected $casts = [
        'niveau' => 'integer',
        'points_minimum' => 'integer',
    ];

    /**
     * Relations
     */
    public function users()
    {
        return $this->hasMany(User::class, 'rang_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'rang_id');
    }
}
