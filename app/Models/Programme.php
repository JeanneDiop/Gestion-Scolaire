<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    use HasFactory;
    protected $fillable = [
        'matiere',
        'categorie',
        'competences_essentielles',
        'leçons',
        'type_exercices',
        'volume_horaire',
        'duree_seance',
        'mode_evaluation',
        'bareme',
        'file_name',

    ];

    protected $casts = [
        'competences_essentielles' => 'array',
    ];
}
