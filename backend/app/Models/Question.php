<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'unit_id',
        'enonce',
        'timer_secondes',
        'type',
    ];

    protected $casts = [
        'unit_id' => 'integer',
        'timer_secondes' => 'integer',
    ];

    /**
     * Relations
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
