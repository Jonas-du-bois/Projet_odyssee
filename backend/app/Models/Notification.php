<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'lu',
        'date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'lu' => 'boolean',
        'date' => 'datetime',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('lu', false);
    }

    public function scopeRead($query)
    {
        return $query->where('lu', true);
    }
}
