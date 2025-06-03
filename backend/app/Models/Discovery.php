<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discovery extends Model
{
    protected $fillable = [
        'chapter_id',
        'date_disponible',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'date_disponible' => 'date',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
