<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weekly extends Model
{
    protected $fillable = [
        'chapter_id',
        'semaine',
        'nb_questions',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'semaine' => 'date',
        'nb_questions' => 'integer',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function lotteryTickets()
    {
        return $this->hasMany(LotteryTicket::class);
    }
}
