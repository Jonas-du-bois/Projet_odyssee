<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryTicket extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'lottery_tickets'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'weekly_id',
        'obtained_date',
        'bonus',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'obtained_date' => 'date',
        'bonus' => 'boolean',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with weekly
     */
    public function weekly()
    {
        return $this->belongsTo(Weekly::class, 'weekly_id');
    }

    /**
     * Scope for bonus tickets
     */
    public function scopeBonus($query)
    {
        return $query->where('bonus', true);
    }

    /**
     * Scope for regular tickets
     */
    public function scopeRegular($query)
    {
        return $query->where('bonus', false);
    }
}
