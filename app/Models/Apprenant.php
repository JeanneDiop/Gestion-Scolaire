<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tuteur;
use App\Models\Classe;
use App\Models\User;
use App\Models\PresenceAbsence;
use App\Models\Evaluation;
use App\Models\Parcours;
use App\Models\ClasseAssociation;
use App\Models\ApprenantClasse;

class Apprenant extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_naissance',
        'lieu_naissance',
        'numero_CNI',
        'image',
        'numero_carte_scolaire',
        'niveau_education',
        'statut_marital',
        'tuteur_id',
        'classe_id'
    ];
    public function tuteur(){
        return $this->belongsTo(Tuteur::class);
    }

    public function classe(){
        return $this->belongsTo(Classe::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function presenceabsences()
    {
        return $this->hasMany(PresenceAbsence::class);
    }
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
    public function parcours()
    {
        return $this->hasMany(Parcours::class);
    }
    public function classeassociations()
    {
        return $this->hasMany(ClasseAssociation::class, 'apprenant_id');
    }

    public function apprenantclasses()
    {
        return $this->hasMany(ApprenantClasse::class);
    }
}
