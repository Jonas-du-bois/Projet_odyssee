<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weekly extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'chapter_id',
        'week',
        'number_questions',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'week' => 'date',
        'number_questions' => 'integer',
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
