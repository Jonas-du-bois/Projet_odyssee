<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryTicket extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'weekly_id',
        'obtained_date',
        'bonus',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'weekly_id' => 'integer',
        'obtained_date' => 'date',
        'bonus' => 'boolean',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function weekly()
    {
        return $this->belongsTo(Weekly::class);
    }
}
