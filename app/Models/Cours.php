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
        'etat',
        'credit',
        'coefficient',
        'semestre',
        'objectif_generaux',
        'objectif_specifiques'
    ];
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }

    public function planifiercours(){
        return $this->hasMany(Planifiercour::class);
    }
    public function categories(){
        return $this->hasMany(CategorieCours::class, 'cours_id');
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
        return $this->belongsTo(Programme::class, 'programme_id');
    }
    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'cours_id');
    }


}

