<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastChance extends Model
{
    protected $fillable = [
        'nom',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];
}
