<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enseignant;
use App\Models\Planifiercour;
use App\Models\Evaluation;
use App\Models\Programme;
use App\Models\ClasseAssociation;
class Cours extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description',
        'niveau_education',
        'matiere',
        'type',
        'duree',
        'etat',
        'credits',
    ];
    public function enseignant(){
        return $this->belongsTo(Enseignant::class);
    }

    public function planifiercours(){
        return $this->hasMany(Planifiercour::class);
    }
    public function presenceabsences()
    {
        return $this->hasMany(PresenceAbsence::class);
    }
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
    public function programmes()
    {
        return $this->hasMany(Programme::class);
    }

    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'cours_id');
    }
}

