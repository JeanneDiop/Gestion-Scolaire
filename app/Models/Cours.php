<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enseignant;
use App\Models\Planifiercour;
use App\Models\Evaluation;

use App\Models\Presence;
use App\Models\Programme;
use App\Models\ClasseAssociation;
use App\Models\CategorieCours;
class Cours extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',
        'niveau_education',
        'niveau_classe',
        'heure_allouee',
        //'duree_recommander_sceance',
        //'categories_cours',
        'etat',
        //'bareme',
        //'frequence_evaluation',
        //'type_evaluation',
        'credit',
        //'type_exercice',
        //'coefficient',
        //'semestre',
    ];
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }

    public function planifiercours(){
        return $this->hasMany(Planifiercour::class);
    }
    public function categoriecours(){
        return $this->hasMany(CategorieCours::class);
    }
    public function presences()
    {
        return $this->hasMany(Presence::class ,'cours_id');
    }
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function programme()
    {
        return $this->hasMany(Programme::class, 'cours_id');
    }
    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'cours_id');
    }


}

