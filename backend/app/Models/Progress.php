<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $fillable = [
        'user_id',
        'chapter_id',
        'unit_id',
        'pourcentage',
        'terminé',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'chapter_id' => 'integer',
        'unit_id' => 'integer',
        'pourcentage' => 'float',
        'terminé' => 'boolean',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
