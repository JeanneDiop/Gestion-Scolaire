<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cours;
use App\Models\Classe;
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
        'source',
        'file_name',

    ];

    protected $casts = [
        'competences_essentielles' => 'array',
    ];


    public function classes()
    {
        return $this->hasMany(Classe::class);
    }
    public function cours()
    {
        return $this->hasMany(Cours::class);
    }
}
