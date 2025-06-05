<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discovery extends Model
{
    use HasFactory;

    protected $table = 'discoveries'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chapter_id',
        'available_date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'available_date' => 'date',
    ];

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}
